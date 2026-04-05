<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Staff Management</h2>
    </x-slot>

    <div class="py-6 sm:py-10 bg-slate-50 min-h-screen">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-900">Staff Control</h1>
                <p class="text-slate-500 font-medium mt-1">Manage store employees and their access levels.</p>
            </div>

            {{-- Staff List --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($users as $user)
                    <div class="card-glass p-6 flex flex-col gap-4 group hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-vibrant-indigo flex items-center justify-center text-white text-xl font-black shadow-lg shadow-indigo-100">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-black text-slate-900 truncate">{{ $user->name }}</h3>
                                <p class="text-xs font-bold text-slate-400">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-4 flex flex-col gap-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-slate-500">Current Role</span>
                                <span class="px-2 py-1 bg-white rounded-lg font-black text-vibrant-indigo shadow-sm ring-1 ring-slate-100 italic">{{ $user->role->name ?? 'No Role' }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-slate-500">Last Login</span>
                                <span class="font-black text-slate-700">{{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->diffForHumans() : 'Never' }}</span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button @click="$dispatch('open-role-modal', {id: {{ $user->id }}, name: '{{ $user->name }}', role_id: {{ $user->role_id ?? 'null' }}})" 
                                    class="flex-1 py-3 bg-white font-black text-xs text-slate-600 rounded-xl border border-slate-100 hover:border-vibrant-indigo hover:text-vibrant-indigo transition-all shadow-sm">
                                Change Role
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        {{-- Role Modal (Simplified) --}}
        <div x-data="{ open: false, user: {} }" 
             @open-role-modal.window="open = true; user = $event.detail"
             x-show="open" 
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            
            <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-10 ring-1 ring-white/50">
                <h2 class="text-2xl font-black text-slate-900 mb-2">Assign Role</h2>
                <p class="text-slate-400 font-bold text-sm mb-8" x-text="'Choose a new role for ' + user.name"></p>

                <form action="#" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($roles as $role)
                            <label class="cursor-pointer">
                                <input type="radio" name="role_id" value="{{ $role->id }}" 
                                       :checked="user.role_id == {{ $role->id }}"
                                       class="hidden peer">
                                <div class="p-5 rounded-2xl border-2 border-slate-50 font-bold transition-all hover:bg-slate-50 peer-checked:border-vibrant-indigo peer-checked:bg-indigo-50 peer-checked:text-vibrant-indigo flex flex-col">
                                    <span class="text-sm font-black">{{ $role->name }}</span>
                                    <span class="text-[10px] opacity-60 font-bold uppercase tracking-widest">{{ $role->description }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="flex gap-4 pt-6">
                        <button type="button" @click="open = false" class="flex-1 py-4 bg-slate-100 font-black text-slate-500 rounded-2xl">Cancel</button>
                        <button type="submit" class="flex-1 py-4 bg-vibrant-indigo text-white font-black rounded-2xl shadow-xl shadow-indigo-100">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
