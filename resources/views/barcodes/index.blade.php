<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Barcode Management</h2>
    </x-slot>

    <div v-pre class="py-6 sm:py-10 bg-slate-50 min-h-screen"
         x-data="barcodeManager()"
         x-init="fetchVariants()">

        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900">Barcode Manager</h1>
                    <p class="text-slate-500 font-medium mt-1">View, edit, and manage barcodes for all product variants.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('barcodes.print') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 bg-white border border-slate-200 text-slate-700 font-bold rounded-2xl shadow-sm hover:border-indigo-300 hover:text-vibrant-indigo transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print Labels
                    </a>
                </div>
            </div>

            {{-- Tab Bar --}}
            <div class="flex gap-1 p-1 bg-slate-100 rounded-2xl w-fit mb-8">
                <button @click="activeTab = 'variants'" :class="activeTab === 'variants' ? 'bg-white shadow-sm text-vibrant-indigo' : 'text-slate-500 hover:text-slate-700'"
                        class="px-5 py-2 rounded-xl text-sm font-bold transition-all">Variant Barcodes</button>
                <button @click="activeTab = 'history'; fetchHistory()" :class="activeTab === 'history' ? 'bg-white shadow-sm text-vibrant-indigo' : 'text-slate-500 hover:text-slate-700'"
                        class="px-5 py-2 rounded-xl text-sm font-bold transition-all">Change History</button>
            </div>

            {{-- ── TAB: Variant Barcodes ── --}}
            <div x-show="activeTab === 'variants'">

                {{-- Filters --}}
                <div class="card-glass p-4 mb-6 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                        <input x-model="search" @input.debounce.400ms="fetchVariants()"
                               type="text" placeholder="Search by product, SKU, or barcode..."
                               class="pl-10 pr-4 py-2.5 w-full bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-vibrant-indigo focus:border-transparent transition"/>
                    </div>
                    <div x-show="selectedIds.length > 0" class="flex gap-2">
                        <span class="inline-flex items-center px-3 py-2 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-xl">
                            <span x-text="selectedIds.length"></span>&nbsp;selected
                        </span>
                        <button @click="regenerateSelected()"
                                class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-700 transition-all">
                            Regenerate Barcodes
                        </button>
                    </div>
                </div>

                {{-- Success / Error Toast --}}
                <div x-show="toast.show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed bottom-6 right-6 z-50 max-w-sm px-5 py-3 rounded-2xl shadow-lg text-sm font-bold flex items-center gap-3"
                     :class="toast.type === 'success' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="toast.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path x-show="toast.type === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="toast.message"></span>
                </div>

                {{-- Loading --}}
                <div x-show="loading" class="flex justify-center py-20">
                    <div class="w-10 h-10 border-4 border-vibrant-indigo border-t-transparent rounded-full animate-spin"></div>
                </div>

                {{-- Table --}}
                <div x-show="!loading" class="card-glass overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-800 text-white">
                                <tr>
                                    <th class="px-4 py-3 w-10">
                                        <input type="checkbox" @change="toggleSelectAll($event)"
                                               class="w-4 h-4 rounded border-slate-400 text-vibrant-indigo focus:ring-vibrant-indigo"/>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider">SKU</th>
                                    <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider">Size / Color</th>
                                    <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider">Barcode</th>
                                    <th class="px-4 py-3 text-center text-xs font-black uppercase tracking-wider">Stock</th>
                                    <th class="px-4 py-3 text-right text-xs font-black uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="variant in variants" :key="variant.id">
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="px-4 py-3">
                                            <input type="checkbox"
                                                   :value="variant.id"
                                                   @change="toggleSelect(variant.id)"
                                                   :checked="selectedIds.includes(variant.id)"
                                                   class="w-4 h-4 rounded border-slate-300 text-vibrant-indigo focus:ring-vibrant-indigo"/>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div>
                                                <p class="font-bold text-slate-900 text-xs" x-text="variant.product?.name ?? '—'"></p>
                                                <p class="text-[10px] text-slate-400 font-medium" x-text="variant.product?.category?.name ?? ''"></p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs text-slate-600" x-text="variant.sku"></td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-1.5">
                                                <span x-show="variant.size" class="text-[10px] font-bold px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-lg" x-text="variant.size"></span>
                                                <span x-show="variant.color" class="text-[10px] font-bold px-2 py-0.5 bg-violet-50 text-violet-700 rounded-lg" x-text="variant.color"></span>
                                                <span x-show="!variant.size && !variant.color" class="text-slate-400 text-xs">—</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            {{-- Inline edit barcode --}}
                                            <div x-data="{ editing: false, bc: variant.barcode ?? '', saving: false }">
                                                <div x-show="!editing" class="flex items-center gap-2 group/bc">
                                                    <span class="font-mono text-xs text-slate-700 bg-slate-100 px-2 py-1 rounded-lg" x-text="bc || 'N/A'"></span>
                                                    <button @click="editing = true"
                                                            class="opacity-0 group-hover/bc:opacity-100 transition-opacity text-slate-400 hover:text-vibrant-indigo">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6-6 3 3-6 6H9v-3z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div x-show="editing" class="flex items-center gap-2">
                                                    <input x-model="bc" type="text"
                                                           class="font-mono text-xs border border-indigo-300 rounded-lg px-2 py-1 w-36 focus:outline-none focus:ring-2 focus:ring-vibrant-indigo"
                                                           @keydown.enter="saveBarcode(variant, bc, () => { editing = false; }, (msg) => { showToast(msg, 'error') })"
                                                           @keydown.escape="editing = false; bc = variant.barcode ?? ''"/>
                                                    <button @click="saveBarcode(variant, bc, () => { editing = false; $nextTick(() => fetchVariants()) }, (msg) => showToast(msg, 'error'))"
                                                            class="text-emerald-600 hover:text-emerald-700">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                    <button @click="editing = false; bc = variant.barcode ?? ''" class="text-rose-500 hover:text-rose-600">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-xs font-black px-2 py-1 rounded-full"
                                                  :class="variant.stock_quantity <= 5 ? 'bg-rose-100 text-rose-700' : variant.stock_quantity <= 20 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'"
                                                  x-text="variant.stock_quantity"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a :href="'/barcodes/print?variant=' + variant.id" target="_blank"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-indigo-50 hover:text-indigo-700 transition-all">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                                </svg>
                                                Print
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    {{-- Empty --}}
                    <div x-show="!loading && variants.length === 0" class="text-center py-16 text-slate-400 font-medium">
                        No variants found matching your search.
                    </div>
                    {{-- Pagination --}}
                    <div x-show="pagination.last_page > 1" class="flex justify-between items-center px-6 py-4 border-t border-slate-100">
                        <p class="text-xs font-bold text-slate-500">
                            Page <span x-text="pagination.current_page"></span> of <span x-text="pagination.last_page"></span>
                        </p>
                        <div class="flex gap-2">
                            <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                                    class="px-4 py-2 text-xs font-bold border border-slate-200 rounded-xl disabled:opacity-40 hover:border-indigo-300 transition-all">← Prev</button>
                            <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page"
                                    class="px-4 py-2 text-xs font-bold border border-slate-200 rounded-xl disabled:opacity-40 hover:border-indigo-300 transition-all">Next →</button>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── TAB: History ── --}}
            <div x-show="activeTab === 'history'">
                <div x-show="historyLoading" class="flex justify-center py-20">
                    <div class="w-10 h-10 border-4 border-vibrant-indigo border-t-transparent rounded-full animate-spin"></div>
                </div>
                <div x-show="!historyLoading" class="card-glass overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-800 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider">Product / Variant</th>
                                    <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider">Old Barcode</th>
                                    <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider">New Barcode</th>
                                    <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="entry in history">
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3">
                                            <p class="font-bold text-slate-900 text-xs" x-text="entry.variant?.product?.name ?? '—'"></p>
                                            <p class="font-mono text-[10px] text-slate-400" x-text="entry.variant?.sku ?? ''"></p>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs text-slate-500 line-through" x-text="entry.old_barcode ?? '—'"></td>
                                        <td class="px-4 py-3 font-mono text-xs text-emerald-700 font-bold" x-text="entry.new_barcode ?? '—'"></td>
                                        <td class="px-4 py-3 text-xs text-slate-400 font-medium" x-text="new Date(entry.created_at).toLocaleString()"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div x-show="!historyLoading && history.length === 0" class="text-center py-16 text-slate-400 font-medium">
                        No barcode changes recorded yet.
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    function barcodeManager() {
        return {
            variants: [],
            history: [],
            loading: false,
            historyLoading: false,
            search: '',
            page: 1,
            pagination: { current_page: 1, last_page: 1 },
            selectedIds: [],
            activeTab: 'variants',
            toast: { show: false, message: '', type: 'success' },

            async fetchVariants() {
                this.loading = true;
                this.selectedIds = [];
                const params = new URLSearchParams({ page: this.page });
                if (this.search) params.set('search', this.search);
                try {
                    const res = await fetch(`/api/barcodes?${params}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    this.variants = data.data ?? [];
                    this.pagination = { current_page: data.current_page ?? 1, last_page: data.last_page ?? 1 };
                } finally {
                    this.loading = false;
                }
            },

            async fetchHistory() {
                this.historyLoading = true;
                try {
                    const res = await fetch('/barcodes/history', {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    this.history = data.data ?? data;
                } finally {
                    this.historyLoading = false;
                }
            },

            async saveBarcode(variant, newBarcode, onSuccess, onError) {
                try {
                    const productId = variant.product_id;
                    // Build a minimal update payload for the variant
                    const payload = {
                        name: variant.product?.name,
                        category_id: variant.product?.category_id,
                        variants: [{ id: variant.id, sku: variant.sku, barcode: newBarcode,
                            sale_price: variant.sale_price, minimum_sale_price: variant.minimum_sale_price,
                            purchase_price: variant.purchase_price, stock_quantity: variant.stock_quantity,
                            size: variant.size, color: variant.color }]
                    };
                    const res = await fetch(`/api/products/${productId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(payload)
                    });
                    if (!res.ok) {
                        const data = await res.json();
                        onError(data.message ?? 'Failed to update barcode.');
                        return;
                    }
                    variant.barcode = newBarcode;
                    this.showToast('Barcode updated successfully!', 'success');
                    onSuccess();
                } catch (e) {
                    onError('Network error. Please try again.');
                }
            },

            async regenerateSelected() {
                // Calls the product update for each selected variant with an empty barcode (auto-generates)
                let successCount = 0;
                for (const variantId of this.selectedIds) {
                    const variant = this.variants.find(v => v.id === variantId);
                    if (!variant) continue;
                    const payload = {
                        name: variant.product?.name,
                        category_id: variant.product?.category_id,
                        variants: [{ id: variant.id, sku: variant.sku, barcode: '',
                            sale_price: variant.sale_price, minimum_sale_price: variant.minimum_sale_price,
                            purchase_price: variant.purchase_price, stock_quantity: variant.stock_quantity,
                            size: variant.size, color: variant.color }]
                    };
                    const res = await fetch(`/api/products/${variant.product_id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) successCount++;
                }
                this.showToast(`${successCount} barcodes regenerated.`, 'success');
                this.fetchVariants();
            },

            toggleSelect(id) {
                const idx = this.selectedIds.indexOf(id);
                if (idx === -1) this.selectedIds.push(id);
                else this.selectedIds.splice(idx, 1);
            },

            toggleSelectAll(event) {
                this.selectedIds = event.target.checked ? this.variants.map(v => v.id) : [];
            },

            changePage(page) {
                if (page < 1 || page > this.pagination.last_page) return;
                this.page = page;
                this.fetchVariants();
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => { this.toast.show = false; }, 3500);
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
