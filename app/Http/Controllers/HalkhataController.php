<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\HalkhataHistory;
use Illuminate\Support\Facades\DB;

class HalkhataController extends Controller
{
    public function reset(Request $request)
    {
        $request->validate([
            'fiscal_year' => 'required|string',
            'note' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $customers = Customer::where('previous_due', '>', 0)->get();
            $histories = [];

            foreach ($customers as $customer) {
                // 1. Create History Snapshot
                $histories[] = HalkhataHistory::create([
                    'customer_id' => $customer->id,
                    'fiscal_year' => $request->fiscal_year,
                    'opening_due' => $customer->ledgers()->where('type', 'opening_balance')->whereYear('created_at', now()->year)->value('debit') ?? 0,
                    'closing_due' => $customer->previous_due,
                    'total_paid_in_year' => $customer->ledgers()->where('type', 'payment')->whereYear('created_at', now()->year)->sum('credit'),
                ]);

                // 2. Create Ledger Reset Entry
                $customer->ledgers()->create([
                    'type' => 'halkhata_reset',
                    'credit' => $customer->previous_due, // Clear current ledger view balance conceptually
                    'balance' => 0,
                    'note' => "Halkhata Reset for Year " . $request->fiscal_year,
                ]);

                // 3. Carry forward as new opening balance
                $customer->ledgers()->create([
                    'type' => 'opening_balance',
                    'debit' => $customer->previous_due,
                    'balance' => $customer->previous_due,
                    'note' => "Opening Balance for New Year",
                ]);
            }

            return response()->json(['message' => 'Halkhata reset completed', 'count' => count($histories)]);
        });
    }
}
