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
                <div class="relative group">
                    <input type="text" placeholder="Search..." class="bg-slate-50 text-slate-600 rounded-2xl pl-10 pr-4 py-2 border-slate-200 focus:ring-2 focus:ring-vibrant-indigo w-40 sm:w-64 transition-all focus:w-80 shadow-inner" />
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-slate-400 group-focus-within:text-vibrant-indigo" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <!-- Notifications -->
                <div class="relative">
                    <button class="p-2.5 rounded-2xl text-slate-400 hover:text-vibrant-indigo hover:bg-indigo-50 transition-all border border-slate-100 shadow-sm relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="absolute top-2 right-2 w-2.5 h-2.5 bg-vibrant-indigo rounded-full border-2 border-white animate-bounce"></span>
                    </button>
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
