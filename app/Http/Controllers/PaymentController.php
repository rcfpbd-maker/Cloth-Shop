<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

use App\Services\ActivityLogService;

class PaymentController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }
    public function index(Request $request)
    {
        $payments = Payment::with(['creator', 'paymentMethod', 'reference'])->latest()->paginate(20);
        
        if ($request->wantsJson()) {
            return response()->json($payments);
        }

        return view('payments.index', compact('payments'));
    }

    /**
     * Record a payment for a Sale or Customer
     */
    public function store(Request $request)
    {
        $request->validate([
            'reference_type' => 'required|in:sale,customer',
            'reference_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'note' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $payment = Payment::create([
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'payment_method_id' => $request->payment_method_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'note' => $request->note,
                'created_by' => auth()->id(),
            ]);

            if ($request->reference_type === 'sale') {
                $sale = Sale::findOrFail($request->reference_id);
                $sale->increment('paid_amount', $request->amount);
                $sale->decrement('due_amount', $request->amount);

                if ($sale->customer_id) {
                    $customer = $sale->customer;
                    $newBalance = $customer->updateBalance($request->amount, 'credit');
                    
                    $customer->ledgers()->create([
                        'type' => 'payment',
                        'reference_type' => Payment::class,
                        'reference_id' => $payment->id,
                        'credit' => $request->amount,
                        'balance' => $newBalance,
                        'note' => 'Payment for INV: ' . $sale->invoice_no,
                    ]);
                }
            } elseif ($request->reference_type === 'customer') {
                $customer = Customer::findOrFail($request->reference_id);
                $newBalance = $customer->updateBalance($request->amount, 'credit');

                $customer->ledgers()->create([
                    'type' => 'payment',
                    'reference_type' => Payment::class,
                    'reference_id' => $payment->id,
                    'credit' => $request->amount,
                    'balance' => $newBalance,
                    'note' => 'General Payment Entry',
                ]);
            }

            // Log activity
            $this->activityLogService->log('accounts', 'create', "Recorded payment of {$payment->amount} for {$payment->reference_type}", $payment->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment recorded successfully',
                'payment' => $payment
            ]);
        });
    }
}
