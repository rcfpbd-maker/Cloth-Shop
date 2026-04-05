<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CashbookController extends Controller
{
    /**
     * Unified view of cash flow
     */
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        
        $payments = Payment::with(['paymentMethod', 'reference', 'creator'])
            ->whereDate('payment_date', $date)
            ->get()
            ->map(function ($p) {
                return [
                    'date' => $p->payment_date,
                    'type' => in_array($p->reference_type, ['sale', 'customer']) ? 'Cash In' : 'Cash Out',
                    'amount' => $p->amount,
                    'method' => $p->paymentMethod->name ?? 'N/A',
                    'reference' => $p->reference_type . ' #' . $p->reference_id,
                    'note' => $p->note,
                    'creator' => $p->creator->name ?? 'N/A',
                ];
            });

        $expenses = Expense::with(['category', 'paymentMethod', 'creator'])
            ->whereDate('expense_date', $date)
            ->get()
            ->map(function ($e) {
                return [
                    'date' => $e->expense_date,
                    'type' => 'Cash Out (Expense)',
                    'amount' => $e->amount,
                    'method' => $e->paymentMethod->name ?? 'N/A',
                    'reference' => 'Expense: ' . ($e->category->name ?? 'N/A'),
                    'note' => $e->description,
                    'creator' => $e->creator->name ?? 'N/A',
                ];
            });

        $combined = $payments->concat($expenses)->sortByDesc('date')->values();

        return response()->json([
            'date' => $date,
            'summary' => [
                'total_in' => $payments->where('type', 'Cash In')->sum('amount'),
                'total_out' => $payments->where('type', 'Cash Out')->sum('amount') + $expenses->sum('amount'),
            ],
            'transactions' => $combined
        ]);
    }
}
