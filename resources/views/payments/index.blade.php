<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center text-slate-800">
            <h2 class="font-black text-2xl leading-tight">Payment Collection</h2>
            <div class="flex gap-4">
                <div class="px-6 py-2.5 bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col items-end">
                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Total Receivable</span>
                    <span class="font-black text-xl text-rose-600">৳{{ number_format(\App\Models\Customer::sum('previous_due'), 2) }}</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div v-pre class="py-12 bg-slate-50 min-h-screen" x-data="paymentManager()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Quick Filter --}}
            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="relative flex-1 group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-vibrant-indigo transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                        </span>
                        <input type="text" x-model="search" @input.debounce.300ms="fetchCustomers()"
                               placeholder="Search customer to collect payment..." 
                               class="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-slate-700 transition-all">
                    </div>
                </div>
            </div>

            {{-- Customer Dues List --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="customer in customers" :key="customer.id">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Outstanding</span>
                                <span class="font-black text-2xl text-slate-900" x-text="'৳' + parseFloat(customer.previous_due).toFixed(2)"></span>
                            </div>
                        </div>

                        <h3 class="font-black text-lg text-slate-800 mb-1" x-text="customer.name"></h3>
                        <p class="text-xs font-bold text-slate-400 mb-8" x-text="customer.phone"></p>

                        <button @click="openPaymentModal(customer)" 
                                class="w-full py-4 bg-vibrant-indigo text-white font-black rounded-2xl shadow-lg shadow-indigo-100 hover:shadow-indigo-200 transition-all flex items-center justify-center gap-2">
                            Collect Payment
                        </button>
                    </div>
                </template>
            </div>

            {{-- Recent Payments Table --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden mt-12">
                <div class="px-8 py-6 border-b border-slate-50">
                    <h3 class="font-black text-xl text-slate-800">Recent Collections</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Method</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Amount</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <template x-for="p in recentPayments" :key="p.id">
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-8 py-6 font-black text-slate-700" x-text="p.reference ? p.reference.name : 'Unknown'"></td>
                                    <td class="px-8 py-6 font-bold text-slate-500" x-text="new Date(p.payment_date).toLocaleDateString()"></td>
                                    <td class="px-8 py-6 font-black text-vibrant-indigo uppercase text-[10px]" x-text="p.payment_method?.name"></td>
                                    <td class="px-8 py-6 font-black text-emerald-600" x-text="'৳' + p.amount"></td>
                                    <td class="px-8 py-6 text-sm text-slate-400 font-bold" x-text="p.note"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Payment Collection Modal --}}
        <x-modal name="collection-modal" focusable>
            <div class="p-10" x-show="selectedCustomer">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h3 class="font-black text-2xl text-slate-800">Record Payment</h3>
                        <p class="text-sm font-bold text-slate-400" x-text="'For ' + selectedCustomer?.name"></p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Total Due</span>
                        <span class="font-black text-2xl text-rose-600" x-text="'৳' + parseFloat(selectedCustomer?.previous_due).toFixed(2)"></span>
                    </div>
                </div>

                <form @submit.prevent="submitPayment" class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Collection Date</label>
                            <input type="date" x-model="payload.payment_date" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Amount to Collect (৳)</label>
                            <input type="number" x-model.number="payload.amount" required step="0.01" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-black text-2xl">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Payment Method</label>
                        <select x-model="payload.payment_method_id" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-black uppercase text-xs">
                            <template x-for="m in paymentMethods" :key="m.id">
                                <option :value="m.id" x-text="m.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Memo / Note</label>
                        <textarea x-model="payload.note" placeholder="Optional installment note..." class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-bold h-24"></textarea>
                    </div>

                    <button type="submit" :disabled="loading"
                            class="w-full py-5 bg-vibrant-indigo text-white font-black rounded-[1.5rem] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center justify-center gap-3">
                        <svg x-show="loading" class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span x-text="loading ? 'Processing...' : 'Confirm Collection'"></span>
                    </button>
                </form>
            </div>
        </x-modal>
    </div>

    @push('scripts')
    <script>
    function paymentManager() {
        return {
            customers: [],
            recentPayments: [],
            paymentMethods: [],
            search: '',
            loading: false,
            selectedCustomer: null,
            payload: {
                payment_date: new Date().toISOString().split('T')[0],
                amount: 0,
                payment_method_id: '',
                note: ''
            },

            async init() {
                await this.loadMethods();
                await this.fetchRecentPayments();
                await this.fetchCustomers();
            },

            async loadMethods() {
                const res = await fetch('/api/pos/init');
                const data = await res.json();
                this.paymentMethods = data.payment_methods;
                if (this.paymentMethods.length > 0) this.payload.payment_method_id = this.paymentMethods[0].id;
            },

            async fetchCustomers() {
                const res = await fetch(`/customers?search=${this.search}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.customers = data.data.filter(c => parseFloat(c.previous_due) > 0);
            },

            async fetchRecentPayments() {
                const res = await fetch('/payments', { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.recentPayments = data.data;
            },

            openPaymentModal(c) {
                this.selectedCustomer = c;
                this.payload.amount = parseFloat(c.previous_due);
                this.$dispatch('open-modal', 'collection-modal');
            },

            async submitPayment() {
                this.loading = true;
                const requestData = {
                    ...this.payload,
                    reference_type: 'customer',
                    reference_id: this.selectedCustomer.id
                };

                try {
                    const res = await fetch('/payments', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(requestData)
                    });
                    if (res.ok) {
                        this.$dispatch('close-modal', 'collection-modal');
                        this.fetchCustomers();
                        this.fetchRecentPayments();
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
