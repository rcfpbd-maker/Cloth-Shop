<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900 bg-slate-50 selection:bg-vibrant-indigo selection:text-white">
        <div id="app" class="flex min-h-screen">
            <!-- Sidebar (Desktop) -->
            @include('layouts.sidebar')

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 bg-slate-50">
                @include('layouts.navigation')

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white border-b border-slate-200">
                        <div class="max-w-screen-2xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
        
        {{-- Global Finance Actions (Register Customer anywhere) --}}
        <div x-data="globalFinance()" @customer-created.window="location.reload()">
            <x-modal name="add-customer-global" focusable>
                <div class="p-10">
                    <h3 class="font-black text-2xl text-slate-800 mb-8 tracking-tight">Quick Customer Registration</h3>
                    <form @submit.prevent="saveCustomer" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Name</label>
                                <input type="text" x-model="form.name" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Phone</label>
                                <input type="text" x-model="form.phone" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-vibrant-indigo font-bold">
                            </div>
                        </div>
                        <div class="pt-6 flex justify-end items-center gap-6">
                            <button type="button" @click="$dispatch('close-modal', 'add-customer-global')" class="font-black text-slate-400 hover:text-slate-600 transition-colors uppercase text-[10px] tracking-widest">Cancel</button>
                            <button type="submit" class="px-10 py-5 bg-vibrant-indigo text-white font-black rounded-[1.5rem] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">Create Account</button>
                        </div>
                    </form>
                </div>
            </x-modal>
        </div>

        <script>
            function globalFinance() {
                return {
                    form: { name: '', phone: '', credit_limit: 5000 },
                    async saveCustomer() {
                        try {
                            const res = await fetch('/customers', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify(this.form)
                            });
                            if (res.ok) {
                                this.$dispatch('close-modal', 'add-customer-global');
                                this.$dispatch('customer-created');
                                this.form = { name: '', phone: '', credit_limit: 5000 };
                            }
                        } catch (e) { console.error(e); }
                    }
                }
            }

            function notifications() {
                return {
                    items: [],
                    unreadCount: 0,
                    loading: false,
                    open: false,
                    async fetchNotifications() {
                        this.loading = true;
                        try {
                            const res = await fetch('/api/notifications', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (res.ok) {
                                this.items = await res.json();
                                this.unreadCount = this.items.length;
                            }
                        } catch (e) {
                            console.error('Failed to load notifications', e);
                        } finally {
                            this.loading = false;
                        }
                    },
                    async markAsRead(id) {
                        try {
                            const res = await fetch(`/api/notifications/${id}/read`, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (res.ok) {
                                // removing from items so it clears instantly
                                this.items = this.items.filter(i => i.id !== id);
                                this.unreadCount = this.items.length;
                            }
                        } catch (e) {}
                    },
                    async markAllAsRead() {
                        try {
                            const res = await fetch(`/api/notifications/read-all`, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (res.ok) {
                                this.items = [];
                                this.unreadCount = 0;
                            }
                        } catch (e) {}
                    },
                    formatDate(dateString) {
                        const date = new Date(dateString);
                        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    }
                }
            }

            function omniSearch() {
                return {
                    query: '',
                    results: { products: [], customers: [], suppliers: [] },
                    loading: false,
                    open: false,
                    async fetchResults() {
                        if (this.query.length < 2) {
                            this.results = { products: [], customers: [], suppliers: [] };
                            this.open = false;
                            return;
                        }
                        this.loading = true;
                        this.open = true;
                        try {
                            const res = await fetch(`/api/search?q=${encodeURIComponent(this.query)}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (res.ok) {
                                this.results = await res.json();
                            }
                        } catch (e) {
                            console.error('Search failed', e);
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }

            // Ensure Alpine is started AFTER all stack scripts have registered their data/init listeners
            window.addEventListener('DOMContentLoaded', () => {
                if (window.Alpine) {
                    window.Alpine.start();
                }
            });
        </script>
    </body>
</html>
