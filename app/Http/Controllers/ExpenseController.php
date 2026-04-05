<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

use App\Services\ActivityLogService;

class ExpenseController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }
    /**
     * Display a listing of shop expenses.
     */
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'paymentMethod', 'creator']);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->wantsJson()) {
            return response()->json($query->latest()->paginate(20));
        }

        $expenses = $query->latest()->paginate(20);
        return view('accounts.expenses', compact('expenses'));
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'description' => 'nullable|string',
        ]);

        $expense = Expense::create([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'payment_method_id' => $request->payment_method_id,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);

        // Log activity
        $this->activityLogService->log('accounts', 'create', "Recorded expense of {$expense->amount}", $expense->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense recorded successfully',
            'expense' => $expense->load('category')
        ]);
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(string $id)
    {
        $expense = Expense::findOrFail($id);
        $amount = $expense->amount;
        $expense->delete();

        // Log activity
        $this->activityLogService->log('accounts', 'delete', "Deleted expense of {$amount}", $id);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense deleted successfully'
        ]);
    }

    /**
     * Get expense categories for dropdowns.
     */
    public function categories()
    {
        return response()->json(ExpenseCategory::all());
    }
}
