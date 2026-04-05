<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Expense;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceReportController extends Controller
{
    /**
     * Profit & Loss Report
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $saleIds = Sale::whereBetween('sale_date', [$startDate, $endDate])->pluck('id');
        
        /** @var object|null $profitData */
        $profitData = DB::table('sale_items')
            ->join('product_variants', 'sale_items.product_variant_id', '=', 'product_variants.id')
            ->whereIn('sale_items.sale_id', $saleIds)
            ->select(DB::raw('SUM((sale_items.price * sale_items.quantity - sale_items.discount) - (product_variants.purchase_price * sale_items.quantity)) as total_profit'))
            ->first();

        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        
        $grossProfit = $profitData && isset($profitData->total_profit) ? (float) $profitData->total_profit : 0;
        $netProfit = $grossProfit - $totalExpenses;

        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'gross_profit' => round($grossProfit, 2),
            'total_expenses' => round($totalExpenses, 2),
            'net_profit' => round($netProfit, 2)
        ]);
    }

    /**
     * Customer Due Report
     */
    public function customerDues()
    {
        $dues = Customer::where('previous_due', '>', 0)
            ->orderByDesc('previous_due')
            ->get();

        return response()->json([
            'total_customers_with_due' => $dues->count(),
            'total_due_amount' => $dues->sum('previous_due'),
            'data' => $dues
        ]);
    }

    /**
     * Payment Method Wise Report
     */
    public function paymentMethods(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $summary = Payment::with('paymentMethod')
            ->select('payment_method_id', DB::raw('SUM(amount) as total'))
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->groupBy('payment_method_id')
            ->get();

        return response()->json($summary);
    }
}
