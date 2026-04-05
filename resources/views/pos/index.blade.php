<x-app-layout>
    <div v-pre class="h-[calc(100vh-5rem)] bg-slate-100 overflow-hidden" 
         x-data="posSystem()" 
         x-init="init()">
        
        <div class="h-full flex flex-col lg:flex-row">
            
            {{-- Left Side: Product Discovery --}}
            <div class="flex-1 flex flex-col min-w-0 bg-white border-r border-slate-200">
                
                {{-- Search & Categories --}}
                <div class="p-4 space-y-4 border-b border-slate-100">
                    <div class="flex gap-3">
                        <div class="relative flex-1">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                            </span>
                            <input type="text" 
                                   x-model="search" 
                                   @input.debounce.300ms="fetchProducts()"
                                   @keydown.enter="handleBarcodeScan()"
                                   placeholder="Search products or scan barcode..." 
                                   class="w-full pl-12 pr-4 py-3 bg-slate-100 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-vibrant-indigo transition-all font-medium text-slate-700">
                        </div>
                        <select x-model="categoryFilter" @change="fetchProducts()"
                                class="px-4 py-3 bg-slate-100 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-vibrant-indigo transition-all font-bold text-slate-600">
                            <option value="">All Categories</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Product Grid --}}
                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                    <div x-show="loading" class="flex justify-center py-12">
                        <div class="w-10 h-10 border-4 border-vibrant-indigo border-t-transparent rounded-full animate-spin"></div>
                    </div>

                    <div x-show="!loading" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <template x-for="product in products" :key="product.id">
                            <button @click="addToCart(product)" 
                                    class="group bg-white border border-slate-100 p-3 rounded-2xl hover:border-vibrant-indigo hover:shadow-xl hover:-translate-y-1 transition-all text-left flex flex-col h-full relative overflow-hidden">
                                <div class="mb-2">
                                    <div class="font-black text-slate-900 text-sm truncate" x-text="product.product.name"></div>
                                    <div class="flex justify-between items-center mt-1">
                                        <span class="text-[10px] font-bold text-slate-400" x-text="product.sku"></span>
                                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-600" x-text="product.size || product.color || 'Standard'"></span>
                                    </div>
                                </div>
                                <div class="mt-auto flex justify-between items-end">
                                    <div class="font-black text-vibrant-indigo text-lg">৳<span x-text="parseFloat(product.sale_price).toFixed(0)"></span></div>
                                    <div class="text-[10px] font-bold" :class="product.stock_quantity <= 5 ? 'text-rose-500' : 'text-slate-400'">
                                        Stock: <span x-text="product.stock_quantity"></span>
                                    </div>
                                </div>
                                <div x-show="isInCart(product)" class="absolute top-2 right-2 w-5 h-5 bg-vibrant-indigo text-white rounded-full flex items-center justify-center shadow-lg transform scale-110">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div x-show="!loading && products.length === 0" class="text-center py-12">
                        <p class="text-slate-400 font-bold">No products found.</p>
                    </div>
                </div>
            </div>

            {{-- Right Side: Checkout Console --}}
            <div class="w-full lg:w-[400px] xl:w-[450px] bg-slate-50 flex flex-col shadow-2xl relative z-10">
                
                {{-- Customer Section --}}
                <div class="p-4 bg-white border-b border-slate-200">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Customer Selection</label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <select x-model="selectedCustomerId" 
                                    class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-vibrant-indigo transition-all font-bold text-slate-700 appearance-none">
                                <option value="">Walk-in Customer</option>
                                <template x-for="cust in customers" :key="cust.id">
                                    <option :value="cust.id" x-text="cust.name + ' (' + cust.phone + ')'"></option>
                                </template>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <button class="p-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-vibrant-indigo hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                    <div x-show="selectedCustomer" class="mt-2 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-500">Current Due: <span class="text-rose-500" x-text="'৳' + selectedCustomer.previous_due"></span></span>
                        <span class="text-xs font-bold text-slate-500">Credit Limit: <span class="text-emerald-600" x-text="'৳' + selectedCustomer.credit_limit"></span></span>
                    </div>
                </div>

                {{-- Cart Items --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="bg-white p-3 rounded-2xl border border-slate-100 flex items-center gap-3 shadow-sm group">
                            <div class="flex-1 min-w-0">
                                <div class="font-black text-slate-900 text-xs truncate" x-text="item.product.name"></div>
                                <div class="text-[10px] font-bold text-slate-400" x-text="item.sku"></div>
                            </div>
                            <div class="flex items-center gap-2 bg-slate-50 rounded-lg p-1">
                                <button @click="updateQty(index, -1)" class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-white hover:shadow-sm text-slate-400 hover:text-rose-500 transition-all font-black">-</button>
                                <span class="w-8 text-center text-xs font-black text-slate-700" x-text="item.qty"></span>
                                <button @click="updateQty(index, 1)" class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-white hover:shadow-sm text-slate-400 hover:text-emerald-500 transition-all font-black">+</button>
                            </div>
                            <div class="text-right min-w-[60px]">
                                <div class="font-black text-slate-900 text-xs text-rose-500" x-text="'৳' + (item.sale_price * item.qty).toFixed(0)"></div>
                            </div>
                            <button @click="removeFromCart(index)" class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-slate-300 hover:text-rose-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                    
                    <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-slate-300 py-12">
                        <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <p class="font-black">Cart is empty</p>
                    </div>
                </div>

                {{-- Totals & Checkout --}}
                <div class="p-6 bg-white border-t border-slate-200 space-y-4">
                    <div class="space-y-2">
                        <div class="flex justify-between text-slate-500 font-bold text-sm">
                            <span>Subtotal</span>
                            <span x-text="'৳' + subtotal.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between items-center text-slate-500 font-bold text-sm">
                            <span>Order Discount</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-300">৳</span>
                                <input type="number" x-model.number="orderDiscount" class="w-20 p-1 text-right bg-slate-50 border-none rounded-lg text-sm font-black focus:ring-vibrant-indigo">
                            </div>
                        </div>
                        <div class="pt-3 border-t border-dashed border-slate-200 flex justify-between items-center">
                            <span class="font-black text-slate-900 text-xl">Grand Total</span>
                            <span class="font-black text-vibrant-indigo text-3xl" x-text="'৳' + grandTotal.toFixed(0)"></span>
                        </div>
                    </div>

                    <button @click="openPaymentModal()" 
                            :disabled="cart.length === 0"
                            class="w-full py-4 bg-vibrant-indigo text-white font-black rounded-2xl shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition-all disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Proceed to Payment
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════ PAYMENT MODAL ═══════════ --}}
        <div x-show="showPaymentModal" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            
            <div @click.away="showPaymentModal = false" 
                 class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
                
                <div class="p-6 bg-slate-900 text-white flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-black">Finalize Payment</h2>
                        <p class="text-slate-400 text-xs font-bold mt-1">Order #<span x-text="Date.now().toString().slice(-6)"></span></p>
                    </div>
                    <div class="text-right">
                        <div class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Payable Amount</div>
                        <div class="text-3xl font-black text-vibrant-indigo" x-text="'৳' + grandTotal.toFixed(0)"></div>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    {{-- Payment Method Selection --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Choose Payment Method</label>
                        <div class="grid grid-cols-2 gap-3">
                            <template x-for="method in paymentMethods" :key="method.id">
                                <button @click="paymentMethodId = method.id" 
                                        :class="paymentMethodId === method.id ? 'border-vibrant-indigo bg-indigo-50 text-vibrant-indigo' : 'border-slate-100 hover:border-indigo-200 text-slate-500'"
                                        class="p-4 border-2 rounded-2xl transition-all text-center">
                                    <div class="font-black text-sm" x-text="method.name"></div>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Amounts --}}
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Paid Amount</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-400">৳</span>
                                <input type="number" x-model.number="paidAmount" 
                                       class="w-full pl-10 pr-4 py-4 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-vibrant-indigo transition-all font-black text-2xl text-slate-700">
                                <button @click="paidAmount = grandTotal" class="absolute right-3 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-vibrant-indigo text-white text-[10px] font-black rounded-lg hover:bg-indigo-700 transition-all">FULL PAY</button>
                            </div>
                        </div>
                    </div>

                    {{-- Transaction Summary --}}
                    <div class="bg-slate-50 rounded-2xl p-4 space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-bold">Paid</span>
                            <span class="font-black text-slate-900" x-text="'৳' + paidAmount"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-bold" x-text="changeAmount >= 0 ? 'Change' : 'Due Amount'"></span>
                            <span class="font-black" :class="changeAmount >= 0 ? 'text-emerald-600' : 'text-rose-600'" x-text="'৳' + Math.abs(changeAmount)"></span>
                        </div>
                    </div>
                </div>

                {{-- Action --}}
                <div class="p-8 pt-0 flex gap-4">
                    <button @click="showPaymentModal = false" class="flex-1 py-4 font-black text-slate-500 hover:text-slate-700 transition-all">Cancel</button>
                    <button @click="submitOrder()" 
                            :disabled="submitting || (changeAmount < 0 && !selectedCustomerId)"
                            class="flex-[2] py-4 bg-emerald-500 text-white font-black rounded-2xl shadow-xl shadow-emerald-100 hover:bg-emerald-600 transition-all flex items-center justify-center gap-2">
                        <svg x-show="submitting" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span x-text="submitting ? 'Processing...' : 'Complete Sale'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Success Notification Overlay --}}
        <div x-show="showSuccess" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-y-10"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-4 z-[100]">
            <div class="w-10 h-10 bg-emerald-500 rounded-2xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <div class="font-black text-lg">Sale Completed!</div>
                <div class="text-xs font-bold text-slate-400">Invoice <span x-text="lastInvoiceNo"></span> generated.</div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function posSystem() {
        return {
            loading: false,
            submitting: false,
            search: '',
            categoryFilter: '',
            customers: [],
            categories: [],
            paymentMethods: [],
            products: [],
            cart: [],
            orderDiscount: 0,
            selectedCustomerId: '',
            paymentMethodId: '',
            paidAmount: 0,
            showPaymentModal: false,
            showSuccess: false,
            lastInvoiceNo: '',

            get selectedCustomer() {
                return this.customers.find(c => c.id == this.selectedCustomerId);
            },

            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.sale_price * item.qty), 0);
            },

            get grandTotal() {
                return Math.max(0, this.subtotal - this.orderDiscount);
            },

            get changeAmount() {
                return this.paidAmount - this.grandTotal;
            },

            async init() {
                await this.loadInitialData();
                await this.fetchProducts();
            },

            async loadInitialData() {
                try {
                    const res = await fetch('/api/pos/init', { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    this.customers = data.customers;
                    this.categories = data.categories;
                    this.paymentMethods = data.payment_methods;
                    if (this.paymentMethods.length > 0) this.paymentMethodId = this.paymentMethods[0].id;
                } catch (e) { console.error(e); }
            },

            async fetchProducts() {
                this.loading = true;
                const params = new URLSearchParams();
                if (this.search) params.set('query', this.search);
                if (this.categoryFilter) params.set('category_id', this.categoryFilter);

                try {
                    const res = await fetch(`/pos/search?${params}`, { headers: { 'Accept': 'application/json' } });
                    this.products = await res.json();
                } catch (e) { console.error(e); }
                finally { this.loading = false; }
            },

            handleBarcodeScan() {
                if (!this.search || this.products.length === 0) return;
                // If there's an exact match in SKU or barcode, add it
                const exactMatch = this.products.find(p => p.sku === this.search || p.barcode === this.search);
                if (exactMatch) {
                    this.addToCart(exactMatch);
                    this.search = ''; // Clear search for next scan
                }
            },

            addToCart(product) {
                const existing = this.cart.findIndex(i => i.id === product.id);
                if (existing > -1) {
                    this.updateQty(existing, 1);
                } else {
                    if (product.stock_quantity <= 0) {
                        alert('Out of stock!');
                        return;
                    }
                    this.cart.push({ ...product, qty: 1 });
                }
            },

            removeFromCart(index) {
                this.cart.splice(index, 1);
            },

            updateQty(index, delta) {
                const item = this.cart[index];
                const newQty = item.qty + delta;
                if (newQty < 1) {
                    this.removeFromCart(index);
                } else if (newQty > item.stock_quantity) {
                    alert('Insufficient stock!');
                } else {
                    item.qty = newQty;
                }
            },

            isInCart(product) {
                return this.cart.some(i => i.id === product.id);
            },

            openPaymentModal() {
                this.paidAmount = this.grandTotal;
                this.showPaymentModal = true;
            },

            async submitOrder() {
                this.submitting = true;
                const payload = {
                    customer_id: this.selectedCustomerId,
                    sale_date: new Date().toISOString().split('T')[0],
                    items: this.cart.map(i => ({
                        variant_id: i.id,
                        quantity: i.qty,
                        price: i.sale_price,
                        discount: 0
                    })),
                    invoice_discount: this.orderDiscount,
                    paid_amount: this.paidAmount,
                    payment_method_id: this.paymentMethodId
                };

                try {
                    const res = await fetch('/sales', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(payload)
                    });
                    const result = await res.json();
                    
                    if (res.ok) {
                        this.lastInvoiceNo = result.sale.invoice_no;
                        this.cart = [];
                        this.orderDiscount = 0;
                        this.selectedCustomerId = '';
                        this.showPaymentModal = false;
                        this.showSuccess = true;
                        setTimeout(() => this.showSuccess = false, 5000);
                        await this.fetchProducts(); // Refresh stock
                    } else {
                        alert(result.message || 'Payment failed. Please check balance.');
                    }
                } catch (e) {
                    alert('Network error. Please try again.');
                } finally {
                    this.submitting = false;
                }
            }
        };
    }
    </script>
    <style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
    @endpush
</x-app-layout>
