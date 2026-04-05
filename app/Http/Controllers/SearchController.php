<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across products, customers, and suppliers
     */
    public function globalSearch(Request $request)
    {
        $query = $request->query('q');
        if (!$query) {
            return response()->json(['products' => [], 'customers' => [], 'suppliers' => []]);
        }

        $results = [];

        // Search Products
        if (auth()->user()->can('products.view')) {
            $results['products'] = ProductVariant::with('product')
                ->where('sku', 'like', "%{$query}%")
                ->orWhere('barcode', 'like', "%{$query}%")
                ->orWhereHas('product', function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get();
        }

        // Search Customers
        if (auth()->user()->can('customers.view')) {
            $results['customers'] = Customer::where('name', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->limit(5)
                ->get();
        }

        // Search Suppliers
        if (auth()->user()->hasRole(['Admin', 'Manager'])) { // Assuming manage suppliers permission or specific check
            $results['suppliers'] = Supplier::where('name', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->limit(5)
                ->get();
        }

        return response()->json($results);
    }

    /**
     * Product specific search
     */
    public function searchProducts(Request $request)
    {
        $query = $request->query('q');
        $products = ProductVariant::with('product')
            ->where('sku', 'like', "%{$query}%")
            ->orWhere('barcode', 'like', "%{$query}%")
            ->orWhereHas('product', function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->paginate(20);

        return response()->json($products);
    }
}
