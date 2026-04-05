<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Calculate risks based on debt age
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $sixtyDaysAgo = Carbon::now()->subDays(60);
        $ninetyDaysAgo = Carbon::now()->subDays(90);

        // Fetch top debtors
        $topDebtors = Customer::where('previous_due', '>', 0)
            ->orderByDesc('previous_due')
            ->limit(10)
            ->get();

        // Categorize debt:
        // We'll approximate debt age by looking at the oldest unpaid sale,
        // but for simplicity, we'll categorize customers generally by when they last paid versus their due.
        // If a customer has due and hasn't paid in 90 days = High Risk
        // If they haven't paid in 30-90 days = Medium Risk
        // Under 30 days = Low Risk
        
        // This is a simplified approach on the fly for the dashboard
        $customersWithDues = Customer::where('previous_due', '>', 0)->get();
        
        $highRisk = 0;
        $mediumRisk = 0;
        $lowRisk = 0;
        
        $highRiskAmount = 0;
        $mediumRiskAmount = 0;
        $lowRiskAmount = 0;
        
        $totalReceivable = 0;

        foreach ($customersWithDues as $customer) {
            $totalReceivable += $customer->previous_due;
            
            // Get last payment
            $lastPayment = Payment::where('reference_type', 'customer')
                ->where('reference_id', $customer->id)
                ->orderByDesc('payment_date')
                ->first();
                
            $lastPaymentDate = $lastPayment ? Carbon::parse($lastPayment->payment_date) : null;
            
            // If they never paid, look at last sale
            if (!$lastPaymentDate) {
                $lastSale = Sale::where('customer_id', $customer->id)->orderByDesc('sale_date')->first();
                $lastPaymentDate = $lastSale ? Carbon::parse($lastSale->sale_date) : Carbon::now()->subDays(100);
            }
            
            if ($lastPaymentDate->lt($ninetyDaysAgo)) {
                $highRisk++;
                $highRiskAmount += $customer->previous_due;
            } elseif ($lastPaymentDate->lt($thirtyDaysAgo)) {
                $mediumRisk++;
                $mediumRiskAmount += $customer->previous_due;
            } else {
                $lowRisk++;
                $lowRiskAmount += $customer->previous_due;
            }
        }
        
        // Let's get the detailed risk list for the table
        // We will just map it dynamically
        $riskCustomers = Customer::where('previous_due', '>', 0)
            ->with(['sales' => function($q) {
                $q->latest('sale_date')->limit(1);
            }])
            ->get()->map(function($c) use ($thirtyDaysAgo, $ninetyDaysAgo) {
                $lastPayment = Payment::where('reference_type', 'customer')->where('reference_id', $c->id)->latest('payment_date')->first();
                $lastDate = $lastPayment ? Carbon::parse($lastPayment->payment_date) : ($c->sales->first() ? Carbon::parse($c->sales->first()->sale_date) : Carbon::now()->subDays(100));
                
                $daysSince = $lastDate->diffInDays(Carbon::now());
                $status = 'Low';
                if ($daysSince >= 90) $status = 'High';
                elseif ($daysSince >= 30) $status = 'Medium';
                
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'phone' => $c->phone,
                    'due' => $c->previous_due,
                    'last_payment' => $lastDate->format('Y-m-d'),
                    'days_since' => $daysSince,
                    'status' => $status
                ];
            })->sortByDesc('days_since')->values();

        return view('reports.customer-dashboard', compact(
            'topDebtors',
            'highRisk', 'mediumRisk', 'lowRisk',
            'highRiskAmount', 'mediumRiskAmount', 'lowRiskAmount',
            'totalReceivable',
            'riskCustomers'
        ));
    }
}
