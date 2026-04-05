<!-- Sidebar (Desktop) -->
<aside v-pre x-data="{ collapsed: false }" 
       :class="collapsed ? 'w-20' : 'w-72'"
       class="hidden lg:flex flex-col h-screen bg-helsinki-dark border-r border-slate-800 transition-all duration-300 sticky top-0">
    
    <!-- Sidebar Header -->
    <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800">
        <div class="flex items-center space-x-3 overflow-hidden" x-show="!collapsed" x-transition>
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg">
                <x-application-logo class="w-8 h-8 text-vibrant-indigo" />
            </div>
            <span class="text-xl font-black text-white tracking-tight whitespace-nowrap">ClothERP</span>
        </div>
        <button @click="collapsed = !collapsed" class="p-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
            <svg x-show="!collapsed" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
            <svg x-show="collapsed" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <div class="flex-1 overflow-y-auto py-6">
        @include('layouts.sidebar-links', ['isMobile' => false])
    </div>

    <!-- Sidebar Footer -->
    <div class="p-6 border-t border-slate-800">
        <div class="flex items-center space-x-4 bg-slate-900 p-4 rounded-2xl ring-1 ring-slate-800 group hover:ring-vibrant-indigo transition-all cursor-pointer">
            <div class="w-10 h-10 rounded-xl bg-vibrant-indigo flex items-center justify-center text-white font-bold ring-2 ring-slate-900">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div x-show="!collapsed" x-transition class="overflow-hidden">
                <p class="text-sm font-black text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">{{ Auth::user()->role->name ?? 'User' }}</p>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Drawer -->
<div v-pre x-data="{ mobileOpen: false }" 
     @open-mobile-sidebar.window="mobileOpen = true"
     class="lg:hidden">
    
    <!-- Backdrop -->
    <div x-show="mobileOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileOpen = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60]"></div>

    <!-- Drawer -->
    <div x-show="mobileOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 w-80 bg-helsinki-dark shadow-2xl z-[70] flex flex-col">
        
        <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg">
                    <x-application-logo class="w-8 h-8 text-vibrant-indigo" />
                </div>
                <span class="text-xl font-black text-white">ClothERP</span>
            </div>
            <button @click="mobileOpen = false" class="p-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto py-6">
            @include('layouts.sidebar-links', ['isMobile' => true])
        </div>

        <!-- Mobile User Info -->
        <div class="p-6 border-t border-slate-800">
            <div class="flex items-center space-x-4 bg-slate-900 p-4 rounded-2xl">
                <div class="w-10 h-10 rounded-xl bg-vibrant-indigo flex items-center justify-center text-white font-bold ring-2 ring-slate-900">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-black text-white">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] font-bold text-slate-500 uppercase">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
