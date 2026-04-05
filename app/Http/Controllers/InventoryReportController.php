<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    /**
     * Current Stock Report
     */
    public function stockReport()
    {
        $stock = ProductVariant::with(['product', 'inventory'])
            ->get()
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'barcode' => $variant->barcode,
                    'product_name' => $variant->product->name ?? 'N/A',
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'purchase_price' => $variant->purchase_price,
                    'sale_price' => $variant->sale_price,
                    'stock_quantity' => $variant->stock_quantity,
                    'stock_value' => $variant->stock_quantity * $variant->purchase_price,
                ];
            });

        return response()->json([
            'total_items' => $stock->count(),
            'total_stock_value' => $stock->sum('stock_value'),
            'data' => $stock
        ]);
    }

    /**
     * Low Stock Alert Report
     */
    public function lowStock()
    {
        $lowStock = ProductVariant::with('product')
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->get();

        return response()->json($lowStock);
    }

    /**
     * Dead Stock Report (No sales in 30 days)
     */
    public function deadStock()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $deadStock = ProductVariant::with('product')
            ->whereDoesntHave('sales', function ($query) use ($thirtyDaysAgo) {
                $query->where('sale_date', '>=', $thirtyDaysAgo);
            })
            ->where('stock_quantity', '>', 0)
            ->get();

        return response()->json($deadStock);
    }
}
