<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-800 leading-tight">System Activity Logs</h2>
        <p class="text-slate-500 font-medium mt-1">Audit trail of all administrative and operational actions across the system.</p>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen" x-data="activityLogs()" x-init="fetchLogs()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filters -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Module</label>
                    <select x-model="filters.module" @change="fetchLogs(1)" class="w-full bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-slate-700 py-3">
                        <option value="">All Modules</option>
                        <option value="auth">Auth & Security</option>
                        <option value="inventory">Inventory</option>
                        <option value="sales">Sales & POS</option>
                        <option value="customers">Customers</option>
                        <option value="accounts">Accounts & Finance</option>
                        <option value="reports">Reports</option>
                    </select>
                </div>
                <!-- Time Range -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Date Range</label>
                    <select x-model="filters.range" @change="fetchLogs(1)" class="w-full bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-vibrant-indigo font-bold text-slate-700 py-3">
                        <option value="today">Today</option>
                        <option value="7days">Last 7 Days</option>
                        <option value="30days">Last 30 Days</option>
                        <option value="all">All Time</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button @click="fetchLogs(1)" class="bg-vibrant-indigo text-white font-bold py-3 px-6 rounded-xl hover:bg-indigo-700 transition">Filter</button>
                    <button @click="resetFilters()" class="bg-slate-100 text-slate-600 font-bold py-3 px-6 rounded-xl hover:bg-slate-200 transition">Reset</button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden relative">
                
                <div x-show="loading" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-10 flex items-center justify-center">
                    <div class="w-8 h-8 border-4 border-vibrant-indigo border-t-transparent rounded-full animate-spin"></div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Timestamp</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">User</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Module</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Action</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest w-96">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="log in logs" :key="log.id">
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4">
                                        <span class="text-xs font-bold text-slate-500" x-text="formatDate(log.created_at)"></span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-black text-slate-600 uppercase" x-text="(log.user?.name || '?').charAt(0)"></div>
                                            <span class="text-sm font-black text-slate-800" x-text="log.user?.name || 'System'"></span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 bg-indigo-50 text-vibrant-indigo text-[10px] font-black rounded uppercase tracking-wider" x-text="log.module"></span>
                                    </td>
                                    <td class="p-4">
                                        <span :class="{
                                            'bg-emerald-50 text-emerald-600': log.action_type === 'create',
                                            'bg-amber-50 text-amber-600': log.action_type === 'update',
                                            'bg-rose-50 text-rose-600': log.action_type === 'delete',
                                            'bg-sky-50 text-sky-600': log.action_type === 'login' || log.action_type === 'logout'
                                        }" class="px-2 py-1 text-[10px] font-black rounded uppercase tracking-wider" x-text="log.action_type"></span>
                                    </td>
                                    <td class="p-4">
                                        <p class="text-xs font-medium text-slate-600 line-clamp-2" x-text="log.description"></p>
                                    </td>
                                </tr>
                            </template>
                            
                            <tr x-show="logs.length === 0 && !loading">
                                <td colspan="5" class="p-10 text-center">
                                    <p class="text-slate-500 font-bold">No activity logs found for the selected criteria.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="p-4 border-t border-slate-100 flex justify-between items-center" x-show="pagination.last_page > 1">
                    <span class="text-xs font-bold text-slate-500" x-text="'Showing page ' + pagination.current_page + ' of ' + pagination.last_page"></span>
                    <div class="flex gap-2">
                        <button @click="fetchLogs(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 bg-slate-100 text-slate-600 font-bold rounded hover:bg-slate-200 disabled:opacity-50 text-xs">Prev</button>
                        <button @click="fetchLogs(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 bg-slate-100 text-slate-600 font-bold rounded hover:bg-slate-200 disabled:opacity-50 text-xs">Next</button>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    @push('scripts')
    <script>
    function activityLogs() {
        return {
            logs: [],
            loading: false,
            filters: {
                module: '',
                range: '7days' // default to last 7 days
            },
            pagination: { current_page: 1, last_page: 1 },

            resetFilters() {
                this.filters.module = '';
                this.filters.range = '7days';
                this.fetchLogs(1);
            },

            async fetchLogs(page = 1) {
                this.loading = true;
                try {
                    let url = new URL('/api/activity-logs', window.location.origin);
                    url.searchParams.append('page', page);
                    if (this.filters.module) url.searchParams.append('module', this.filters.module);
                    
                    // Simple date logic for the frontend
                    let fromDate = new Date();
                    if (this.filters.range === 'today') {
                        fromDate.setHours(0,0,0,0);
                    } else if (this.filters.range === '7days') {
                        fromDate.setDate(fromDate.getDate() - 7);
                    } else if (this.filters.range === '30days') {
                        fromDate.setDate(fromDate.getDate() - 30);
                    }
                    
                    if (this.filters.range !== 'all') {
                        let isoDate = fromDate.toISOString().split('T')[0];
                        url.searchParams.append('from_date', isoDate);
                    }

                    const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await res.json();
                    
                    this.logs = data.data;
                    this.pagination.current_page = data.current_page;
                    this.pagination.last_page = data.last_page;
                    
                } catch (e) {
                    console.error('Failed to load logs', e);
                } finally {
                    this.loading = false;
                }
            },
            
            formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleString();
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
