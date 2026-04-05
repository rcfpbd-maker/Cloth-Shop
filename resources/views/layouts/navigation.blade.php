<nav v-pre x-data="{ open: false }" class="bg-white border-b border-slate-200 sticky top-0 z-40 h-20 flex items-center shadow-sm">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="flex justify-between items-center h-full">
            
            <!-- Left Side: Mobile Menu Button & Search -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Button (Hamburger) -->
                <button @click="$dispatch('open-mobile-sidebar')" class="lg:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-100 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                <!-- Page Breadcrumb (Desktop) -->
                <div class="hidden lg:flex items-center space-x-2 text-sm font-bold text-slate-400">
                    <span class="hover:text-vibrant-indigo cursor-pointer transition-colors">{{ str_replace('.', ' / ', request()->route()->getName()) }}</span>
                </div>
            </div>

            <!-- Right Side: Search, Notifications, Profile -->
            <div class="flex items-center space-x-2 sm:space-x-6">
                <!-- Global Search -->
                <div class="relative group z-50" x-data="omniSearch()" @click.away="open = false">
                    <input type="text" 
                           x-model="query" 
                           @input.debounce.300ms="fetchResults"
                           @focus="if(query.length > 1) open = true"
                           placeholder="Search everything... (Press /)" 
                           class="bg-slate-50 text-slate-600 rounded-2xl pl-10 pr-4 py-2 border-slate-200 focus:ring-2 focus:ring-vibrant-indigo w-40 sm:w-64 transition-all focus:w-80 shadow-inner" />
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-slate-400 group-focus-within:text-vibrant-indigo" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    
                    <!-- Search Results Overlay -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 mt-2 w-full min-w-[300px] sm:min-w-[400px] bg-white rounded-2xl shadow-2xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                        
                        <div x-show="loading" class="p-8 flex justify-center items-center">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                        </div>

                        <div x-show="!loading" class="max-h-[60vh] overflow-y-auto">
                            <!-- Products -->
                            <template x-if="results.products && results.products.length > 0">
                                <div>
                                    <div class="px-4 py-2 bg-slate-50 border-y border-slate-100 text-xs font-black tracking-widest text-slate-400 uppercase">Products</div>
                                    <template x-for="item in results.products" :key="'p'+item.id">
                                        <a :href="'/products?search=' + item.sku" class="flex flex-col px-4 py-3 hover:bg-indigo-50 border-b border-slate-50 transition-colors">
                                            <span class="text-sm font-black text-slate-900" x-text="item.product.name"></span>
                                            <span class="text-xs font-bold text-slate-500" x-text="item.sku + ' • ' + '৳' + item.price"></span>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <!-- Customers -->
                            <template x-if="results.customers && results.customers.length > 0">
                                <div>
                                    <div class="px-4 py-2 bg-slate-50 border-y border-slate-100 text-xs font-black tracking-widest text-slate-400 uppercase">Customers</div>
                                    <template x-for="item in results.customers" :key="'c'+item.id">
                                        <a :href="'/customers/' + item.id + '/ledger'" class="flex flex-col px-4 py-3 hover:bg-emerald-50 border-b border-slate-50 transition-colors">
                                            <span class="text-sm font-black text-emerald-900" x-text="item.name"></span>
                                            <span class="text-xs font-bold text-emerald-600" x-text="item.phone"></span>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <!-- Suppliers -->
                            <template x-if="results.suppliers && results.suppliers.length > 0">
                                <div>
                                    <div class="px-4 py-2 bg-slate-50 border-y border-slate-100 text-xs font-black tracking-widest text-slate-400 uppercase">Suppliers</div>
                                    <template x-for="item in results.suppliers" :key="'s'+item.id">
                                        <a :href="'/suppliers/' + item.id" class="flex flex-col px-4 py-3 hover:bg-rose-50 border-b border-slate-50 transition-colors">
                                            <span class="text-sm font-black text-rose-900" x-text="item.name"></span>
                                            <span class="text-xs font-bold text-rose-600" x-text="item.phone"></span>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!loading && (!results.products || results.products.length === 0) && (!results.customers || results.customers.length === 0) && (!results.suppliers || results.suppliers.length === 0)">
                                <div class="p-6 text-center text-sm font-bold text-slate-400">
                                    No results found for "<span x-text="query"></span>"
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Global Actions -->
                <div class="flex items-center space-x-2">
                    <button @click="$dispatch('open-modal', 'add-customer-global')" 
                            class="flex items-center space-x-1 sm:space-x-2 px-3 sm:px-4 py-2 bg-emerald-500 text-white rounded-xl font-black shadow-lg shadow-emerald-100 hover:bg-emerald-600 transition-all hover:-translate-y-0.5 text-xs sm:text-sm">
                        <svg class="w-4 h-4 sm:w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>New Customer</span>
                    </button>
                    
                    <!-- Notifications -->
                    <div class="relative" x-data="notifications()" x-init="fetchNotifications()">
                        <button @click="open = !open" @click.away="open = false" class="p-2.5 rounded-2xl text-slate-400 hover:text-vibrant-indigo hover:bg-indigo-50 transition-all border border-slate-100 shadow-sm relative">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] font-black text-white ring-2 ring-white"></span>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden z-50">
                            
                            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                                <h3 class="font-black text-slate-800">Notifications</h3>
                                <button x-show="unreadCount > 0" @click="markAllAsRead" class="text-xs font-bold text-vibrant-indigo hover:text-indigo-700 hover:underline">Mark all read</button>
                            </div>
                            
                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="loading">
                                    <div class="p-8 flex justify-center items-center">
                                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                                    </div>
                                </template>
                                
                                <template x-if="!loading && items.length === 0">
                                    <div class="p-8 text-center">
                                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                        </div>
                                        <p class="text-sm font-bold text-slate-500">You're all caught up!</p>
                                    </div>
                                </template>

                                <template x-for="notification in items" :key="notification.id">
                                    <div :class="{'bg-indigo-50/50': !notification.is_read}" class="p-4 border-b border-slate-100 hover:bg-slate-50 transition-colors flex gap-4 cursor-pointer" @click="markAsRead(notification.id)">
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="notification.type === 'alert' ? 'bg-rose-100 text-rose-600' : 'bg-indigo-100 text-vibrant-indigo'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-black text-slate-900 uppercase" x-text="notification.module"></p>
                                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2" x-text="notification.message"></p>
                                            <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-wider" x-text="formatDate(notification.created_at)"></p>
                                        </div>
                                        <div x-show="!notification.is_read" class="w-2 h-2 bg-vibrant-indigo rounded-full mt-2"></div>
                                    </div>
                                </template>
                            </div>
                            
                            <a href="#" class="block text-center p-3 text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 border-t border-slate-100">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center space-x-2 bg-white hover:bg-slate-50 py-1.5 px-3 rounded-2xl border border-slate-200 transition-all shadow-sm group">
                            <div class="w-8 h-8 rounded-xl bg-helsinki-dark flex items-center justify-center text-white font-bold text-xs shadow-md ring-2 ring-white">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="hidden sm:inline text-sm font-black text-slate-700 decoration-vibrant-indigo/30 decoration-2 group-hover:underline">{{ Auth::user()->name }}</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="text-slate-700 font-bold">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();"
                                    class="text-rose-600 font-black">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
