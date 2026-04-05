<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Supplier management</h2>
    </x-slot>

    <div class="py-6 sm:py-10 bg-slate-50 min-h-screen"
         x-data="supplierComp({{ json_encode($suppliers->items() ?? []) }})"
         x-init="initData()">

        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900">Suppliers</h1>
                    <p class="text-slate-500 font-medium mt-1">Manage all your product suppliers and their balances.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="openAddModal()"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-vibrant-indigo text-white font-bold rounded-2xl shadow-lg hover:bg-indigo-700 transition-all hover:-translate-y-0.5 active:translate-y-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Supplier
                    </button>
                </div>
            </div>

            {{-- Grid View --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="item in list" :key="item.id">
                    <div class="card-glass p-6 flex flex-col gap-4 group hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-500 font-black shadow-inner">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-black text-slate-900 truncate" x-text="item.name"></h3>
                                    <p class="text-xs font-bold text-slate-400" x-text="item.company_name ?? 'Individual'"></p>
                                </div>
                            </div>
                            <span :class="item.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'"
                                  class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"
                                  x-text="item.status"></span>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-4 space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-slate-500">Phone</span>
                                <span class="font-black text-slate-900" x-text="item.phone"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-slate-500">Balance Due</span>
                                <span class="font-black text-rose-600 font-mono" x-text="'৳' + (parseFloat(item.previous_due) || 0).toLocaleString()"></span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                             <button @click="openEditModal(item)" class="flex-1 py-3 bg-indigo-50 font-black text-xs text-indigo-600 rounded-xl hover:bg-indigo-100 transition-all">Edit</button>
                             <a :href="'/suppliers/' + item.id + '/ledger'" class="flex-1 py-3 bg-white font-black text-xs text-slate-600 rounded-xl border border-slate-100 hover:border-slate-300 text-center transition-all shadow-sm">Ledger</a>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="list.length === 0" class="py-24 text-center bg-white rounded-[2rem] border-2 border-dashed border-slate-100">
                <p class="text-slate-400 font-bold">No suppliers found. Start by adding one!</p>
            </div>
            
            <div class="mt-6">
                {{ $suppliers->links() }}
            </div>
        </div>

        {{-- Add/Edit Modal --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             style="display: none;"
             @click.self="showModal = false">
            
            <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-xl overflow-hidden ring-1 ring-white/50">
                <div class="p-8">
                    <h2 class="text-2xl font-black text-slate-900 mb-6" x-text="editingItem ? 'Edit Supplier' : 'Add New Supplier'"></h2>
                    
                    <form @submit.prevent="saveData()" class="grid grid-cols-2 gap-6">
                        <div class="col-span-2 space-y-2">
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest ml-1">Supplier Name</label>
                            <input type="text" x-model="form.name" required
                                   class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-700 focus:ring-2 focus:ring-vibrant-indigo transition-all"
                                   placeholder="Full Name">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest ml-1">Phone Number</label>
                            <input type="text" x-model="form.phone" required
                                   class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-700 focus:ring-2 focus:ring-vibrant-indigo transition-all"
                                   placeholder="01xxxxxxxxx">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest ml-1">Company Name</label>
                            <input type="text" x-model="form.company_name"
                                   class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-700 focus:ring-2 focus:ring-vibrant-indigo transition-all"
                                   placeholder="optional">
                        </div>

                        <div class="col-span-2 space-y-2">
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest ml-1">Address</label>
                            <textarea x-model="form.address"
                                      class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-700 focus:ring-2 focus:ring-vibrant-indigo transition-all"
                                      placeholder="Supplier Address..."></textarea>
                        </div>

                        <div class="flex gap-4 pt-4 col-span-2">
                            <button type="button" @click="showModal = false"
                                    class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl hover:bg-slate-200 transition-all">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="flex-1 px-6 py-4 bg-vibrant-indigo text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all">
                                Save Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function supplierComp(initialList) {
        return {
            list: initialList,
            showModal: false,
            editingItem: null,
            form: {
                name: '',
                phone: '',
                company_name: '',
                address: '',
                status: 'active'
            },

            initData() {
                // Initialize if needed
            },

            openAddModal() {
                this.editingItem = null;
                this.resetForm();
                this.showModal = true;
            },

            openEditModal(item) {
                this.editingItem = item;
                this.form = {
                    name: item.name,
                    phone: item.phone,
                    company_name: item.company_name,
                    address: item.address,
                    status: item.status
                };
                this.showModal = true;
            },

            resetForm() {
                this.form = { name: '', phone: '', company_name: '', address: '', status: 'active' };
            },

            async saveData() {
                const url = this.editingItem ? `/suppliers/${this.editingItem.id}` : '/suppliers';
                const method = this.editingItem ? 'PUT' : 'POST';
                
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });
                    
                    const result = await response.json();
                    if (response.ok) {
                        this.showModal = false;
                        window.location.reload(); // Simple reload to refresh list and pagination
                    } else {
                        alert(result.message || 'Something went wrong');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Request failed');
                }
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
