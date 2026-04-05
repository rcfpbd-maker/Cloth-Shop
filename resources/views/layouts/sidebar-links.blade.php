@props(['isMobile' => false])

<nav class="space-y-2 px-4">
    <!-- Dashboard Item -->
    <a href="{{ route('dashboard') }}" 
       class="{{ request()->routeIs('dashboard') ? 'bg-vibrant-indigo text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} flex items-center p-3 rounded-xl transition-all group">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        <span class="ms-4 font-bold" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition>Dashboard</span>
    </a>

    <!-- Inventory Dropdown -->
    <div v-pre x-data="{ open: {{ request()->is('products*') || request()->is('barcodes*') || request()->is('categories*') || request()->is('purchases*') ? 'true' : 'false' }} }">
        <button @click="open = !open" 
                class="w-full flex items-center p-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all group">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            <span class="ms-4 font-bold flex-1 text-left" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition>Inventory</span>
            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div x-show="open && (!collapsed || {{ $isMobile ? 'true' : 'false' }})" class="mt-2 ms-10 space-y-1 transition-all" x-transition>
            <a href="{{ route('products.index') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('products.index') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Product List</a>
            <a href="{{ route('barcodes.index') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('barcodes*') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Barcodes</a>
            <a href="{{ route('products.index') }}?low_stock=1" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->input('low_stock') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Stock Alerts</a>
            <a href="{{ route('categories.index') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('categories*') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Categories</a>
            <a href="{{ route('suppliers.index') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->is('suppliers*') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Suppliers</a>
            <a href="{{ route('purchases.index') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->is('purchases*') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Purchase Orders</a>
        </div>
    </div>

    <!-- Sales Dropdown -->
    <div v-pre x-data="{ open: {{ request()->is('pos*') || request()->is('sales*') || request()->is('invoices*') ? 'true' : 'false' }} }">
        <button @click="open = !open" 
                class="w-full flex items-center p-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all group">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            <span class="ms-4 font-bold flex-1 text-left" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition>Sales & POS</span>
            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div x-show="open && (!collapsed || {{ $isMobile ? 'true' : 'false' }})" class="mt-2 ms-10 space-y-1 transition-all" x-transition>
            <a href="{{ route('pos.index') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('pos.index') ? 'text-white bg-indigo-600' : 'text-slate-500 hover:text-white font-black' }}">📦 POS System</a>
            <a href="{{ route('sales.index') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('sales.index') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Sale History</a>
            <a href="{{ url('invoices') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->is('invoices*') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Master Invoices</a>
            <a href="{{ route('customers.dashboard') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('customers.dashboard') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Risk Analytics</a>
            <a href="{{ route('customers.index') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('customers.index') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Customer Database</a>
        </div>
    </div>

    <!-- Accounts & Finance -->
    <div v-pre x-data="{ open: {{ request()->is('payments*') || request()->is('expenses*') || request()->is('cashbook*') ? 'true' : 'false' }} }">
        <button @click="open = !open" 
                class="w-full flex items-center p-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all group">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <span class="ms-4 font-bold flex-1 text-left" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition>Finance</span>
            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div x-show="open && (!collapsed || {{ $isMobile ? 'true' : 'false' }})" class="mt-2 ms-10 space-y-1 transition-all" x-transition>
            <a href="{{ route('payments.index') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('payments.index') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Payment Collection</a>
            <a href="{{ route('expenses.index') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('expenses.index') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Shop Expenses</a>
            <a href="{{ url('cashbook') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->is('cashbook*') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Cashbook</a>
            <a href="{{ route('halkhata.index') }}" class="block p-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('halkhata.index') ? 'text-white bg-slate-800' : 'text-slate-500 hover:text-white' }}">Halkhata Reset (Yearly)</a>
        </div>
    </div>

    <!-- Notifications Hub -->
    <a href="#" class="text-slate-400 hover:bg-slate-800 hover:text-white flex items-center p-3 rounded-xl transition-all group lg:hidden">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        <span class="ms-4 font-bold" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition>Notifications</span>
    </a>

    @can('settings.view')
    <div class="pt-4 pb-2">
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-600 px-3" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}">System Administration</p>
    </div>

    <!-- Users -->
    <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.index') ? 'bg-vibrant-indigo text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} flex items-center p-3 rounded-xl transition-all group">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        <span class="ms-4 font-bold" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition>Staff Control</span>
    </a>

    <!-- Maintenance -->
    <a href="{{ route('activity-logs.index') }}" class="{{ request()->routeIs('activity-logs.index') ? 'bg-vibrant-indigo text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} flex items-center p-3 rounded-xl transition-all group">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
        <span class="ms-4 font-bold" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition>Audit Log</span>
    </a>
    
    <a href="{{ route('backups.index') }}" class="{{ request()->routeIs('backups.index') ? 'bg-vibrant-indigo text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} flex items-center p-3 rounded-xl transition-all group">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
        <span class="ms-4 font-bold" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition>Backups</span>
    </a>

    <!-- Settings -->
    <a href="#" class="text-slate-400 hover:bg-slate-800 hover:text-white flex items-center p-3 rounded-xl transition-all group">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        <span class="ms-4 font-bold" x-show="!collapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition>Settings</span>
    </a>
    @endcan
</nav>
