<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\ReturnItem;
use App\Models\ProductVariant;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Services\InvoiceService;

use App\Services\ActivityLogService;

class SaleReturnController extends Controller
{
    protected $invoiceService;
    protected $activityLogService;

    public function __construct(InvoiceService $invoiceService, ActivityLogService $activityLogService)
    {
        $this->invoiceService = $invoiceService;
        $this->activityLogService = $activityLogService;
    }
    /**
     * Store return items and adjust stock/financials
     */
    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.refund_amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $sale = Sale::findOrFail($request->sale_id);
            $returns = [];

            foreach ($request->items as $item) {
                // 1. Create Return Record
                $return = ReturnItem::create([
                    'sale_id' => $sale->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'refund_amount' => $item['refund_amount'],
                    'reason' => $request->reason,
                    'created_by' => auth()->id(),
                ]);

                // 2. Increase Stock
                $variant = ProductVariant::find($item['product_variant_id']);
                $variant->increment('stock_quantity', $item['quantity']);

                // 3. Inventory Transaction
                InventoryTransaction::create([
                    'product_variant_id' => $variant->id,
                    'transaction_type' => 'return',
                    'quantity' => $item['quantity'],
                    'reference_id' => $sale->id,
                    'note' => "Sale Return: " . $sale->invoice_no,
                    'created_by' => auth()->id(),
                ]);

                $returns[] = $return;
                
                // 4. Financial Adjustment (If it was a credit sale, reduce due)
                if ($sale->customer_id && $sale->due_amount > 0) {
                    $adjustment = min($sale->due_amount, $item['refund_amount']);
                    $sale->decrement('due_amount', $adjustment);
                    
                    $customer = $sale->customer;
                    $newBalance = $customer->updateBalance($adjustment, 'credit');

                    $customer->ledgers()->create([
                        'type' => 'return',
                        'reference_type' => ReturnItem::class,
                        'reference_id' => $return->id,
                        'credit' => $adjustment,
                        'balance' => $newBalance,
                        'note' => 'Return adjustment for INV: ' . $sale->invoice_no,
                    ]);
                }

                // Generate Invoice for this return item
                $this->invoiceService->createFromReturn($return);

                // Log activity
                $this->activityLogService->log('sales', 'return', "Items returned for invoice: {$sale->invoice_no}", $return->id);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Items returned successfully',
                'returns' => $returns
            ]);
        });
    }
}
