<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ReturnItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    /**
     * Daily Sales Report
     */
    public function daily(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        
        $sales = Sale::with(['customer', 'paymentMethod'])
            ->whereDate('sale_date', $date)
            ->get();

        return response()->json([
            'date' => $date,
            'total_sales' => $sales->sum('total_amount'),
            'total_invoices' => $sales->count(),
            'total_paid' => $sales->sum('paid_amount'),
            'total_due' => $sales->sum('due_amount'),
            'data' => $sales
        ]);
    }

    /**
     * Monthly Sales Report
     */
    public function monthly(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        $sales = Sale::select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('COUNT(*) as total_invoices'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(paid_amount) as total_paid'),
                DB::raw('SUM(due_amount) as total_due')
            )
            ->whereMonth('sale_date', $month)
            ->whereYear('sale_date', $year)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'month' => $month,
            'year' => $year,
            'summary' => [
                'total_amount' => $sales->sum('total_amount'),
                'total_paid' => $sales->sum('total_paid'),
                'total_due' => $sales->sum('total_due'),
            ],
            'daily_data' => $sales
        ]);
    }

    /**
     * Top Selling Products
     */
    public function topProducts(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $topProducts = SaleItem::select('product_variant_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total) as total_revenue'))
            ->with(['variant.product'])
            ->groupBy('product_variant_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();

        return response()->json($topProducts);
    }

    /**
     * Sales Returns Report
     */
    public function returns(Request $request)
    {
        $query = ReturnItem::with(['sale', 'variant.product']);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        return response()->json($query->latest()->paginate(20));
    }
}
