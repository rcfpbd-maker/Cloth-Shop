<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10 bg-slate-50 min-h-screen">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Dashboard Header -->
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 leading-tight">Business Overview</h1>
                    <p class="text-slate-500 font-medium">Monitoring real-time performance across all modules.</p>
                </div>
                <div class="flex flex-col items-end space-y-4">
                    <example-component></example-component>
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-white text-slate-700 shadow-sm border border-slate-200">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full me-2 animate-pulse"></span>
                        Live System
                    </span>
                    <button class="btn-vibrant">
                        Generate Report
                    </button>
                </div>
            </div>

            <!-- Top Row Widgets -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Today Sales -->
                <div class="card-glass p-6 group hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-vibrant-indigo rounded-2xl shadow-lg shadow-indigo-200">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">+12% vs yest.</span>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Today Sales</p>
                        <h3 class="text-2xl font-black text-slate-900">$12,450.00</h3>
                    </div>
                </div>

                <!-- Profit -->
                <div class="card-glass p-6 group hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-success-emerald rounded-2xl shadow-lg shadow-emerald-200">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">+8% growth</span>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Profit</p>
                        <h3 class="text-2xl font-black text-slate-900">$3,820.50</h3>
                    </div>
                </div>

                <!-- Cash in Hand -->
                <div class="card-glass p-6 group hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-helsinki-dark rounded-2xl shadow-lg shadow-slate-300 text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Cash in Hand</p>
                        <h3 class="text-2xl font-black text-slate-900">$45,000.00</h3>
                    </div>
                </div>

                <!-- High Stock Alert -->
                <div class="card-glass p-6 group hover:-translate-y-1 transition-all duration-300 bg-rose-50 border-rose-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-rose-500 rounded-2xl shadow-lg shadow-rose-200">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-rose-600 bg-rose-100 px-2 py-1 rounded-full">Action Needed</span>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-rose-500 uppercase tracking-wider">Low Stock Alert</p>
                        <h3 class="text-2xl font-black text-rose-900">12 Items</h3>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables Area -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Sales Chart -->
                <div class="xl:col-span-2 card-glass p-8">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h3 class="text-xl font-black text-slate-900">Sales Analytics</h3>
                            <p class="text-slate-400 text-sm font-medium">Monthly revenue vs forecast</p>
                        </div>
                        <div class="flex bg-slate-100 p-1 rounded-xl">
                            <button class="px-4 py-1.5 rounded-lg text-sm font-bold bg-white shadow-sm text-vibrant-indigo">Week</button>
                            <button class="px-4 py-1.5 rounded-lg text-sm font-bold text-slate-500 hover:text-slate-700 transition-all">Month</button>
                        </div>
                    </div>
                    <div class="h-80 w-full bg-slate-50 rounded-2xl flex items-center justify-center border border-dashed border-slate-200">
                        <p class="text-slate-400 font-medium">Chart.js Implementation Ready</p>
                    </div>
                </div>

                <!-- Top 5 Products -->
                <div class="card-glass p-8">
                    <h3 class="text-xl font-black text-slate-900 mb-6">Top 5 Products</h3>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between p-3 rounded-2xl hover:bg-indigo-50 transition-all group cursor-pointer">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center font-black text-indigo-600 group-hover:bg-vibrant-indigo group-hover:text-white transition-all">01</div>
                                <div>
                                    <p class="text-sm font-black text-slate-900">Premium Denim Jeans</p>
                                    <p class="text-xs font-medium text-slate-400">Men • XL Blue</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-slate-900">124 Sales</p>
                                <p class="text-xs font-bold text-emerald-500">$4,520</p>
                            </div>
                        </div>
                        <!-- More product items... -->
                        <div class="flex items-center justify-between p-3 rounded-2xl hover:bg-indigo-50 transition-all group cursor-pointer">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center font-black text-indigo-600 group-hover:bg-vibrant-indigo group-hover:text-white transition-all">02</div>
                                <div>
                                    <p class="text-sm font-black text-slate-900">Polo Sport Shirt</p>
                                    <p class="text-xs font-medium text-slate-400">Uni • L Red</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-slate-900">98 Sales</p>
                                <p class="text-xs font-bold text-emerald-500">$2,140</p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('products.index') }}" class="block w-full text-center mt-8 py-3 rounded-xl border-2 border-dashed border-slate-200 text-slate-400 font-black hover:border-vibrant-indigo hover:text-vibrant-indigo transition-all">View All Products</a>
                </div>
            </div>

            <!-- Bottom Row Advanced Widgets -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
                <!-- Today Expense -->
                <div class="card-glass p-6 border-l-4 border-rose-500">
                    <p class="text-xs font-black text-rose-500 uppercase tracking-widest mb-1">Today Expense</p>
                    <h4 class="text-xl font-black text-slate-800">$1,200.00</h4>
                </div>
                <!-- Today Purchase -->
                <div class="card-glass p-6 border-l-4 border-indigo-500">
                    <p class="text-xs font-black text-indigo-500 uppercase tracking-widest mb-1">Today Purchase</p>
                    <h4 class="text-xl font-black text-slate-800">$5,400.00</h4>
                </div>
                <!-- Total Receivable -->
                <div class="card-glass p-6 border-l-4 border-emerald-500">
                    <p class="text-xs font-black text-emerald-500 uppercase tracking-widest mb-1">Total Receivable</p>
                    <h4 class="text-xl font-black text-slate-800">$18,250.00</h4>
                </div>
                <!-- Total Payable -->
                <div class="card-glass p-6 border-l-4 border-amber-500">
                    <p class="text-xs font-black text-amber-500 uppercase tracking-widest mb-1">Total Payable</p>
                    <h4 class="text-xl font-black text-slate-800">$9,120.00</h4>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
