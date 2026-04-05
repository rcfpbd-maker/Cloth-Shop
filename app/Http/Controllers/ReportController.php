<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\Expense;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * High-level dashboard summary
     */
    public function summary()
    {
        $today = Carbon::today()->toDateString();
        
        return response()->json([
            'today_sales' => round(Sale::whereDate('sale_date', $today)->sum('total_amount'), 2),
            'today_collection' => round(Sale::whereDate('sale_date', $today)->sum('paid_amount'), 2),
            'today_expenses' => round(Expense::whereDate('expense_date', $today)->sum('amount'), 2),
            'total_active_customers' => Customer::count(),
            'total_products' => ProductVariant::count(),
            'low_stock_count' => ProductVariant::whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
        ]);
    }

    public function salesReport()
    {
        return 'Sales Report - Backend Logic Pending';
    }

    public function stockReport()
    {
        return 'Stock Report - Backend Logic Pending';
    }

    public function financeReport()
    {
        return 'Finance Report - Backend Logic Pending';
    }
}
