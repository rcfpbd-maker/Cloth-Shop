<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Product Catalog</h2>
    </x-slot>

    <div class="py-6 sm:py-10 bg-slate-50 min-h-screen"
         x-data="productComp({{ json_encode($categories) }})"
         x-init="initData()">

        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900">Product Catalog</h1>
                    <p class="text-slate-500 font-medium mt-1">Manage all products, variants, and stock levels.</p>
                </div>
                <button @click="openAddModal()"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-vibrant-indigo text-white font-bold rounded-2xl shadow-lg hover:bg-indigo-700 transition-all hover:-translate-y-0.5 active:translate-y-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Product
                </button>
            </div>

            {{-- Filter Bar --}}
            <div class="card-glass p-4 mb-8 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input x-model="filters.search" @input.debounce.400ms="fetchData()"
                           type="text" placeholder="Search by name, SKU..."
                           class="pl-10 pr-4 py-2.5 w-full bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition"/>
                </div>
                <select x-model="filters.category_id" @change="fetchData()"
                        class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition">
                    <option value="">All Categories</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name"></option>
                    </template>
                </select>
                <button @click="filters.low_stock = !filters.low_stock; fetchData()"
                        :class="filters.low_stock ? 'bg-rose-500 text-white border-rose-500' : 'bg-white text-slate-600 border-slate-200 hover:border-rose-300'"
                        class="px-4 py-2.5 border rounded-xl text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Low Stock
                </button>
            </div>

            {{-- Grid View --}}
            <div x-show="!isLoading && list.length > 0"
                 class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <template x-for="item in list" :key="item?.id ?? Math.random()">
                    <div class="card-glass p-5 flex flex-col gap-4 group hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-black text-slate-900 truncate text-sm" x-text="item?.name || '—'"></h3>
                                <p class="text-xs font-bold text-slate-400 mt-0.5" x-text="item?.category?.name ?? '—'"></p>
                            </div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 space-y-1.5">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-slate-500">Variants</span>
                                <span class="font-black text-slate-900" x-text="item?.variants?.length ?? 0"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-slate-500">Total Stock</span>
                                <span class="font-black text-emerald-600" x-text="calcStock(item || {}) + ' pcs'"></span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                             <button @click="openEditModal(item)" class="flex-1 py-2 text-xs font-bold text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition-all">Edit</button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Empty State --}}
            <div x-show="!isLoading && list.length === 0" class="text-center py-24 bg-white rounded-3xl border-2 border-dashed border-slate-100">
                <p class="text-slate-400 font-bold">No products found</p>
            </div>

            {{-- Pagination simplified for now --}}
        </div>

        {{-- Add/Edit Modal (Simplified for verification) --}}
        <template x-if="showModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="showModal = false">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl p-8">
                     <h2 class="text-xl font-black mb-4" x-text="editingItem ? 'Edit Product' : 'Add New Product'"></h2>
                     <form @submit.prevent="saveData" class="space-y-4">
                         <input x-model="form.name" required class="w-full border-none bg-slate-50 rounded-xl p-4 font-bold" placeholder="Product Name">
                         <div class="flex gap-4">
                             <button type="button" @click="showModal = false" class="flex-1 p-4 border rounded-xl font-bold">Cancel</button>
                             <button type="submit" class="flex-1 p-4 bg-vibrant-indigo text-white rounded-xl font-bold">Save</button>
                         </div>
                     </form>
                </div>
            </div>
        </template>

    </div>

    @push('scripts')
    <script>
    function productComp(categories) {
        return {
            categories: categories,
            list: [],
            isLoading: false,
            showModal: false,
            editingItem: null,
            filters: { search: '', category_id: '', low_stock: false, page: 1 },
            form: { name: '', category_id: '', variants: [] },

            initData() {
                this.fetchData();
            },

            async fetchData() {
                this.isLoading = true;
                const p = new URLSearchParams();
                if (this.filters.search) p.set('search', this.filters.search);
                if (this.filters.category_id) p.set('category_id', this.filters.category_id);
                if (this.filters.low_stock) p.set('low_stock', 1);

                try {
                    const res = await fetch(`/api/products?${p}`, { headers: { 'Accept': 'application/json' } });
                    const d = await res.json();
                    this.list = d.data ?? d;
                } catch (e) { console.error(e); }
                finally { this.isLoading = false; }
            },

            openAddModal() {
                this.editingItem = null;
                this.form = { name: '', category_id: '', variants: [] };
                this.showModal = true;
            },

            openEditModal(item) {
                this.editingItem = item;
                this.form = { name: item.name, category_id: item.category_id, variants: [] };
                this.showModal = true;
            },

            async saveData() {
                const url = this.editingItem ? `/api/products/${this.editingItem.id}` : '/api/products';
                const method = this.editingItem ? 'PUT' : 'POST';
                try {
                    await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.form)
                    });
                    this.showModal = false;
                    this.fetchData();
                } catch (e) { console.error(e); }
            },

            calcStock(p) {
                return (p.variants ?? []).reduce((s, v) => s + (parseInt(v.stock_quantity) || 0), 0);
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
