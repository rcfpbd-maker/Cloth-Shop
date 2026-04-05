<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\DayClosing;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    /**
     * Financial summary for dashboard
     */
    public function summary(Request $request)
    {
        $range = $request->get('range', 'today'); // today, month, year
        $query = Sale::query();
        $expenseQuery = Expense::query();
        $collectionQuery = Payment::whereIn('reference_type', ['sale', 'customer']);

        if ($range === 'today') {
            $query->whereDate('sale_date', Carbon::today());
            $expenseQuery->whereDate('expense_date', Carbon::today());
            $collectionQuery->whereDate('payment_date', Carbon::today());
        } elseif ($range === 'month') {
            $query->whereMonth('sale_date', Carbon::now()->month);
            $expenseQuery->whereMonth('expense_date', Carbon::now()->month);
            $collectionQuery->whereMonth('payment_date', Carbon::now()->month);
        }

        $totalSales = $query->sum('total_amount');
        $totalExpenses = $expenseQuery->sum('amount');
        $totalCollection = $collectionQuery->sum('amount');
        
        // Profit calculation based on items
        $totalProfit = 0;
        $saleIds = $query->pluck('id');
        
        $profitData = DB::table('sale_items')
            ->join('product_variants', 'sale_items.product_variant_id', '=', 'product_variants.id')
            ->whereIn('sale_items.sale_id', $saleIds)
            ->select(DB::raw('SUM((sale_items.price * sale_items.quantity - sale_items.discount) - (product_variants.purchase_price * sale_items.quantity)) as total_profit'))
            ->first();

        $netProfit = ($profitData->total_profit ?? 0) - $totalExpenses;

        return response()->json([
            'range' => $range,
            'total_sales' => round($totalSales, 2),
            'total_collection' => round($totalCollection, 2),
            'total_expenses' => round($totalExpenses, 2),
            'gross_profit' => round($profitData->total_profit ?? 0, 2),
            'net_profit' => round($netProfit, 2),
            'current_cash_hand' => round(DayClosing::latest('closing_date')->first()->closing_cash ?? 0, 2)
        ]);
    }
}
