<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-800 leading-tight">
            Halkhata Yearly Reset
        </h2>
    </x-slot>

    <div v-pre class="py-12 bg-slate-50 min-h-screen" x-data="halkhataManager()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-[3rem] p-12 border border-slate-100">
                
                <div class="flex flex-col items-center text-center mb-12">
                    <div class="w-24 h-24 bg-amber-50 rounded-full flex items-center justify-center text-amber-600 mb-6">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    </div>
                    <h3 class="font-black text-3xl text-slate-900 mb-4">Fiscal Year Closing</h3>
                    <p class="text-slate-400 font-bold max-w-md">
                        This action will finalize the current year's ledger for all customers and carry over outstanding balances as new opening entries for the next year.
                    </p>
                </div>

                @php
                    $pendingDues = \App\Models\Customer::where('previous_due', '>', 0)->count();
                    $totalDues = \App\Models\Customer::sum('previous_due');
                @endphp

                <div class="grid grid-cols-2 gap-6 mb-12">
                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Customers with Dues</span>
                        <span class="block text-2xl font-black text-slate-900 text-center">{{ $pendingDues }} Users</span>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-center">Total Receivable</span>
                        <span class="block text-2xl font-black text-rose-600 text-center">৳{{ number_format($totalDues, 2) }}</span>
                    </div>
                </div>

                <form @submit.prevent="confirmReset" class="space-y-6">
                    <div class="bg-rose-50 p-6 rounded-3xl border border-rose-100 mb-8">
                        <div class="flex gap-4">
                            <svg class="w-6 h-6 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p class="text-xs font-bold text-rose-700 leading-relaxed">
                                <span class="block font-black text-sm mb-1 uppercase tracking-tight">Irreversible Action</span>
                                Performing a Halkhata Reset cannot be undone. Please ensure you have backed up your database before proceeding.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">New Fiscal Year Name</label>
                            <input type="text" x-model="payload.fiscal_year" required placeholder="e.g. 2024-2025" 
                                   class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-black">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Internal Notes</label>
                            <input type="text" x-model="payload.note" placeholder="Optional"
                                   class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-bold">
                        </div>
                    </div>

                    <div class="pt-8 flex flex-col items-center gap-4">
                        <button type="submit" :disabled="loading"
                                class="w-full py-5 bg-rose-600 text-white font-black rounded-3xl shadow-xl shadow-rose-200 hover:bg-rose-700 transition-all flex items-center justify-center gap-3">
                            <svg x-show="loading" class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span x-text="loading ? 'Finalizing Year...' : 'Start Halkhata Reset'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function halkhataManager() {
        return {
            loading: false,
            payload: {
                fiscal_year: new Date().getFullYear(),
                note: ''
            },

            async confirmReset() {
                if (!confirm('Are you absolutely sure? This will reset all current customer ledgers and carry over dues to the new year.')) return;
                
                this.loading = true;
                try {
                    const res = await fetch('/halkhata/reset', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.payload)
                    });
                    const data = await res.json();
                    alert(data.message);
                    window.location.reload();
                } catch (e) {
                    alert('An error occurred during reset.');
                } finally {
                    this.loading = false;
                }
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
