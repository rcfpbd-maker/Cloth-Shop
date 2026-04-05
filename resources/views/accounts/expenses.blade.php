<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center text-slate-800">
            <h2 class="font-black text-2xl leading-tight">Shop Expenses</h2>
            <button @click="$dispatch('open-modal', 'add-expense')" class="px-8 py-3 bg-vibrant-indigo text-white font-black rounded-2xl shadow-lg shadow-indigo-100 flex items-center gap-2 hover:bg-indigo-700 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Record Expense
            </button>
        </div>
    </x-slot>

    <div v-pre class="py-12 bg-slate-50 min-h-screen" x-data="expenseManager()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Today's Total</span>
                    <span class="text-3xl font-black text-slate-900">৳{{ number_format(\App\Models\Expense::whereDate('expense_date', now())->sum('amount'), 2) }}</span>
                </div>
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 border-l-4 border-l-vibrant-indigo">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">This Month</span>
                    <span class="text-3xl font-black text-vibrant-indigo">৳{{ number_format(\App\Models\Expense::whereMonth('expense_date', now()->month)->sum('amount'), 2) }}</span>
                </div>
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Cash in Hand</span>
                    @php
                        $in = \App\Models\Payment::whereIn('reference_type', ['sale', 'customer'])->sum('amount');
                        $out = \App\Models\Payment::whereNotIn('reference_type', ['sale', 'customer'])->sum('amount') + \App\Models\Expense::sum('amount');
                        $cash = $in - $out;
                    @endphp
                    <span class="text-3xl font-black text-emerald-600">৳{{ number_format($cash, 2) }}</span>
                </div>
            </div>

            {{-- Expenses Table --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-50 flex justify-between items-center">
                    <h3 class="font-black text-xl text-slate-800">Expense Log</h3>
                    <div class="flex gap-4">
                        <input type="date" x-model="filters.start_date" @change="fetchExpenses()" class="px-4 py-2 bg-slate-50 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-vibrant-indigo">
                        <input type="date" x-model="filters.end_date" @change="fetchExpenses()" class="px-4 py-2 bg-slate-50 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-vibrant-indigo">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Category</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Description</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Amount</th>
                                <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <template x-for="e in expenses" :key="e.id">
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-10 py-7 font-bold text-slate-500 text-sm" x-text="new Date(e.expense_date).toLocaleDateString('en-GB')"></td>
                                    <td class="px-10 py-7">
                                        <span class="px-4 py-1.5 bg-indigo-50 text-vibrant-indigo rounded-full text-[10px] font-black uppercase tracking-widest" x-text="e.category.name"></span>
                                    </td>
                                    <td class="px-10 py-7 font-bold text-slate-600 text-sm" x-text="e.description || '—'"></td>
                                    <td class="px-10 py-7 font-black text-slate-900 text-right" x-text="'৳' + parseFloat(e.amount).toFixed(2)"></td>
                                    <td class="px-10 py-7 text-right">
                                        <button @click="deleteExpense(e.id)" class="p-2 text-slate-300 hover:text-rose-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Add Expense Modal --}}
        <x-modal name="add-expense" focusable>
            <div class="p-10">
                <h3 class="font-black text-2xl text-slate-800 mb-8 tracking-tight">Record Shop Expense</h3>
                <form @submit.prevent="saveExpense" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Category</label>
                            <select x-model="form.category_id" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-black text-xs uppercase">
                                <template x-for="c in categories" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Expense Date</label>
                            <input type="date" x-model="form.expense_date" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Amount (৳)</label>
                            <input type="number" step="0.01" x-model="form.amount" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-black text-2xl">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Payment Method</label>
                            <select x-model="form.payment_method_id" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-black text-xs uppercase">
                                <template x-for="m in methods" :key="m.id">
                                    <option :value="m.id" x-text="m.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Description</label>
                        <textarea x-model="form.description" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-sm h-32" placeholder="What was this expense for?"></textarea>
                    </div>

                    <div class="pt-6">
                        <button type="submit" :disabled="loading" class="w-full py-5 bg-vibrant-indigo text-white font-black rounded-[1.5rem] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center justify-center gap-3">
                            <svg x-show="loading" class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span x-text="loading ? 'Recording...' : 'Confirm Expense'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>
    </div>

    @push('scripts')
    <script>
    function expenseManager() {
        return {
            expenses: [],
            categories: [],
            methods: [],
            loading: false,
            filters: { start_date: '', end_date: '', category_id: '' },
            form: {
                category_id: '',
                expense_date: new Date().toISOString().split('T')[0],
                amount: '',
                payment_method_id: '',
                description: ''
            },

            async init() {
                await this.loadInitialData();
                await this.fetchExpenses();
            },

            async loadInitialData() {
                const [catRes, posRes] = await Promise.all([
                    fetch('/expenses-categories'),
                    fetch('/api/pos/init')
                ]);
                this.categories = await catRes.json();
                const posData = await posRes.json();
                this.methods = posData.payment_methods;
                
                if (this.categories.length) this.form.category_id = this.categories[0].id;
                if (this.methods.length) this.form.payment_method_id = this.methods[0].id;
            },

            async fetchExpenses() {
                const params = new URLSearchParams(this.filters);
                const res = await fetch(`/expenses?${params}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.expenses = data.data;
            },

            async saveExpense() {
                this.loading = true;
                try {
                    const res = await fetch('/expenses', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.form)
                    });
                    if (res.ok) {
                        this.$dispatch('close-modal', 'add-expense');
                        this.fetchExpenses();
                        this.form.amount = '';
                        this.form.description = '';
                    }
                } finally {
                    this.loading = false;
                }
            },

            async deleteExpense(id) {
                if (!confirm('Are you sure you want to delete this expense?')) return;
                const res = await fetch(`/expenses/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                if (res.ok) this.fetchExpenses();
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
