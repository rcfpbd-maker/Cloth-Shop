<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Product Catalog</h2>
    </x-slot>

    <div v-pre class="py-6 sm:py-10 bg-slate-50 min-h-screen"
         x-data="productCatalog({{ json_encode($categories) }})"
         x-init="fetchProducts()">

        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900">Product Catalog</h1>
                    <p class="text-slate-500 font-medium mt-1">Manage all products, variants, and stock levels.</p>
                </div>
                <button @click="openAddModal()"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-vibrant-indigo text-white font-bold rounded-2xl shadow-lg hover:bg-indigo-700 transition-all hover:-translate-y-0.5 active:translate-y-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Product
                </button>
            </div>

            {{-- Filter Bar --}}
            <div class="card-glass p-4 mb-8 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input x-model="filters.search" @input.debounce.400ms="fetchProducts()"
                           type="text" placeholder="Search by name, SKU..."
                           class="pl-10 pr-4 py-2.5 w-full bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-vibrant-indigo focus:border-transparent transition"/>
                </div>
                <select x-model="filters.category_id" @change="fetchProducts()"
                        class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition">
                    <option value="">All Categories</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name"></option>
                    </template>
                </select>
                <button @click="filters.low_stock = !filters.low_stock; fetchProducts()"
                        :class="filters.low_stock ? 'bg-rose-500 text-white border-rose-500' : 'bg-white text-slate-600 border-slate-200 hover:border-rose-300'"
                        class="px-4 py-2.5 border rounded-xl text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Low Stock
                </button>
                <button @click="viewMode = viewMode === 'grid' ? 'table' : 'grid'"
                        class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:border-indigo-300 transition-all flex items-center gap-2">
                    <svg x-show="viewMode === 'grid'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
                    </svg>
                    <svg x-show="viewMode === 'table'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span x-text="viewMode === 'grid' ? 'Table View' : 'Grid View'"></span>
                </button>
            </div>

            {{-- Loading State --}}
            <div x-show="loading" class="flex justify-center items-center py-24">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-vibrant-indigo border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-slate-500 font-medium">Loading products...</p>
                </div>
            </div>

            {{-- Empty State --}}
            <div x-show="!loading && products.length === 0" class="text-center py-24">
                <div class="w-20 h-20 bg-slate-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-black text-slate-700 mb-2">No products found</h3>
                <p class="text-slate-400 font-medium mb-6">Try adjusting your filters or add your first product.</p>
                <button @click="openAddModal()" class="px-6 py-3 bg-vibrant-indigo text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all">
                    Add First Product
                </button>
            </div>

            {{-- Grid View --}}
            <div x-show="!loading && products.length > 0 && viewMode === 'grid'"
                 class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <template x-for="product in products" :key="product.id">
                    <div class="card-glass p-5 flex flex-col gap-4 group hover:-translate-y-1 transition-all duration-300">
                        {{-- Product Header --}}
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-black text-slate-900 truncate text-sm" x-text="product.name"></h3>
                                <p class="text-xs font-bold text-slate-400 mt-0.5" x-text="product.category?.name ?? '—'"></p>
                            </div>
                            <span :class="product.status == 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                  class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full whitespace-nowrap ml-2"
                                  x-text="product.status == 1 ? 'Active' : 'Inactive'"></span>
                        </div>

                        {{-- Meta Tags --}}
                        <div class="flex flex-wrap gap-1.5">
                            <span x-show="product.brand" class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-lg" x-text="product.brand"></span>
                            <span x-show="product.fabric_type" class="text-[10px] font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded-lg" x-text="product.fabric_type"></span>
                        </div>

                        {{-- Variants Summary --}}
                        <div class="bg-slate-50 rounded-xl p-3 space-y-1.5">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-slate-500">Variants</span>
                                <span class="font-black text-slate-900" x-text="product.variants?.length ?? 0"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-slate-500">Total Stock</span>
                                <span class="font-black"
                                      :class="totalStock(product) <= 5 ? 'text-rose-600' : totalStock(product) <= 20 ? 'text-amber-600' : 'text-emerald-600'"
                                      x-text="totalStock(product) + ' pcs'"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-slate-500">Sale Price</span>
                                <span class="font-black text-slate-900" x-text="minPrice(product)"></span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 mt-auto pt-2 border-t border-slate-100">
                            <button @click="openEditModal(product)"
                                    class="flex-1 py-2 text-xs font-bold text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition-all">
                                Edit
                            </button>
                            <button @click="confirmDelete(product)"
                                    class="flex-1 py-2 text-xs font-bold text-rose-600 bg-rose-50 rounded-xl hover:bg-rose-100 transition-all">
                                Delete
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Table View --}}
            <div x-show="!loading && products.length > 0 && viewMode === 'table'" class="card-glass overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider">Brand</th>
                                <th class="px-4 py-3 text-center text-xs font-black uppercase tracking-wider">Variants</th>
                                <th class="px-4 py-3 text-center text-xs font-black uppercase tracking-wider">Stock</th>
                                <th class="px-4 py-3 text-center text-xs font-black uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-black uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="product in products" :key="product.id">
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 font-bold text-slate-900" x-text="product.name"></td>
                                    <td class="px-4 py-3 text-slate-500 font-medium" x-text="product.category?.name ?? '—'"></td>
                                    <td class="px-4 py-3 text-slate-500 font-medium" x-text="product.brand ?? '—'"></td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-700" x-text="product.variants?.length ?? 0"></td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="font-black text-xs px-2 py-1 rounded-full"
                                              :class="totalStock(product) <= 5 ? 'bg-rose-100 text-rose-700' : totalStock(product) <= 20 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'"
                                              x-text="totalStock(product)"></span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-xs font-black px-2 py-1 rounded-full"
                                              :class="product.status == 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                              x-text="product.status == 1 ? 'Active' : 'Inactive'"></span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openEditModal(product)"
                                                    class="px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-all">Edit</button>
                                            <button @click="confirmDelete(product)"
                                                    class="px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 transition-all">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            <div x-show="!loading && pagination.last_page > 1" class="flex justify-center items-center gap-2 mt-8">
                <button @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page <= 1"
                        class="px-4 py-2 rounded-xl text-sm font-bold text-slate-600 bg-white border border-slate-200 disabled:opacity-40 hover:border-indigo-300 transition-all">
                    ← Prev
                </button>
                <div class="flex gap-1">
                    <template x-for="page in paginationPages()" :key="page">
                        <button @click="changePage(page)"
                                :class="page === pagination.current_page ? 'bg-vibrant-indigo text-white' : 'bg-white text-slate-600 hover:border-indigo-300 border border-slate-200'"
                                class="w-10 h-10 rounded-xl text-sm font-bold transition-all" x-text="page"></button>
                    </template>
                </div>
                <button @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= pagination.last_page"
                        class="px-4 py-2 rounded-xl text-sm font-bold text-slate-600 bg-white border border-slate-200 disabled:opacity-40 hover:border-indigo-300 transition-all">
                    Next →
                </button>
            </div>
        </div>

        {{-- ═══════════ ADD / EDIT MODAL ═══════════ --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             @keydown.escape.window="showModal = false">

            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.stop
                 class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">

                {{-- Modal Header --}}
                <div class="sticky top-0 bg-white border-b border-slate-100 px-8 py-5 flex items-center justify-between z-10 rounded-t-3xl">
                    <h2 class="text-xl font-black text-slate-900" x-text="editingProduct ? 'Edit Product' : 'Add New Product'"></h2>
                    <button @click="showModal = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveProduct()" class="px-8 py-6 space-y-6">

                    {{-- Error Banner --}}
                    <div x-show="formError" class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-medium" x-text="formError"></div>

                    {{-- Product Info --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Product Name <span class="text-rose-500">*</span></label>
                            <input x-model="form.name" type="text" placeholder="e.g. Premium Cotton Shirt"
                                   class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo focus:border-transparent transition" required/>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Category <span class="text-rose-500">*</span></label>
                            <select x-model="form.category_id"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition" required>
                                <option value="">Select Category</option>
                                <template x-for="cat in categories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Brand</label>
                            <input x-model="form.brand" type="text" placeholder="Brand name"
                                   class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition"/>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Fabric Type</label>
                            <input x-model="form.fabric_type" type="text" placeholder="e.g. Cotton, Denim"
                                   class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition"/>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Status</label>
                            <select x-model="form.status"
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Description</label>
                            <textarea x-model="form.description" rows="2" placeholder="Optional product description..."
                                      class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition resize-none"></textarea>
                        </div>
                    </div>

                    {{-- Variants Section --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Variants <span class="text-rose-500">*</span></h3>
                            <button type="button" @click="addVariant()"
                                    class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition-all flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Variant
                            </button>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(variant, index) in form.variants" :key="index">
                                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 relative group">
                                    <button type="button" @click="removeVariant(index)"
                                            x-show="form.variants.length > 1"
                                            class="absolute top-3 right-3 w-6 h-6 rounded-full bg-rose-100 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1">SKU *</label>
                                            <input x-model="variant.sku" type="text" placeholder="SKU-001"
                                                   class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition" required/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1">Barcode</label>
                                            <input x-model="variant.barcode" type="text" placeholder="Auto-generated"
                                                   class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition bg-slate-50"/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1">Size</label>
                                            <input x-model="variant.size" type="text" placeholder="S, M, L, XL"
                                                   class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition"/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1">Color</label>
                                            <input x-model="variant.color" type="text" placeholder="Red, Blue..."
                                                   class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition"/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1">Sale Price *</label>
                                            <input x-model="variant.sale_price" type="number" step="0.01" min="0" placeholder="0.00"
                                                   class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition" required/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1">Min Sale Price *</label>
                                            <input x-model="variant.minimum_sale_price" type="number" step="0.01" min="0" placeholder="0.00"
                                                   class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition" required/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1">Purchase Price *</label>
                                            <input x-model="variant.purchase_price" type="number" step="0.01" min="0" placeholder="0.00"
                                                   class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition" required/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1">Stock Qty *</label>
                                            <input x-model="variant.stock_quantity" type="number" min="0" placeholder="0"
                                                   class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-vibrant-indigo transition" required/>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showModal = false"
                                class="flex-1 py-3 border-2 border-slate-200 text-slate-600 font-bold rounded-2xl hover:border-slate-300 transition-all">
                            Cancel
                        </button>
                        <button type="submit" :disabled="saving"
                                class="flex-1 py-3 bg-vibrant-indigo text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all disabled:opacity-60 flex items-center justify-center gap-2">
                            <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span x-text="saving ? 'Saving...' : (editingProduct ? 'Save Changes' : 'Add Product')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═══════════ DELETE CONFIRM MODAL ═══════════ --}}
        <div x-show="showDeleteModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div x-show="showDeleteModal"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center">
                <div class="w-16 h-16 bg-rose-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-lg font-black text-slate-900 mb-2">Delete Product?</h3>
                <p class="text-slate-500 font-medium text-sm mb-6">
                    Are you sure you want to delete <strong x-text="deletingProduct?.name"></strong>? This action cannot be undone.
                </p>
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 py-3 border-2 border-slate-200 text-slate-600 font-bold rounded-2xl hover:border-slate-300 transition-all">Cancel</button>
                    <button @click="deleteProduct()" :disabled="deleting"
                            class="flex-1 py-3 bg-rose-500 text-white font-bold rounded-2xl hover:bg-rose-600 transition-all disabled:opacity-60">
                        <span x-text="deleting ? 'Deleting...' : 'Delete'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function productCatalog(categories) {
        return {
            categories: categories,
            products: [],
            loading: false,
            saving: false,
            deleting: false,
            viewMode: 'grid',
            showModal: false,
            showDeleteModal: false,
            editingProduct: null,
            deletingProduct: null,
            formError: '',
            pagination: { current_page: 1, last_page: 1 },
            filters: { search: '', category_id: '', low_stock: false, page: 1 },
            form: { name: '', category_id: '', brand: '', fabric_type: '', description: '', status: 1, variants: [] },

            async fetchProducts() {
                this.loading = true;
                const params = new URLSearchParams();
                if (this.filters.search) params.set('search', this.filters.search);
                if (this.filters.category_id) params.set('category_id', this.filters.category_id);
                if (this.filters.low_stock) params.set('low_stock', 1);
                params.set('page', this.filters.page);

                try {
                    const res = await fetch(`/api/products?${params}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    this.products = data.data ?? data;
                    if (data.last_page) this.pagination = { current_page: data.current_page, last_page: data.last_page };
                } catch (e) {
                    console.error('Failed to load products', e);
                } finally {
                    this.loading = false;
                }
            },

            openAddModal() {
                this.editingProduct = null;
                this.formError = '';
                this.form = {
                    name: '', category_id: '', subcategory_id: '', brand: '', fabric_type: '', description: '', status: 1,
                    variants: [this.blankVariant()]
                };
                this.showModal = true;
            },

            openEditModal(product) {
                this.editingProduct = product;
                this.formError = '';
                this.form = {
                    name: product.name,
                    category_id: product.category_id,
                    subcategory_id: product.subcategory_id ?? '',
                    brand: product.brand ?? '',
                    fabric_type: product.fabric_type ?? '',
                    description: product.description ?? '',
                    status: product.status,
                    variants: (product.variants ?? []).map(v => ({
                        id: v.id, sku: v.sku, barcode: v.barcode ?? '', size: v.size ?? '',
                        color: v.color ?? '', sale_price: v.sale_price, minimum_sale_price: v.minimum_sale_price,
                        purchase_price: v.purchase_price, stock_quantity: v.stock_quantity
                    }))
                };
                if (this.form.variants.length === 0) this.form.variants.push(this.blankVariant());
                this.showModal = true;
            },

            blankVariant() {
                return { sku: '', barcode: '', size: '', color: '', sale_price: '', minimum_sale_price: '', purchase_price: '', stock_quantity: 0 };
            },

            addVariant() {
                this.form.variants.push(this.blankVariant());
            },

            removeVariant(index) {
                this.form.variants.splice(index, 1);
            },

            async saveProduct() {
                this.saving = true;
                this.formError = '';
                const url = this.editingProduct ? `/api/products/${this.editingProduct.id}` : '/api/products';
                const method = this.editingProduct ? 'PUT' : 'POST';

                try {
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.form)
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.formError = data.message ?? (data.errors ? Object.values(data.errors).flat().join(' ') : 'An error occurred.');
                        return;
                    }
                    this.showModal = false;
                    await this.fetchProducts();
                } catch (e) {
                    this.formError = 'Network error. Please try again.';
                } finally {
                    this.saving = false;
                }
            },

            confirmDelete(product) {
                this.deletingProduct = product;
                this.showDeleteModal = true;
            },

            async deleteProduct() {
                this.deleting = true;
                try {
                    await fetch(`/api/products/${this.deletingProduct.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    this.showDeleteModal = false;
                    await this.fetchProducts();
                } finally {
                    this.deleting = false;
                }
            },

            totalStock(product) {
                return (product.variants ?? []).reduce((sum, v) => sum + (parseInt(v.stock_quantity) || 0), 0);
            },

            minPrice(product) {
                const prices = (product.variants ?? []).map(v => parseFloat(v.sale_price) || 0).filter(p => p > 0);
                if (!prices.length) return '—';
                const min = Math.min(...prices);
                const max = Math.max(...prices);
                return min === max ? `৳${min.toFixed(2)}` : `৳${min.toFixed(2)} – ৳${max.toFixed(2)}`;
            },

            changePage(page) {
                if (page < 1 || page > this.pagination.last_page) return;
                this.filters.page = page;
                this.fetchProducts();
            },

            paginationPages() {
                const pages = [], total = this.pagination.last_page, curr = this.pagination.current_page;
                const range = 2;
                for (let i = Math.max(1, curr - range); i <= Math.min(total, curr + range); i++) pages.push(i);
                return pages;
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
