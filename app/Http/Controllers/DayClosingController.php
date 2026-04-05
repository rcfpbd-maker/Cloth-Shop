<?php

namespace App\Http\Controllers;

use App\Models\DayClosing;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DayClosingController extends Controller
{
    /**
     * Get preview data for today's closing
     */
    public function preview()
    {
        $today = Carbon::today()->toDateString();
        $prevClosing = DayClosing::latest('closing_date')->first();
        
        $openingCash = $prevClosing ? $prevClosing->closing_cash : 0;
        
        $totalSales = Sale::whereDate('sale_date', $today)->sum('total_amount');
        $totalCollection = Payment::whereIn('reference_type', ['sale', 'customer'])
            ->whereDate('payment_date', $today)
            ->sum('amount');
        
        $totalExpense = Expense::whereDate('expense_date', $today)->sum('amount') + 
                        Payment::where('reference_type', 'purchase')
                            ->whereDate('payment_date', $today)
                            ->sum('amount');

        $closingCash = $openingCash + $totalCollection - $totalExpense;

        return response()->json([
            'date' => $today,
            'opening_cash' => $openingCash,
            'total_sales' => $totalSales,
            'total_collection' => $totalCollection,
            'total_expense' => $totalExpense,
            'estimated_closing_cash' => $closingCash,
            'is_closed' => DayClosing::where('closing_date', $today)->exists()
        ]);
    }

    /**
     * Finalize and store Day Closing
     */
    public function store(Request $request)
    {
        $today = Carbon::today()->toDateString();
        
        if (DayClosing::where('closing_date', $today)->exists()) {
            return response()->json(['message' => 'Closing already completed for today.'], 422);
        }

        // We re-calculate on backend for security
        $prevClosing = DayClosing::latest('closing_date')->first();
        $openingCash = $prevClosing ? $prevClosing->closing_cash : ($request->opening_cash ?? 0);
        
        $totalSales = Sale::whereDate('sale_date', $today)->sum('total_amount');
        $totalCollection = Payment::whereIn('reference_type', ['sale', 'customer'])
            ->whereDate('payment_date', $today)
            ->sum('amount');
        
        $totalExpense = Expense::whereDate('expense_date', $today)->sum('amount') + 
                        Payment::where('reference_type', 'purchase')
                            ->whereDate('payment_date', $today)
                            ->sum('amount');

        $closingCash = $openingCash + $totalCollection - $totalExpense;

        $closing = DayClosing::create([
            'closing_date' => $today,
            'opening_cash' => $openingCash,
            'total_sales' => $totalSales,
            'total_expense' => $totalExpense,
            'total_collection' => $totalCollection,
            'closing_cash' => $closingCash,
            'closed_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Day closed successfully',
            'closing' => $closing
        ]);
    }

    public function index()
    {
        return response()->json(DayClosing::with('closedBy')->latest('closing_date')->paginate(30));
    }
}
