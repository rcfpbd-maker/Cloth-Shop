<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Purchase;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * List suppliers with optional search
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
        }

        $suppliers = $query->latest()->paginate(20);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($suppliers);
        }

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show a specific supplier with purchase history
     */
    public function show(Request $request, Supplier $supplier)
    {
        $supplier->load(['purchases' => function ($q) {
            $q->latest()->limit(20);
        }]);

        $stats = [
            'total_purchases' => $supplier->purchases()->count(),
            'total_purchase_amount' => $supplier->purchases()->sum('total_amount'),
            'total_paid' => $supplier->purchases()->sum('paid_amount'),
            'outstanding_due' => $supplier->previous_due,
        ];

        return response()->json([
            'supplier' => $supplier,
            'stats' => $stats,
        ]);
    }

    /**
     * Create a new supplier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'required|string|max:20|unique:suppliers',
            'email'        => 'nullable|email|max:255|unique:suppliers',
            'address'      => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
        ]);

        $supplier = Supplier::create($validated);

        $this->activityLogService->log('inventory', 'create', "Created supplier: {$supplier->name}", $supplier->id);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Supplier created successfully',
            'supplier' => $supplier,
        ], 201);
    }

    /**
     * Update an existing supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'required|string|max:20|unique:suppliers,phone,' . $supplier->id,
            'email'        => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'address'      => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'status'       => 'nullable|in:active,inactive',
        ]);

        $supplier->update($validated);

        $this->activityLogService->log('inventory', 'update', "Updated supplier: {$supplier->name}", $supplier->id);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Supplier updated successfully',
            'supplier' => $supplier,
        ]);
    }

    /**
     * Delete a supplier (blocked if they have outstanding dues or purchases)
     */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->previous_due > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => "Cannot delete supplier. Outstanding due of ৳{$supplier->previous_due} must be cleared first.",
            ], 422);
        }

        if ($supplier->purchases()->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot delete supplier with existing purchase records.',
            ], 422);
        }

        $name = $supplier->name;
        $supplier->delete();

        $this->activityLogService->log('inventory', 'delete', "Deleted supplier: {$name}");

        return response()->json([
            'status'  => 'success',
            'message' => 'Supplier deleted successfully',
        ]);
    }

    /**
     * Supplier payment ledger — all purchases with due tracking
     */
    public function ledger(Request $request, Supplier $supplier)
    {
        $purchases = $supplier->purchases()
            ->with(['items.variant.product'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'supplier' => $supplier,
            'ledger'   => $purchases,
        ]);
    }
}
