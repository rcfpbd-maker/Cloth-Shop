<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ProductVariant;
use App\Models\Expense;
use App\Models\DayClosing;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Aggregate Business KPIs & Rankings
     */
    public function index(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('dashboard');
        }

        $today = Carbon::today()->toDateString();
        
        // Basic Stats
        $todaySales = Sale::whereDate('sale_date', $today)->sum('total_amount');
        $todayPurchase = Purchase::whereDate('purchase_date', $today)->sum('total_amount');
        $todayExpense = Expense::whereDate('expense_date', $today)->sum('amount');
        $todayCollection = Payment::whereIn('reference_type', ['sale', 'customer'])
            ->whereDate('payment_date', $today)
            ->sum('amount');

        // Profit Calculation (Item-wise)
        $saleIdsToday = Sale::whereDate('sale_date', $today)->pluck('id');
        /** @var object|null $profitDataToday */
        $profitDataToday = DB::table('sale_items')
            ->join('product_variants', 'sale_items.product_variant_id', '=', 'product_variants.id')
            ->whereIn('sale_items.sale_id', $saleIdsToday)
            ->select(DB::raw('SUM((sale_items.price * sale_items.quantity - sale_items.discount) - (product_variants.purchase_price * sale_items.quantity)) as total_profit'))
            ->first();
            
        $todayGrossProfit = $profitDataToday && isset($profitDataToday->total_profit) ? (float) $profitDataToday->total_profit : 0;
        $todayNetProfit = $todayGrossProfit - $todayExpense;

        // Cash in Hand (from latest day closing)
        $cashInHand = DayClosing::latest('closing_date')->first()->closing_cash ?? 0;

        // Cachable Heavy Queries
        $totalStockValue = Cache::remember('total_stock_value', 3600, function () {
            /** @var object|null $stockData */
            $stockData = DB::table('product_variants')
                ->select(DB::raw('SUM(stock_quantity * purchase_price) as total_value'))
                ->first();
            return $stockData && isset($stockData->total_value) ? (float) $stockData->total_value : 0;
        });

        $lowStockCount = Cache::remember('low_stock_count', 3600, function () {
            return ProductVariant::whereColumn('stock_quantity', '<=', 'reorder_level')->count();
        });

        // Rankings
        $topProducts = SaleItem::select('product_variant_id', DB::raw('SUM(quantity) as total_qty'))
            ->with(['variant.product'])
            ->groupBy('product_variant_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $topCreditCustomers = Customer::where('previous_due', '>', 0)
            ->orderByDesc('previous_due')
            ->limit(5)
            ->get();

        return response()->json([
            'kpis' => [
                'today_sales' => round($todaySales, 2),
                'today_purchase' => round($todayPurchase, 2),
                'today_expense' => round($todayExpense, 2),
                'today_collection' => round($todayCollection, 2),
                'today_net_profit' => round($todayNetProfit, 2),
                'cash_in_hand' => round($cashInHand, 2),
                'total_stock_value' => round($totalStockValue, 2),
                'total_receivable' => round(Customer::sum('previous_due'), 2),
                'total_payable' => round(Supplier::sum('previous_due'), 2),
            ],
            'alerts' => [
                'low_stock_count' => $lowStockCount,
                'dead_stock_count' => ProductVariant::whereDoesntHave('sales', function($q) {
                    $q->where('sale_date', '>', now()->subDays(30));
                })->where('stock_quantity', '>', 0)->count(),
            ],
            'rankings' => [
                'top_products' => $topProducts,
                'top_customers' => $topCreditCustomers,
            ]
        ]);
    }

    /**
     * Data for Sales/Profit Charts
     */
    public function charts()
    {
        $last7Days = collect(range(0, 6))->map(function ($i) {
            return Carbon::today()->subDays($i)->toDateString();
        })->reverse()->values();

        $chartData = $last7Days->map(function ($date) {
            $salesTotal = Sale::whereDate('sale_date', $date)->sum('total_amount');
            
            $saleIds = Sale::whereDate('sale_date', $date)->pluck('id');
            /** @var object|null $profitData */
            $profitData = DB::table('sale_items')
                ->join('product_variants', 'sale_items.product_variant_id', '=', 'product_variants.id')
                ->whereIn('sale_items.sale_id', $saleIds)
                ->select(DB::raw('SUM((sale_items.price * sale_items.quantity - sale_items.discount) - (product_variants.purchase_price * sale_items.quantity)) as profit'))
                ->first();
            
            $expenses = Expense::whereDate('expense_date', $date)->sum('amount');
            $grossProfit = $profitData && isset($profitData->profit) ? (float) $profitData->profit : 0;

            return [
                'date' => $date,
                'sales' => round($salesTotal, 2),
                'profit' => round($grossProfit - $expenses, 2),
            ];
        });

        return response()->json($chartData);
    }
}
