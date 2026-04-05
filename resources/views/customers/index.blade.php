<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center text-slate-800">
            <h2 class="font-black text-2xl leading-tight">Customer Database</h2>
            {{-- Global 'New Customer' button in navigation handles creation --}}
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen" 
         x-data="customerRegistry()" 
         x-init="init()"
         @customer-created.window="fetchData()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Search Bar --}}
            <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
                <div class="relative group">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-vibrant-indigo transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    </span>
                    <input type="text" x-model="search" @input.debounce.300ms="fetchData()"
                           placeholder="Search by name or phone..." 
                           class="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-slate-700 transition-all">
                </div>
            </div>

            {{-- Customers Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-show="items.length > 0">
                <template x-for="c in items" :key="c?.id ?? Math.random()">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group overflow-hidden relative">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-vibrant-indigo group-hover:bg-vibrant-indigo group-hover:text-white transition-all shadow-sm">
                                <span class="font-black text-xl" x-text="(c.name || '?').charAt(0)"></span>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Due</span>
                                <span :class="parseFloat(c?.previous_due || 0) > 0 ? 'text-rose-600' : 'text-emerald-600'" 
                                      class="font-black text-xl" x-text="'৳' + parseFloat(c?.previous_due || 0).toLocaleString()"></span>
                            </div>
                        </div>

                        <h3 class="font-black text-lg text-slate-800 mb-1" x-text="c.name"></h3>
                        <p class="text-sm font-bold text-slate-400 mb-6" x-text="c.phone"></p>

                        <div class="flex gap-2">
                            <a :href="'/customers/' + (c?.id || 0) + '/ledger'" class="flex-1 py-3 bg-slate-900 text-white text-center rounded-xl font-black text-sm hover:bg-slate-800 transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                                Ledger
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Empty State --}}
            <div x-show="!loading && items.length === 0" class="text-center py-20 bg-white rounded-[3rem] border-2 border-dashed border-slate-100">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h3 class="font-black text-xl text-slate-800 mb-2">No Customers Found</h3>
                <p class="text-slate-400 font-bold max-w-xs mx-auto text-sm">Use the "New Customer" button in the navigation to add someone.</p>
            </div>
            
            {{-- Loading State --}}
            <div x-show="loading" class="flex justify-center py-10">
                <div class="w-8 h-8 border-4 border-vibrant-indigo border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function customerRegistry() {
        return {
            items: [],
            search: '',
            loading: false,

            init() {
                this.fetchData();
            },

            async fetchData() {
                this.loading = true;
                try {
                    const res = await fetch('/customers?search=' + encodeURIComponent(this.search), { 
                        headers: { 
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        } 
                    });
                    const data = await res.json();
                    this.items = data.data || [];
                } catch (e) { 
                    console.error('Fetch failed:', e);
                    this.items = [];
                } finally {
                    this.loading = false;
                }
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
