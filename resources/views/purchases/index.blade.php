<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-slate-800 leading-tight">
                Purchase Orders
            </h2>
            <a href="{{ route('purchases.create') }}" class="px-6 py-2.5 bg-vibrant-indigo text-white font-black rounded-xl shadow-lg shadow-indigo-100 hover:shadow-indigo-200 transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Purchase
            </a>
        </div>
    </x-slot>

    <div v-pre class="py-12 bg-slate-50 min-h-screen" x-data="purchaseManager()" x-init="fetchPurchases()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Filters --}}
            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="relative flex-1 group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-vibrant-indigo transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                        </span>
                        <input type="text" x-model="search" @input.debounce.300ms="fetchPurchases()"
                               placeholder="Search invoice or supplier..." 
                               class="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-slate-700 transition-all">
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Invoice</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Supplier</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Total</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Paid</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Due</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <template x-for="purchase in purchases" :key="purchase.id">
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-8 py-6">
                                        <div class="font-black text-slate-900" x-text="purchase.invoice_no"></div>
                                        <div class="text-[10px] font-bold text-slate-400" x-text="'Creator: ' + purchase.creator.name"></div>
                                    </td>
                                    <td class="px-8 py-6 font-black text-slate-700" x-text="purchase.supplier.name"></td>
                                    <td class="px-8 py-6 font-bold text-slate-500" x-text="new Date(purchase.purchase_date).toLocaleDateString()"></td>
                                    <td class="px-8 py-6 font-black text-slate-900" x-text="'৳' + purchase.total_amount"></td>
                                    <td class="px-8 py-6 font-black text-emerald-600" x-text="'৳' + purchase.paid_amount"></td>
                                    <td class="px-8 py-6">
                                        <span :class="parseFloat(purchase.due_amount) > 0 ? 'text-rose-600' : 'text-slate-400'" class="font-black" x-text="'৳' + purchase.due_amount"></span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex gap-2">
                                            <button @click="viewDetails(purchase.id)" class="p-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-vibrant-indigo hover:text-white transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function purchaseManager() {
        return {
            purchases: [],
            search: '',
            loading: false,

            async fetchPurchases() {
                this.loading = true;
                const params = new URLSearchParams();
                if (this.search) params.set('search', this.search);

                try {
                    const res = await fetch(`/purchases?${params}`, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    this.purchases = data.data;
                } catch (e) { console.error(e); }
                finally { this.loading = false; }
            },

            viewDetails(id) {
                window.location.href = `/purchases/${id}`;
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
