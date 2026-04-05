<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('customers.index') }}" class="p-2 bg-white rounded-xl text-slate-400 hover:text-vibrant-indigo shadow-sm transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-slate-800 leading-tight">
                        Customer Ledger
                    </h2>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $customer->name }} ({{ $customer->phone }})</p>
                </div>
            </div>
            
            <div class="flex gap-4">
                <div class="px-6 py-3 bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col items-end">
                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Current Balance</span>
                    <span class="font-black text-xl {{ $customer->previous_due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        ৳{{ number_format($customer->previous_due, 2) }}
                        <span class="text-[10px]">{{ $customer->previous_due > 0 ? 'DUE' : 'ADV' }}</span>
                    </span>
                </div>
                <button @click="printLedger()" class="px-6 py-2.5 bg-slate-900 text-white font-black rounded-xl shadow-lg flex items-center gap-2 hover:bg-slate-800 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2h6a2 2 0 002 2v4"/></svg>
                    Print
                </button>
            </div>
        </div>
    </x-slot>

    <div v-pre class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Timeline Layout --}}
            <div class="space-y-4">
                @forelse ($ledgers as $entry)
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 group hover:border-vibrant-indigo transition-all relative overflow-hidden">
                        {{-- Type Indicator --}}
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $entry->debit > 0 ? 'bg-rose-500' : 'bg-emerald-500' }}"></div>
                        
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex gap-4 items-center">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center {{ $entry->debit > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                                    @if($entry->type === 'sale')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-800 uppercase tracking-tight" x-text="'{{ $entry->type }}'.toUpperCase()"></h4>
                                    <p class="text-xs font-bold text-slate-400">{{ $entry->created_at->format('M d, Y - h:i A') }}</p>
                                </div>
                            </div>

                            <div class="flex-1 px-4">
                                <p class="text-sm font-bold text-slate-600">{{ $entry->note }}</p>
                                <p class="text-[10px] font-black text-vibrant-indigo uppercase tracking-widest mt-1">Ref: #{{ $entry->reference_id }}</p>
                            </div>

                            <div class="flex items-center gap-8 min-w-[200px] justify-end">
                                <div class="text-right">
                                    <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Amount</span>
                                    <span class="font-black text-lg {{ $entry->debit > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $entry->debit > 0 ? '+ ৳' . number_format($entry->debit, 2) : '- ৳' . number_format($entry->credit, 2) }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Running Due</span>
                                    <span class="font-black text-slate-900">৳{{ number_format($entry->balance, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white p-20 rounded-[3rem] text-center border-2 border-dashed border-slate-100">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                        </div>
                        <h3 class="font-black text-xl text-slate-800 mb-2">Clean Slate</h3>
                        <p class="text-slate-400 font-bold max-w-xs mx-auto">No transactions have been recorded for this customer yet.</p>
                    </div>
                @endforelse

                <div class="py-10">
                    {{ $ledgers->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
