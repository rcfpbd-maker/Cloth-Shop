<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-800 leading-tight">Database Backup & Restore</h2>
        <p class="text-slate-500 font-medium mt-1">Safeguard your data by managing system backups and restoring points securely.</p>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen" x-data="backupManager()" x-init="fetchBackups()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Main Actions -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex justify-between items-center">
                <div>
                    <h3 class="font-black text-slate-800 text-lg">Manual Backup</h3>
                    <p class="text-slate-500 text-sm mt-0.5">Generate a complete database snapshot instantly.</p>
                </div>
                
                @can('settings.manage')
                <button @click="createBackup()"
                        :disabled="isCreating"
                        class="bg-vibrant-indigo text-white font-bold py-3 px-6 rounded-2xl hover:bg-indigo-700 transition-all flex items-center gap-2 shadow-lg hover:-translate-y-0.5 disabled:opacity-50 disabled:translate-y-0 cursor-pointer">
                    <svg x-show="!isCreating" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <svg x-show="isCreating" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span x-text="isCreating ? 'Creating Backup...' : 'Generate New Backup'"></span>
                </button>
                @endcan
            </div>

            <!-- Backup History Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden relative">
                
                <div x-show="loading" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-10 flex items-center justify-center">
                    <div class="w-8 h-8 border-4 border-vibrant-indigo border-t-transparent rounded-full animate-spin"></div>
                </div>

                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-black text-slate-800 text-lg">Backup History</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest pl-6">Saved Datetime</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">File Name</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Triggered By</th>
                                <th class="p-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right pr-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="backup in backups" :key="backup.id">
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="p-4 pl-6">
                                        <span class="text-sm font-bold text-slate-800" x-text="formatDate(backup.created_at)"></span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="text-xs font-semibold text-slate-600" x-text="backup.file_name"></span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 bg-slate-100 text-slate-700 text-[10px] font-black rounded uppercase tracking-wider" x-text="backup.creator?.name || 'System'"></span>
                                    </td>
                                    <td class="p-4 text-right pr-6">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a :href="'/api/backups/' + backup.id + '/download'" target="_blank"
                                               class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 flex items-center justify-center transition-colors" title="Download">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                            
                                            @can('settings.manage')
                                            <button @click="confirmRestore(backup)" 
                                                    class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition-colors shadow-sm" title="Restore Data">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            
                            <tr x-show="backups.length === 0 && !loading">
                                <td colspan="4" class="p-10 text-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    </div>
                                    <p class="text-slate-500 font-bold">No backups available.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-slate-100 flex justify-between items-center" x-show="pagination.last_page > 1">
                    <span class="text-xs font-bold text-slate-500" x-text="'Showing page ' + pagination.current_page + ' of ' + pagination.last_page"></span>
                    <div class="flex gap-2">
                        <button @click="fetchBackups(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 bg-slate-100 text-slate-600 font-bold rounded hover:bg-slate-200 disabled:opacity-50 text-xs">Prev</button>
                        <button @click="fetchBackups(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 bg-slate-100 text-slate-600 font-bold rounded hover:bg-slate-200 disabled:opacity-50 text-xs">Next</button>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Restore Confirmation Modal -->
        <template x-if="restoreModalOpen">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="restoreModalOpen = false">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 border border-slate-100">
                    <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-center text-slate-900 mb-2">Confirm Restoration</h3>
                    <p class="text-slate-500 text-center text-sm font-medium mb-6">
                        Are you sure you want to restore the database from "<span class="font-bold text-slate-800" x-text="selectedBackup?.file_name"></span>"? This action will overwrite all current system data and cannot be undone.
                    </p>

                    <div class="flex gap-3">
                        <button @click="restoreModalOpen = false" :disabled="isRestoring" class="flex-1 px-4 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-colors">
                            Cancel
                        </button>
                        <button @click="processRestore()" :disabled="isRestoring" class="flex-1 px-4 py-3 bg-rose-600 text-white font-bold rounded-xl hover:bg-rose-700 transition-colors shadow-lg flex items-center justify-center gap-2">
                            <svg x-show="isRestoring" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span x-text="isRestoring ? 'Restoring...' : 'Yes, Restore'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
        
    </div>

    @push('scripts')
    <script>
    function backupManager() {
        return {
            backups: [],
            loading: false,
            isCreating: false,
            isRestoring: false,
            restoreModalOpen: false,
            selectedBackup: null,
            pagination: { current_page: 1, last_page: 1 },

            async fetchBackups(page = 1) {
                this.loading = true;
                try {
                    let url = new URL('/api/backups', window.location.origin);
                    url.searchParams.append('page', page);

                    const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await res.json();
                    
                    this.backups = data.data;
                    this.pagination.current_page = data.current_page;
                    this.pagination.last_page = data.last_page;
                } catch (e) {
                    console.error('Failed to load backups', e);
                } finally {
                    this.loading = false;
                }
            },
            
            async createBackup() {
                this.isCreating = true;
                try {
                    const res = await fetch('/api/backups', { 
                        method: 'POST',
                        headers: { 
                            'Accept': 'application/json', 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        } 
                    });
                    const data = await res.json();
                    if(data.status === 'success') {
                        this.fetchBackups(1);
                    } else {
                        alert(data.message || 'Failed to generate backup.');
                    }
                } catch (e) {
                    console.error('Failed to create backup', e);
                    alert('Error reaching the server while generating backup.');
                } finally {
                    this.isCreating = false;
                }
            },

            confirmRestore(backup) {
                this.selectedBackup = backup;
                this.restoreModalOpen = true;
            },

            async processRestore() {
                if(!this.selectedBackup) return;
                this.isRestoring = true;
                try {
                    const res = await fetch(`/api/backups/${this.selectedBackup.id}/restore`, { 
                        method: 'POST',
                        headers: { 
                            'Accept': 'application/json', 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        } 
                    });
                    const data = await res.json();
                    if(data.status === 'success') {
                        alert('System Restored Successfully! Refreshing...');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to restore backup.');
                    }
                } catch (e) {
                    console.error('Failed to restore', e);
                    alert('Error reaching the server. Ensure the database service is running correctly.');
                } finally {
                    this.isRestoring = false;
                    this.restoreModalOpen = false;
                }
            },
            
            formatDate(dateString) {
                const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                return new Date(dateString).toLocaleDateString(undefined, options);
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
