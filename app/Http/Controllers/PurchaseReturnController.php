<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Purchase;
use App\Models\ReturnItem;
use App\Models\ProductVariant;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.refund_amount' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $purchase = Purchase::findOrFail($request->purchase_id);
            $totalRefundAmount = 0;
            $returns = [];

            foreach ($request->items as $item) {
                // Verify variant was originally in purchase? 
                // We'll trust the frontend for now or could add a check:
                // $purchaseItem = $purchase->items()->where('product_variant_id', $item['product_variant_id'])->firstOrFail();

                $returnItem = $purchase->returns()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'refund_amount' => $item['refund_amount'],
                    'reason' => $item['reason'] ?? 'Purchase Return',
                    'created_by' => auth()->id(),
                ]);

                // Update Stock
                $variant = ProductVariant::findOrFail($item['product_variant_id']);
                $variant->decrement('stock_quantity', $item['quantity']);

                // Log Transaction
                InventoryTransaction::create([
                    'product_variant_id' => $variant->id,
                    'transaction_type' => 'purchase_return',
                    'quantity' => -$item['quantity'],
                    'reference_id' => $purchase->id,
                    'created_by' => auth()->id(),
                ]);

                $totalRefundAmount += $item['refund_amount'];
                $returns[] = $returnItem;
            }

            // Adjust Supplier Due & Purchase
            // If the supplier owes us money back or we just reduce the due amount
            $purchase->decrement('total_amount', $totalRefundAmount);
            
            // Adjust Due Amount carefully
            // Depending on accounting, usually you reduce due_amount if it's > 0, otherwise it's an advance
            if ($purchase->due_amount >= $totalRefundAmount) {
                $purchase->decrement('due_amount', $totalRefundAmount);
                $supplierAmountToDecrease = $totalRefundAmount;
            } else {
                $supplierAmountToDecrease = $purchase->due_amount; // Just clear due
                $purchase->update(['due_amount' => 0]);
                // Any extra refund amount means supplier owes us cash, perhaps handle in payment module.
            }

            if ($supplierAmountToDecrease > 0) {
                $supplier = Supplier::find($purchase->supplier_id);
                if ($supplier) {
                    $supplier->updateDue(-$supplierAmountToDecrease);
                }
            }

            return response()->json($returns, 201);
        });
    }

    public function index(Request $request)
    {
        $query = ReturnItem::with(['purchase.supplier', 'variant.product', 'creator'])
            ->whereNotNull('purchase_id');
            
        if ($request->has('purchase_id')) {
            $query->where('purchase_id', $request->purchase_id);
        }

        return response()->json($query->latest()->paginate(20));
    }
}

