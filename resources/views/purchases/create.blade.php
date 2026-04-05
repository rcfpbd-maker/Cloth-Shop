<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center text-slate-800">
            <h2 class="font-black text-2xl leading-tight">New Stock Purchase</h2>
            <a href="{{ route('purchases.index') }}" class="text-sm font-bold text-slate-400 hover:text-vibrant-indigo transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to List
            </a>
        </div>
    </x-slot>

    <div v-pre class="py-12 bg-slate-50 min-h-screen" x-data="purchaseForm()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form @submit.prevent="submitForm" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Left: Items Form --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-black text-lg text-slate-800">Purchase Items</h3>
                            <button type="button" @click="addItem" class="text-vibrant-indigo font-black text-sm flex items-center gap-2 hover:bg-indigo-50 px-4 py-2 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add Item
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 relative group">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                        <div class="md:col-span-12">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Product Variant</label>
                                            <div class="relative">
                                                <input type="text" 
                                                       x-model="item.searchQuery" 
                                                       @input.debounce.300ms="searchVariants(index)"
                                                       placeholder="Type SKU or Name..."
                                                       class="w-full px-5 py-3 bg-white border-none rounded-xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-slate-700 transition-all">
                                                
                                                {{-- Autocomplete --}}
                                                <div x-show="item.showResults" @click.away="item.showResults = false" 
                                                     class="absolute left-0 right-0 mt-2 bg-white border border-slate-100 rounded-2xl shadow-2xl z-50 max-h-60 overflow-y-auto">
                                                    <template x-for="v in item.searchResults" :key="v.id">
                                                        <button type="button" @click="selectVariant(index, v)" 
                                                                class="w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-none">
                                                            <div class="font-black text-xs text-slate-800" x-text="v.product.name"></div>
                                                            <div class="flex justify-between text-[10px] font-bold text-slate-400">
                                                                <span x-text="v.sku"></span>
                                                                <span x-text="(v.size || '') + ' ' + (v.color || '')"></span>
                                                            </div>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="md:col-span-3">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Quantity</label>
                                            <input type="number" x-model.number="item.quantity" min="1" 
                                                   class="w-full px-5 py-3 bg-white border-none rounded-xl focus:ring-2 focus:ring-vibrant-indigo font-black text-slate-700">
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Cost Price (৳)</label>
                                            <input type="number" x-model.number="item.price" step="0.01" 
                                                   class="w-full px-5 py-3 bg-white border-none rounded-xl focus:ring-2 focus:ring-vibrant-indigo font-black text-slate-700">
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Item Total</label>
                                            <div class="px-5 py-3 bg-slate-100 rounded-xl font-black text-slate-900" x-text="'৳' + (item.quantity * item.price).toFixed(2)"></div>
                                        </div>
                                        <div class="md:col-span-1">
                                            <button type="button" @click="removeItem(index)" class="p-3 text-rose-300 hover:text-rose-500 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Right: Summary & Supplier --}}
                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                        <h3 class="font-black text-lg text-slate-800 mb-6">General Info</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Supplier</label>
                                <select x-model="form.supplier_id" required
                                        class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-slate-700">
                                    <option value="">Select Supplier</option>
                                    <template x-for="s in suppliers" :key="s.id">
                                        <option :value="s.id" x-text="s.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Purchase Date</label>
                                <input type="date" x-model="form.purchase_date" required
                                       class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-slate-700">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Reference/Invoice #</label>
                                <input type="text" x-model="form.invoice_no" placeholder="Optional"
                                       class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-slate-700">
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-2xl text-white">
                        <h3 class="font-black text-lg mb-6">Payment Summary</h3>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex justify-between font-bold text-slate-400">
                                <span>Item Total</span>
                                <span x-text="'৳' + subtotal.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-slate-400">Discount</span>
                                <input type="number" x-model.number="form.discount" class="w-24 bg-slate-800 border-none rounded-xl text-right font-black focus:ring-vibrant-indigo">
                            </div>
                            <div class="pt-4 border-t border-slate-800 flex justify-between items-center">
                                <span class="font-black text-xl">Net Total</span>
                                <span class="font-black text-3xl text-vibrant-indigo" x-text="'৳' + netTotal.toFixed(2)"></span>
                            </div>
                        </div>

                        <div class="space-y-4 mb-8">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Payment Method</label>
                                <select x-model="form.payment_method_id"
                                        class="w-full px-5 py-3 bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-white">
                                    <option value="">Select Method</option>
                                    <template x-for="m in paymentMethods" :key="m.id">
                                        <option :value="m.id" x-text="m.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Paid Amount</label>
                                <input type="number" x-model.number="form.paid_amount" 
                                       class="w-full px-5 py-3 bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-vibrant-indigo font-black text-white text-xl">
                            </div>
                            <div class="flex justify-between text-rose-400 font-bold px-1">
                                <span>Due Amount</span>
                                <span x-text="'৳' + (netTotal - form.paid_amount).toFixed(2)"></span>
                            </div>
                        </div>

                        <button type="submit" :disabled="submitting || items.length === 0"
                                class="w-full py-4 bg-vibrant-indigo text-white font-black rounded-2xl shadow-xl shadow-indigo-900/40 hover:bg-indigo-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg x-show="submitting" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span x-text="submitting ? 'Recording...' : 'Submit Purchase'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function purchaseForm() {
        return {
            submitting: false,
            suppliers: [],
            paymentMethods: [],
            items: [],
            form: {
                supplier_id: '',
                purchase_date: new Date().toISOString().split('T')[0],
                invoice_no: '',
                discount: 0,
                paid_amount: 0,
                payment_method_id: ''
            },

            get subtotal() {
                return this.items.reduce((sum, i) => sum + (i.quantity * i.price), 0);
            },

            get netTotal() {
                return Math.max(0, this.subtotal - (this.form.discount || 0));
            },

            async init() {
                await this.loadInitialData();
                this.addItem();
            },

            async loadInitialData() {
                try {
                    const res = await fetch('/api/purchases/init');
                    const data = await res.json();
                    this.suppliers = data.suppliers;
                    this.paymentMethods = data.payment_methods;
                    if (this.paymentMethods.length > 0) this.form.payment_method_id = this.paymentMethods[0].id;
                } catch (e) { console.error(e); }
            },

            addItem() {
                this.items.push({
                    product_variant_id: '',
                    variant: null,
                    quantity: 1,
                    price: 0,
                    searchQuery: '',
                    searchResults: [],
                    showResults: false
                });
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            async searchVariants(index) {
                const query = this.items[index].searchQuery;
                if (!query || query.length < 2) {
                    this.items[index].searchResults = [];
                    this.items[index].showResults = false;
                    return;
                }
                try {
                    const res = await fetch(`/pos/search?query=${query}`, { headers: { 'Accept': 'application/json' } });
                    this.items[index].searchResults = await res.json();
                    this.items[index].showResults = true;
                } catch (e) { console.error(e); }
            },

            selectVariant(index, v) {
                this.items[index].product_variant_id = v.id;
                this.items[index].variant = v;
                this.items[index].price = v.purchase_price || 0;
                this.items[index].searchQuery = `${v.product.name} (${v.sku})`;
                this.items[index].showResults = false;
            },

            async submitForm() {
                if (this.items.some(i => !i.product_variant_id)) {
                    alert('Please select a product for all items.');
                    return;
                }
                this.submitting = true;
                const payload = {
                    ...this.form,
                    items: this.items.map(i => ({
                        product_variant_id: i.product_variant_id,
                        quantity: i.quantity,
                        price: i.price,
                        discount: 0
                    }))
                };

                try {
                    const res = await fetch('/purchases', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) {
                        window.location.href = '{{ route('purchases.index') }}';
                    } else {
                        const err = await res.json();
                        alert(err.message || 'Error saving purchase.');
                    }
                } catch (e) {
                    alert('Network error.');
                } finally {
                    this.submitting = false;
                }
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
