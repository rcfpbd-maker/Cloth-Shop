<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-2xl text-slate-900 leading-tight">
                    Customer Intelligence & Risks
                </h2>
                <p class="text-slate-500 font-medium text-sm mt-1">Analyze receivables, debt age, and financial risks across your customer base.</p>
            </div>
            
            <a href="{{ route('customers.index') }}" class="btn-vibrant">
                View All Customers
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Global Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Receivable -->
                <div class="card-glass p-6">
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Total Receivable Due</p>
                    <h3 class="text-3xl font-black text-slate-900">৳{{ number_format($totalReceivable, 2) }}</h3>
                </div>

                <!-- Low Risk -->
                <div class="card-glass p-6 border-b-4 border-emerald-500 hover:-translate-y-1 transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Age < 30 Days (Low)</p>
                            <h3 class="text-2xl font-black text-slate-900">৳{{ number_format($lowRiskAmount, 2) }}</h3>
                        </div>
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-black rounded-lg">{{ $lowRisk }} Customers</span>
                    </div>
                </div>

                <!-- Medium Risk -->
                <div class="card-glass p-6 border-b-4 border-amber-500 hover:-translate-y-1 transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Age 30-90 Days (Med)</p>
                            <h3 class="text-2xl font-black text-slate-900">৳{{ number_format($mediumRiskAmount, 2) }}</h3>
                        </div>
                        <span class="px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-black rounded-lg">{{ $mediumRisk }} Customers</span>
                    </div>
                </div>

                <!-- High Risk -->
                <div class="card-glass p-6 border-b-4 border-rose-600 hover:-translate-y-1 transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-bold text-rose-500 uppercase tracking-wider mb-2">Age > 90 Days (High)</p>
                            <h3 class="text-2xl font-black text-rose-700">৳{{ number_format($highRiskAmount, 2) }}</h3>
                        </div>
                        <span class="px-2.5 py-1 bg-rose-100 text-rose-700 text-xs font-black rounded-lg">{{ $highRisk }} Customers</span>
                    </div>
                </div>
            </div>

            <!-- Detailed Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Risk Table -->
                <div class="lg:col-span-2 card-glass p-6">
                    <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 text-rose-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Aging Debtors List
                    </h3>
                    
                    <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-wider">Customer</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-black text-slate-500 uppercase tracking-wider">Outstanding (৳)</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-black text-slate-500 uppercase tracking-wider">Days Since Last Paid</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-black text-slate-500 uppercase tracking-wider">Risk Level</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @forelse ($riskCustomers as $c)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <div class="text-sm font-black text-slate-900">{{ $c['name'] }}</div>
                                            <div class="text-xs font-medium text-slate-500">{{ $c['phone'] }}</div>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-bold text-slate-800">
                                            {{ number_format($c['due'], 2) }}
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-bold text-slate-600">
                                            {{ $c['days_since'] }} days
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-center">
                                            @if($c['status'] == 'High')
                                                <span class="inline-flex px-2 py-1 bg-rose-100 text-rose-700 text-xs font-bold rounded">High Risk</span>
                                            @elseif($c['status'] == 'Medium')
                                                <span class="inline-flex px-2 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded">Warning</span>
                                            @else
                                                <span class="inline-flex px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded">Safe</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-slate-500 font-medium">No active debtors found! Excellent.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Debtors -->
                <div class="card-glass p-6">
                    <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        Top 10 Highest Due
                    </h3>

                    <div class="space-y-4">
                        @foreach($topDebtors as $idx => $debtor)
                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors border border-slate-100">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-vibrant-indigo font-black flex items-center justify-center text-xs">
                                    {{ $idx + 1 }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 truncate w-32">{{ $debtor->name }}</p>
                                    <a href="{{ url('customers/'.$debtor->id.'/ledger') }}" class="text-[10px] uppercase font-bold text-vibrant-indigo hover:underline">View Ledger</a>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-rose-600">৳{{ number_format($debtor->previous_due, 2) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
