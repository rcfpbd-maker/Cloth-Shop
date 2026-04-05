<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\Customer;
use App\Models\CustomerLedger;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
        }

        $customers = $query->latest()->paginate(20);

        if ($request->ajax()) {
            return response()->json($customers);
        }

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('customers.index');
    }

    /**
     * Display the specified resource ledger.
     */
    public function ledger(Customer $customer, Request $request)
    {
        $query = $customer->ledgers()->latest();

        $ledger = $query->paginate(30);

        if ($request->ajax()) {
            return response()->json([
                'customer' => $customer,
                'ledger' => $ledger
            ]);
        }

        return view('customers.ledger', compact('customer', 'ledger'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::create($validated);

        return response()->json($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }
}
