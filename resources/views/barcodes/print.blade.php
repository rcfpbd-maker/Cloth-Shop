<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Barcode Label Print – ClothERP</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet"/>

    {{-- JsBarcode for rendering SVG barcodes --}}
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css'])

    <style>
        /* ── Screen Styles ── */
        body { font-family: 'Figtree', sans-serif; background: #f1f5f9; }
        .label-card {
            width: 85mm; height: 55mm;
            display: inline-flex; flex-direction: column; align-items: center; justify-content: center;
            border: 1.5px solid #e2e8f0; border-radius: 6px;
            background: white; padding: 6px; margin: 4px;
            page-break-inside: avoid; break-inside: avoid;
            text-align: center; overflow: hidden;
        }
        .label-card.thermal {
            width: 58mm; height: 40mm;
        }
        .label-card.small {
            width: 50mm; height: 30mm;
        }

        /* ── Print Styles ── */
        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea {
                position: fixed; top: 0; left: 0; width: 100%;
                background: white; padding: 5mm;
            }
            .label-card { border: 1px solid #333 !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div x-data="barcodePrint()" x-init="fetchVariants()" class="min-h-screen">

    {{-- ── No-Print Control Panel ── --}}
    <div class="no-print sticky top-0 bg-slate-900 text-white z-50 shadow-xl print:hidden">
        <div class="max-w-screen-xl mx-auto px-6 py-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('barcodes.index') }}" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="font-black text-lg">Barcode Label Printer</h1>
                    <p class="text-slate-400 text-xs font-medium">Select variants and configure your label format before printing.</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 items-center">
                {{-- Label Size --}}
                <div class="flex bg-slate-800 p-1 rounded-xl gap-1">
                    <button @click="labelSize = 'standard'" :class="labelSize === 'standard' ? 'bg-white text-slate-900' : 'text-slate-400 hover:text-white'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">A4 Standard</button>
                    <button @click="labelSize = 'thermal'" :class="labelSize === 'thermal' ? 'bg-white text-slate-900' : 'text-slate-400 hover:text-white'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">58mm Thermal</button>
                    <button @click="labelSize = 'small'" :class="labelSize === 'small' ? 'bg-white text-slate-900' : 'text-slate-400 hover:text-white'"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">Small</button>
                </div>

                {{-- Qty per label --}}
                <div class="flex items-center gap-2">
                    <label class="text-slate-400 text-xs font-bold">Copies:</label>
                    <input x-model="copies" type="number" min="1" max="100"
                           class="w-16 px-2 py-1.5 bg-slate-800 border border-slate-600 text-white text-xs rounded-lg text-center focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                </div>

                {{-- Select All --}}
                <button @click="selectAll()" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-xl transition-all">
                    <span x-text="selectedIds.length === variants.length ? 'Deselect All' : 'Select All'"></span>
                </button>

                {{-- Print Button --}}
                <button @click="printLabels()"
                        :disabled="selectedIds.length === 0"
                        class="px-5 py-2.5 bg-vibrant-indigo text-white font-bold rounded-xl hover:bg-indigo-700 transition-all disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print <span x-show="selectedIds.length > 0" class="bg-white/20 px-1.5 py-0.5 rounded-lg text-xs" x-text="selectedIds.length + ' labels'"></span>
                </button>
            </div>
        </div>

        {{-- Search bar --}}
        <div class="border-t border-slate-800 px-6 py-3">
            <div class="relative max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input x-model="search" @input.debounce.400ms="fetchVariants()" type="text"
                       placeholder="Filter by product or SKU..."
                       class="pl-9 pr-4 py-2 w-full bg-slate-800 border border-slate-700 text-white text-sm placeholder-slate-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
        </div>
    </div>

    {{-- ══ MAIN LAYOUT ══ --}}
    <div class="max-w-screen-xl mx-auto px-4 py-6 flex gap-6">

        {{-- Selection Panel (left) --}}
        <div class="no-print w-72 flex-shrink-0">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-black text-xs uppercase tracking-wider text-slate-700">Select Variants</h3>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">
                        <span x-text="selectedIds.length"></span>/<span x-text="variants.length"></span>
                    </span>
                </div>

                {{-- Loading --}}
                <div x-show="loading" class="flex justify-center py-10">
                    <div class="w-8 h-8 border-4 border-vibrant-indigo border-t-transparent rounded-full animate-spin"></div>
                </div>

                <div x-show="!loading" class="divide-y divide-slate-50 max-h-[calc(100vh-220px)] overflow-y-auto">
                    <template x-for="variant in variants" :key="variant.id">
                        <label class="flex items-start gap-3 px-4 py-3 cursor-pointer hover:bg-indigo-50 transition-colors"
                               :class="selectedIds.includes(variant.id) ? 'bg-indigo-50' : ''">
                            <input type="checkbox" :value="variant.id"
                                   @change="toggleSelect(variant.id)"
                                   :checked="selectedIds.includes(variant.id)"
                                   class="mt-0.5 w-4 h-4 rounded border-slate-300 text-vibrant-indigo focus:ring-vibrant-indigo flex-shrink-0"/>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-900 truncate" x-text="variant.product?.name ?? '—'"></p>
                                <p class="text-[10px] font-medium text-slate-400 font-mono" x-text="variant.sku"></p>
                                <div class="flex gap-1 mt-0.5">
                                    <span x-show="variant.size" class="text-[9px] font-bold px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded" x-text="variant.size"></span>
                                    <span x-show="variant.color" class="text-[9px] font-bold px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded" x-text="variant.color"></span>
                                </div>
                            </div>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        {{-- Print Preview (right) --}}
        <div class="flex-1">
            <div class="no-print mb-4 flex items-center justify-between">
                <h2 class="text-sm font-black text-slate-700 uppercase tracking-wider">Print Preview</h2>
                <p class="text-xs text-slate-400 font-medium" x-text="totalLabels + ' label(s) will be printed'"></p>
            </div>

            {{-- Empty preview state --}}
            <div x-show="selectedIds.length === 0" class="no-print flex flex-col items-center justify-center py-20 bg-white rounded-2xl border-2 border-dashed border-slate-200">
                <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <p class="text-slate-400 font-bold">Select variants from the left panel to preview labels.</p>
            </div>

            {{-- Label print area --}}
            <div id="printArea" x-show="selectedIds.length > 0" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
                <template x-for="varId in labelQueue" :key="varId + '_label'">
                    <div class="label-card" :class="labelSize !== 'standard' ? labelSize : ''"
                         :id="'label_' + varId">
                        <template x-if="getVariant(varId)">
                            <div class="w-full h-full flex flex-col items-center justify-between py-1">
                                <p class="text-[9px] font-black text-slate-900 text-center leading-tight truncate w-full px-1"
                                   x-text="getVariant(varId)?.product?.name ?? ''"></p>
                                <div class="flex gap-1 justify-center">
                                    <span x-show="getVariant(varId)?.size" class="text-[8px] font-bold text-slate-600"
                                          x-text="getVariant(varId)?.size"></span>
                                    <span x-show="getVariant(varId)?.size && getVariant(varId)?.color" class="text-[8px] text-slate-400">|</span>
                                    <span x-show="getVariant(varId)?.color" class="text-[8px] font-bold text-slate-600"
                                          x-text="getVariant(varId)?.color"></span>
                                </div>
                                <svg :id="'barsvg_' + varId"
                                     x-init="$nextTick(() => renderBarcode(varId))"
                                     class="max-w-full" style="max-height: 28mm;"></svg>
                                <p class="text-[8px] font-mono text-slate-500 tracking-widest"
                                   x-text="getVariant(varId)?.sku ?? ''"></p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function barcodePrint() {
    return {
        variants: [],
        selectedIds: [],
        loading: false,
        search: '',
        labelSize: 'standard',
        copies: 1,

        get totalLabels() { return this.selectedIds.length * parseInt(this.copies || 1); },
        get labelQueue() {
            const queue = [];
            const qty = parseInt(this.copies || 1);
            for (const id of this.selectedIds) {
                for (let i = 0; i < qty; i++) queue.push(id);
            }
            return queue;
        },

        async fetchVariants() {
            this.loading = true;
            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            params.set('page', 1);

            // If a specific variant is pre-selected via query param
            const urlVar = new URLSearchParams(window.location.search).get('variant');

            try {
                const res = await fetch(`/api/barcodes?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.variants = data.data ?? [];
                if (urlVar) {
                    const id = parseInt(urlVar);
                    if (!this.selectedIds.includes(id)) this.selectedIds.push(id);
                }
            } finally {
                this.loading = false;
            }
        },

        getVariant(id) {
            return this.variants.find(v => v.id === id);
        },

        renderBarcode(varId) {
            const variant = this.getVariant(varId);
            if (!variant) return;
            const code = variant.barcode || variant.sku || 'N/A';
            const el = document.getElementById('barsvg_' + varId);
            if (!el) return;
            try {
                JsBarcode(el, code, {
                    format: 'CODE128',
                    width: 1.5,
                    height: this.labelSize === 'small' ? 30 : 45,
                    displayValue: true,
                    fontSize: 9,
                    margin: 2,
                    background: '#ffffff',
                    lineColor: '#000000'
                });
            } catch (e) {
                // If barcode is invalid just show a fallback
                el.innerHTML = `<text x="50%" y="50%" text-anchor="middle" font-size="8" fill="#999">${code}</text>`;
            }
        },

        toggleSelect(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx === -1) this.selectedIds.push(id);
            else this.selectedIds.splice(idx, 1);
        },

        selectAll() {
            if (this.selectedIds.length === this.variants.length) {
                this.selectedIds = [];
            } else {
                this.selectedIds = this.variants.map(v => v.id);
            }
        },

        printLabels() {
            window.print();
        }
    };
}
</script>
</body>
</html>
