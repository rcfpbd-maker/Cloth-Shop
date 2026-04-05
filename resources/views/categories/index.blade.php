<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Category management</h2>
    </x-slot>

    <div class="py-6 sm:py-10 bg-slate-50 min-h-screen"
         x-data="categoryComp({{ json_encode($categories->items() ?? []) }})"
         x-init="initData()">

        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900">Categories</h1>
                    <p class="text-slate-500 font-medium mt-1">Organize your products with categories and subcategories.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="openAddModal()"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-vibrant-indigo text-white font-bold rounded-2xl shadow-lg hover:bg-indigo-700 transition-all hover:-translate-y-0.5 active:translate-y-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Category
                    </button>
                </div>
            </div>

            {{-- Table View --}}
            <div class="card-glass overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/50 bg-slate-50/50">
                            <th class="px-6 py-4 text-xs font-black uppercase text-slate-500 tracking-wider">ID</th>
                            <th class="px-6 py-4 text-xs font-black uppercase text-slate-500 tracking-wider">Name</th>
                            <th class="px-6 py-4 text-xs font-black uppercase text-slate-500 tracking-wider">Parent Category</th>
                            <th class="px-6 py-4 text-xs font-black uppercase text-slate-500 tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-black uppercase text-slate-500 tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/30">
                        <template x-for="item in list" :key="item.id">
                            <tr class="hover:bg-indigo-50/30 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-400 text-sm" x-text="item.id"></td>
                                <td class="px-6 py-4">
                                    <span class="font-black text-slate-900" x-text="item.name"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span x-show="item.parent" class="px-2 py-1 bg-slate-100 rounded-lg text-xs font-bold text-slate-600" x-text="item.parent ? item.parent.name : '—'"></span>
                                    <span x-show="!item.parent" class="text-slate-300 italic text-xs">None</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="item.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'"
                                          class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"
                                          x-text="item.status"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEditModal(item)" class="p-2 text-indigo-600 hover:bg-white rounded-xl transition-all shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <form :action="'/categories/' + item.id" method="POST" class="inline" @submit.prevent="deleteCategory(item)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-rose-600 hover:bg-white rounded-xl transition-all shadow-sm hover:shadow-md">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div x-show="list.length === 0" class="py-12 text-center">
                    <p class="text-slate-400 font-bold">No categories found. Start by adding one!</p>
                </div>
            </div>
            
            <div class="mt-6">
                {{ $categories->links() }}
            </div>
        </div>

        {{-- Add/Edit Modal --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             @click.self="showModal = false"
             style="display: none;">
            
            <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden ring-1 ring-white/50">
                <div class="p-8">
                    <h2 class="text-2xl font-black text-slate-900 mb-6" x-text="editingItem ? 'Edit Category' : 'Create New Category'"></h2>
                    
                    <form :action="editingItem ? '/categories/' + editingItem.id : '/categories'" method="POST" class="space-y-6">
                        @csrf
                        <template x-if="editingItem">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="space-y-2">
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest ml-1">Category Name</label>
                            <input type="text" name="name" x-model="form.name" required
                                   class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-700 focus:ring-2 focus:ring-vibrant-indigo transition-all"
                                   placeholder="e.g. T-Shirts">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest ml-1">Parent Category</label>
                            <select name="parent_id" x-model="form.parent_id"
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-700 focus:ring-2 focus:ring-vibrant-indigo transition-all">
                                <option value="">None (Main Category)</option>
                                @foreach($categories as $cat)
                                    @if(!isset($category) || $cat->id != $category->id)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest ml-1">Status</label>
                            <div class="flex gap-4">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="status" value="active" x-model="form.status" class="hidden peer">
                                    <div class="p-4 rounded-2xl border-2 border-slate-100 peer-checked:border-vibrant-indigo peer-checked:bg-indigo-50 font-bold text-center transition-all bg-white text-slate-500 peer-checked:text-vibrant-indigo">
                                        Active
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="status" value="inactive" x-model="form.status" class="hidden peer">
                                    <div class="p-4 rounded-2xl border-2 border-slate-100 peer-checked:border-slate-500 peer-checked:bg-slate-50 font-bold text-center transition-all bg-white text-slate-500 peer-checked:text-slate-700">
                                        Inactive
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="showModal = false"
                                    class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl hover:bg-slate-200 transition-all">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="flex-1 px-6 py-4 bg-vibrant-indigo text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function categoryComp(initialList) {
        return {
            list: initialList,
            showModal: false,
            editingItem: null,
            form: {
                name: '',
                parent_id: '',
                status: 'active'
            },

            initData() {
                // Initialize if needed
            },

            openAddModal() {
                this.editingItem = null;
                this.form = { name: '', parent_id: '', status: 'active' };
                this.showModal = true;
            },

            openEditModal(item) {
                this.editingItem = item;
                this.form = {
                    name: item.name,
                    parent_id: item.parent_id || '',
                    status: item.status
                };
                this.showModal = true;
            },

            deleteCategory(item) {
                if(confirm('Are you sure you want to delete this category?')) {
                    event.target.submit();
                }
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
