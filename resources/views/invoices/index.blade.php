<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-slate-800 leading-tight">
                Master Invoices
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('pos.index') }}" class="px-6 py-2.5 bg-vibrant-indigo text-white font-black rounded-xl shadow-lg shadow-indigo-100 hover:shadow-indigo-200 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Sale (POS)
                </a>
            </div>
        </div>
    </x-slot>

    <div v-pre class="py-12 bg-slate-50 min-h-screen" x-data="invoiceManager()" x-init="fetchInvoices()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Search & Filters --}}
            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="relative flex-1 group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-vibrant-indigo transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                        </span>
                        <input type="text" x-model="search" @input.debounce.300ms="fetchInvoices()"
                               placeholder="Search invoice number, customer, or supplier..." 
                               class="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-slate-700 transition-all">
                    </div>
                    
                    <div class="flex gap-2 bg-slate-50 p-1.5 rounded-[1.5rem]">
                        <button @click="typeFilter = ''; fetchInvoices()" 
                                :class="typeFilter === '' ? 'bg-white text-vibrant-indigo shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                                class="px-6 py-2.5 rounded-xl font-black text-sm transition-all">All</button>
                        <button @click="typeFilter = 'sale'; fetchInvoices()" 
                                :class="typeFilter === 'sale' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                                class="px-6 py-2.5 rounded-xl font-black text-sm transition-all">Sales</button>
                        <button @click="typeFilter = 'purchase'; fetchInvoices()" 
                                :class="typeFilter === 'purchase' ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                                class="px-6 py-2.5 rounded-xl font-black text-sm transition-all">Purchases</button>
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
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Entity</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Amount</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <template x-for="invoice in invoices" :key="invoice.id">
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-8 py-6">
                                        <div class="font-black text-slate-900" x-text="invoice.invoice_number"></div>
                                        <div class="text-[10px] font-bold text-slate-400" x-text="'Ref ID: #' + invoice.id"></div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="font-black text-slate-700" x-text="invoice.customer ? invoice.customer.name : (invoice.supplier ? invoice.supplier.name : 'Walk-in')"></div>
                                        <div class="text-[10px] font-bold text-slate-400" x-text="invoice.customer ? 'Customer' : (invoice.supplier ? 'Supplier' : 'N/A')"></div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span :class="{
                                            'bg-emerald-50 text-emerald-600': invoice.type === 'sale',
                                            'bg-amber-50 text-amber-600': invoice.type === 'purchase',
                                            'bg-rose-50 text-rose-600': invoice.type === 'return'
                                        }" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest" x-text="invoice.type"></span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="font-bold text-slate-600" x-text="new Date(invoice.created_at).toLocaleDateString()"></div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="font-black text-slate-900" x-text="'৳' + parseFloat(invoice.total_amount).toFixed(2)"></div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-2">
                                            <div :class="parseFloat(invoice.due_amount) <= 0 ? 'bg-emerald-500' : 'bg-amber-500'" class="w-1.5 h-1.5 rounded-full"></div>
                                            <span class="text-xs font-black" :class="parseFloat(invoice.due_amount) <= 0 ? 'text-emerald-600' : 'text-amber-600'" 
                                                  x-text="parseFloat(invoice.due_amount) <= 0 ? 'PAID' : 'DUE'"></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex gap-2">
                                            <button @click="viewInvoice(invoice.id)" class="p-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-vibrant-indigo hover:text-white transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>
                                            <a :href="'/invoices/' + invoice.id + '/print'" target="_blank" class="p-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-emerald-500 hover:text-white transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2h6a2 2 0 002 2v4"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Skeleton --}}
                <div class="px-8 py-5 border-t border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <div class="text-xs font-bold text-slate-400">
                        Showing <span x-text="invoices.length"></span> invoices
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function invoiceManager() {
        return {
            invoices: [],
            search: '',
            typeFilter: '',
            loading: false,

            async fetchInvoices() {
                this.loading = true;
                const params = new URLSearchParams();
                if (this.search) params.set('search', this.search);
                if (this.typeFilter) params.set('type', this.typeFilter);

                try {
                    const res = await fetch(`/invoices?${params}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    this.invoices = data.data;
                } catch (e) { console.error(e); }
                finally { this.loading = false; }
            },

            viewInvoice(id) {
                window.location.href = `/invoices/${id}`;
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
