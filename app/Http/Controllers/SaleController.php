<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

use App\Services\InvoiceService;
use App\Services\NotificationService;
use App\Services\ActivityLogService;

class SaleController extends Controller
{
    protected $invoiceService;
    protected $notificationService;
    protected $activityLogService;

    public function __construct(
        InvoiceService $invoiceService, 
        NotificationService $notificationService,
        ActivityLogService $activityLogService
    ) {
        $this->invoiceService = $invoiceService;
        $this->notificationService = $notificationService;
        $this->activityLogService = $activityLogService;
    }
    public function index()
    {
        $sales = Sale::with(['customer', 'creator', 'paymentMethod'])->latest()->paginate(20);
        return response()->json($sales);
    }

    public function show($id)
    {
        $sale = Sale::with(['customer', 'items.variant.product', 'payments.paymentMethod'])->findOrFail($id);
        return response()->json($sale);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'       => 'nullable|exists:customers,id',
            'sale_date'         => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.variant_id'=> 'required|exists:product_variants,id',
            'items.*.quantity'  => 'required|integer|min:1',
            'items.*.price'     => 'required|numeric|min:0',
            'items.*.discount'  => 'nullable|numeric|min:0',
            'invoice_discount'  => 'nullable|numeric|min:0',
            'paid_amount'       => 'required|numeric|min:0',
            'payment_method_id' => 'required_if:paid_amount,>,0|exists:payment_methods,id',
        ]);

        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $variant = ProductVariant::lockForUpdate()->find($item['variant_id']);
                
                if ($variant->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for " . ($variant->product->name ?? $variant->sku));
                }

                $itemDiscount = $item['discount'] ?? 0;
                $itemTotal = ($item['price'] * $item['quantity']) - $itemDiscount;
                $subtotal += $itemTotal;

                $itemsData[] = [
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $itemDiscount,
                    'total' => $itemTotal,
                ];
            }

            $invoiceDiscount = $request->invoice_discount ?? 0;
            $totalAmount = $subtotal - $invoiceDiscount;
            $dueAmount = max(0, $totalAmount - $request->paid_amount);

            // Credit Check
            if ($dueAmount > 0) {
                if (!$request->customer_id) {
                    throw new \Exception("Customer is required for credit sales.");
                }
                $customer = Customer::find($request->customer_id);
                if (($customer->previous_due + $dueAmount) > $customer->credit_limit) {
                    throw new \Exception("Credit limit exceeded for this customer.");
                }
            }

            // Create Sale
            $sale = Sale::create([
                'invoice_no' => 'SALE-' . strtoupper(uniqid()),
                'customer_id' => $request->customer_id,
                'sale_date' => $request->sale_date,
                'subtotal' => $subtotal,
                'discount' => $invoiceDiscount,
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $dueAmount,
                'payment_method_id' => $request->payment_method_id,
                'created_by' => auth()->id(),
            ]);

            // Save Items & Update Stock
            foreach ($itemsData as $data) {
                $sale->items()->create($data);
                
                $variant = ProductVariant::find($data['product_variant_id']);
                $variant->decrement('stock_quantity', $data['quantity']);

                InventoryTransaction::create([
                    'product_variant_id' => $variant->id,
                    'transaction_type' => 'sale',
                    'quantity' => - $data['quantity'],
                    'reference_id' => $sale->id,
                    'note' => "POS Sale: " . $sale->invoice_no,
                    'created_by' => auth()->id(),
                ]);
            }

            // Handle Financials
            if ($request->customer_id && ($dueAmount != 0 || $request->paid_amount > 0)) {
                $customer = Customer::find($request->customer_id);
                
                // Record Payment if any
                if ($request->paid_amount > 0) {
                    $sale->payments()->create([
                        'payment_method_id' => $request->payment_method_id,
                        'amount' => $request->paid_amount,
                        'payment_date' => $request->sale_date,
                        'created_by' => auth()->id(),
                    ]);
                }

                if ($dueAmount > 0) {
                    $newBalance = $customer->updateBalance($dueAmount, 'debit');
                    $customer->ledgers()->create([
                        'type' => 'sale',
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'debit' => $dueAmount,
                        'balance' => $newBalance,
                        'note' => 'Sale Invoice: ' . $sale->invoice_no,
                    ]);

                    // Notify Manager about Customer Due
                    $this->notificationService->notifyCustomerDue($customer, $dueAmount);
                }
            }

            // Check for Low Stock after transaction
            foreach ($itemsData as $data) {
                $variant = ProductVariant::find($data['product_variant_id']);
                if ($variant->stock_quantity <= 5) { // Threshold for alert
                    $this->notificationService->notifyLowStock($variant);
                }
            }

            // Generate Invoice
            $this->invoiceService->createFromSale($sale);

            // Log activity
            $this->activityLogService->log('sales', 'create', "Created sale invoice: {$sale->invoice_no}", $sale->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Sale completed successfully',
                'sale' => $sale->load('items')
            ]);
        });
    }

    /**
     * Cancel a sale — restore stock and reverse customer ledger
     */
    public function destroy(Sale $sale)
    {
        if ($sale->payments()->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot cancel a sale that already has payments recorded.'
            ], 422);
        }

        return DB::transaction(function () use ($sale) {
            // Restore stock for each item
            foreach ($sale->items as $item) {
                $variant = ProductVariant::find($item->product_variant_id);
                if ($variant) {
                    $variant->increment('stock_quantity', $item->quantity);

                    InventoryTransaction::create([
                        'product_variant_id' => $variant->id,
                        'transaction_type'   => 'adjustment',
                        'quantity'           => $item->quantity,
                        'reference_id'       => $sale->id,
                        'note'               => "Sale Cancelled: " . $sale->invoice_no,
                        'created_by'         => auth()->id(),
                    ]);
                }
            }

            // Reverse customer ledger if credit sale
            if ($sale->customer_id && $sale->due_amount > 0) {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    $newBalance = $customer->updateBalance($sale->due_amount, 'credit');
                    $customer->ledgers()->create([
                        'type'           => 'adjustment',
                        'reference_type' => Sale::class,
                        'reference_id'   => $sale->id,
                        'credit'         => $sale->due_amount,
                        'balance'        => $newBalance,
                        'note'           => 'Sale Cancelled: ' . $sale->invoice_no,
                    ]);
                }
            }

            $this->activityLogService->log('sales', 'delete', "Cancelled sale: {$sale->invoice_no}", $sale->id);

            // Delete items and the sale
            $sale->items()->delete();
            $sale->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Sale cancelled and stock restored.',
            ]);
        });
    }
}
