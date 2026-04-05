<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Customer;
use App\Models\Category;
use Illuminate\Http\Request;

class POSController extends Controller
{
    /**
     * Get data for POS interface
     */
    public function index()
    {
        return response()->json([
            'customers' => Customer::select('id', 'name', 'phone', 'previous_due', 'credit_limit')->get(),
            'categories' => Category::select('id', 'name')->get(),
            'payment_methods' => \App\Models\PaymentMethod::select('id', 'name')->get(),
            'products' => ProductVariant::with('product:id,name')
                ->where('stock_quantity', '>', 0)
                ->limit(50)
                ->get(),
        ]);
    }

    /**
     * Return the POS HTML view.
     */
    public function indexView()
    {
        return view('pos.index');
    }

    /**
     * Search products by name, SKU, or Barcode
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        $products = ProductVariant::with('product:id,name')
            ->where(function(\Illuminate\Database\Eloquent\Builder $q) use ($query) {
                $q->where('sku', 'LIKE', "%$query%")
                  ->orWhere('barcode', 'LIKE', "%$query%")
                  ->orWhereHas('product', function(\Illuminate\Database\Eloquent\Builder $pq) use ($query) {
                      $pq->where('name', 'LIKE', "%$query%");
                  });
            })
            ->where('stock_quantity', '>', 0)
            ->get();

        return response()->json($products);
    }
}
