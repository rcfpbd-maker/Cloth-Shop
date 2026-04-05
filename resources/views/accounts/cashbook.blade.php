<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center text-slate-800">
            <h2 class="font-black text-2xl leading-tight">Master Cashbook</h2>
            <div class="flex items-center gap-4">
                <input type="date" value="{{ $date }}" @change="window.location.href = '?date=' + $event.target.value"
                       class="px-6 py-2.5 bg-white border-none rounded-2xl shadow-sm text-sm font-black focus:ring-2 focus:ring-vibrant-indigo transition-all">
            </div>
        </div>
    </x-slot>

    <div v-pre class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Summary Ribbon --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Total Cash In</span>
                    <span class="block text-3xl font-black text-emerald-600 text-center">৳{{ number_format($summary['total_in'], 2) }}</span>
                </div>
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Total Cash Out</span>
                    <span class="block text-3xl font-black text-rose-600 text-center">৳{{ number_format($summary['total_out'], 2) }}</span>
                </div>
                <div class="bg-vibrant-indigo p-8 rounded-[2.5rem] shadow-xl shadow-indigo-100">
                    <span class="block text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-2 text-center">Daily Net</span>
                    <span class="block text-3xl font-black text-white text-center">৳{{ number_format($summary['total_in'] - $summary['total_out'], 2) }}</span>
                </div>
            </div>

            {{-- Transactions Timeline --}}
            <div class="bg-white rounded-[3rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-50 flex justify-between items-center">
                    <h3 class="font-black text-xl text-slate-800">Transaction History</h3>
                    <span class="px-4 py-1.5 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-widest">
                        Date: {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Reference</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Method</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Time</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Debit (In)</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Credit (Out)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($transactions as $t)
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-10 py-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2.5 h-2.5 rounded-full {{ str_contains($t['type'], 'In') ? 'bg-emerald-500 shadow-lg shadow-emerald-200' : 'bg-rose-500 shadow-lg shadow-rose-200' }}"></div>
                                            <span class="font-black text-slate-700 text-xs uppercase tracking-tight">{{ $t['type'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-6">
                                        <p class="font-bold text-slate-800 text-sm">{{ $t['reference'] }}</p>
                                        <p class="text-[10px] font-bold text-slate-400">{{ $t['note'] ?? 'No notes' }}</p>
                                    </td>
                                    <td class="px-10 py-6">
                                        <span class="font-black text-vibrant-indigo text-[10px] uppercase tracking-widest">{{ $t['method'] }}</span>
                                    </td>
                                    <td class="px-10 py-6 font-bold text-slate-400 text-xs">
                                        {{ \Carbon\Carbon::parse($t['date'])->format('h:i A') }}
                                    </td>
                                    <td class="px-10 py-6 text-right font-black {{ str_contains($t['type'], 'In') ? 'text-emerald-600' : 'text-slate-300' }}">
                                        {{ str_contains($t['type'], 'In') ? '৳' . number_format($t['amount'], 2) : '—' }}
                                    </td>
                                    <td class="px-10 py-6 text-right font-black {{ !str_contains($t['type'], 'In') ? 'text-rose-600' : 'text-slate-300' }}">
                                        {{ !str_contains($t['type'], 'In') ? '৳' . number_format($t['amount'], 2) : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-10 py-20 text-center">
                                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                            <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                                        </div>
                                        <h4 class="font-black text-xl text-slate-800 mb-2">No Transactions Today</h4>
                                        <p class="text-slate-400 font-bold max-w-xs mx-auto text-sm">Either the shop hasn't opened yet, or there was a very slow business day.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
