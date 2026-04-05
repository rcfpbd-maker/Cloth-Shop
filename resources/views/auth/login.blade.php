<x-guest-layout>
    <!-- Page Heading -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Welcome Back</h1>
        <p class="text-slate-500 font-medium">Please sign in to your dashboard</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Quick Login Section -->
    <div class="mb-8 p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner group">
        <p class="font-black text-slate-800 mb-4 text-xs uppercase tracking-widest flex items-center">
            <svg class="w-4 h-4 me-2 text-vibrant-indigo animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            Demo Access
        </p>
        <div class="grid grid-cols-2 gap-3">
            <button type="button" onclick="quickFill('owner@shop.com')" class="group/btn relative py-3 px-4 text-xs font-black text-white bg-helsinki-dark rounded-xl hover:shadow-lg transition-all active:scale-95 overflow-hidden">
                <span class="relative z-10 text-rose-500">Owner</span>
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover/btn:opacity-100 transition-opacity"></div>
            </button>
            <button type="button" onclick="quickFill('manager@shop.com')" class="group/btn relative py-3 px-4 text-xs font-black text-white bg-helsinki-dark rounded-xl hover:shadow-lg transition-all active:scale-95 overflow-hidden">
                <span class="relative z-10 text-emerald-500">Manager</span>
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover/btn:opacity-100 transition-opacity"></div>
            </button>
            <button type="button" onclick="quickFill('salesman@shop.com')" class="group/btn relative py-3 px-4 text-xs font-black text-slate-600 bg-white border border-slate-200 rounded-xl hover:border-vibrant-indigo transition-all active:scale-95">
                Salesman
            </button>
            <button type="button" onclick="quickFill('accountant@shop.com')" class="group/btn relative py-3 px-4 text-xs font-black text-slate-600 bg-white border border-slate-200 rounded-xl hover:border-vibrant-indigo transition-all active:scale-95">
                Accountant
            </button>
        </div>
    </div>

    <script>
        function quickFill(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
            const inputs = document.querySelectorAll('input');
            inputs.forEach(i => {
                i.classList.add('ring-2', 'ring-vibrant-indigo', 'border-vibrant-indigo');
                setTimeout(() => i.classList.remove('ring-2', 'ring-vibrant-indigo', 'border-vibrant-indigo'), 600);
            });
        }
    </script>

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" class="font-black text-slate-700 text-xs px-1" />
            <x-text-input id="email" class="block w-full bg-slate-50 border-slate-200 rounded-2xl py-3 focus:ring-vibrant-indigo focus:border-vibrant-indigo shadow-sm" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs font-bold" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <div class="flex justify-between items-center px-1">
                <x-input-label for="password" :value="__('Password')" class="font-black text-slate-700 text-xs" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-bold text-vibrant-indigo hover:underline" href="{{ route('password.request') }}">
                        {{ __('Forgot?') }}
                    </a>
                @endif
            </div>
            <x-text-input id="password" class="block w-full bg-slate-50 border-slate-200 rounded-2xl py-3 focus:ring-vibrant-indigo focus:border-vibrant-indigo shadow-sm"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs font-bold" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center px-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" class="w-5 h-5 rounded-lg border-slate-300 text-vibrant-indigo shadow-sm focus:ring-vibrant-indigo transition-all cursor-pointer" name="remember">
                <span class="ms-3 text-sm font-bold text-slate-500 group-hover:text-slate-700 transition-colors">{{ __('Keep me logged in') }}</span>
            </label>
        </div>

        <div>
            <button class="w-full py-4 bg-vibrant-indigo text-white font-black rounded-2xl shadow-xl shadow-indigo-200 hover:shadow-indigo-300 transition-all active:scale-[0.98] flex items-center justify-center space-x-2">
                <span>{{ __('Sign In') }}</span>
                <svg class="w-5 h-5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </div>
    </form>
</x-guest-layout>
