<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\ProductVariant;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

use App\Services\InvoiceService;

use App\Services\NotificationService;

use App\Services\ActivityLogService;

class PurchaseController extends Controller
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
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'items.variant.product', 'creator']);

        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $purchases = $query->latest()->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($purchases);
        }

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        return view('purchases.create');
    }

    /**
     * API for initial data (suppliers, products)
     */
    public function initData()
    {
        return response()->json([
            'suppliers' => Supplier::select('id', 'name', 'phone', 'previous_due')->get(),
            'payment_methods' => \App\Models\PaymentMethod::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'invoice_no' => 'nullable|string|unique:purchases,invoice_no',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            
            // Calculate total first
            foreach ($request->items as $item) {
                $itemTotal = ($item['quantity'] * $item['price']) - ($item['discount'] ?? 0);
                $totalAmount += $itemTotal;
            }

            $discount = $request->discount ?? 0;
            $netAmount = $totalAmount - $discount;
            $paidAmount = $request->paid_amount ?? 0;
            $dueAmount = $netAmount - $paidAmount;

            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'invoice_no' => $request->invoice_no ?? ('PUR-' . time()),
                'purchase_date' => $request->purchase_date,
                'total_amount' => $netAmount,
                'discount' => $discount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_method_id' => $request->payment_method_id,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $itemTotal = ($item['quantity'] * $item['price']) - ($item['discount'] ?? 0);
                
                $purchase->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'total' => $itemTotal,
                ]);

                // Update Stock
                $variant = ProductVariant::find($item['product_variant_id']);
                $variant->increment('stock_quantity', $item['quantity']);

                // Log Transaction
                InventoryTransaction::create([
                    'product_variant_id' => $variant->id,
                    'transaction_type' => 'purchase',
                    'quantity' => $item['quantity'],
                    'reference_id' => $purchase->id,
                    'created_by' => auth()->id(),
                ]);
            }

            // Handle Payment
            if ($paidAmount > 0) {
                $purchase->payments()->create([
                    'payment_method_id' => $request->payment_method_id,
                    'amount' => $paidAmount,
                    'payment_date' => $request->purchase_date,
                    'created_by' => auth()->id(),
                ]);
            }

            // Update Supplier Due
            if ($dueAmount > 0) {
                $supplier = Supplier::find($request->supplier_id);
                $supplier->updateDue($dueAmount);
                
                // Notify Manager about Supplier Due
                $this->notificationService->notifySupplierDue($supplier, $dueAmount);
            }

            // Log activity
            $this->activityLogService->log('purchases', 'create', "Created purchase invoice: {$purchase->invoice_no}", $purchase->id);

            // Generate Invoice
            $this->invoiceService->createFromPurchase($purchase);

            return response()->json($purchase->load('items', 'supplier', 'payments'), 201);
        });
    }

    public function show(Purchase $purchase)
    {
        return response()->json($purchase->load(['supplier', 'items.variant.product', 'payments', 'creator']));
    }

    public function destroy(Purchase $purchase)
    {
        // Deleting a purchase should ideally reverse stock and supplier dues.
        // For simplicity, we just delete here or we can implement full reversal logic.
        return DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                $variant = ProductVariant::find($item->product_variant_id);
                $variant->decrement('stock_quantity', $item->quantity);
                
                InventoryTransaction::create([
                    'product_variant_id' => $variant->id,
                    'transaction_type' => 'purchase_delete',
                    'quantity' => -$item->quantity,
                    'reference_id' => $purchase->id,
                    'created_by' => auth()->id(),
                ]);
            }

            if ($purchase->due_amount > 0) {
                $supplier = Supplier::find($purchase->supplier_id);
                if ($supplier) {
                    $supplier->updateDue(-$purchase->due_amount);
                }
            }

            $purchase->delete(); // This deletes items due to DB cascade
            return response()->json(['message' => 'Purchase deleted and stock reversed successfully.']);
        });
    }
}
