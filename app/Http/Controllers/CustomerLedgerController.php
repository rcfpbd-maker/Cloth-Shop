<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class CustomerLedgerController extends Controller
{
    public function index(Request $request, $customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $ledgers = $customer->ledgers()->latest()->paginate(50);
        
        return response()->json([
            'customer' => $customer,
            'ledgers' => $ledgers
        ]);
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'note' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $customer = Customer::findOrFail($request->customer_id);
            
            // 1. Create Payment
            $payment = Payment::create([
                'reference_type' => 'customer',
                'reference_id' => $customer->id,
                'payment_method_id' => $request->payment_method_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'note' => $request->note,
                'created_by' => auth()->id(),
            ]);

            // 2. Update Customer Balance
            $newBalance = $customer->updateBalance($request->amount, 'credit');

            // 3. Create Ledger Entry
            $customer->ledgers()->create([
                'type' => 'payment',
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'credit' => $request->amount,
                'balance' => $newBalance,
                'note' => $request->note ?? 'Installment Payment',
            ]);

            return response()->json(['message' => 'Payment recorded successfully', 'payment' => $payment]);
        });
    }
}
