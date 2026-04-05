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
    <body class="font-sans text-slate-900 antialiased selection:bg-vibrant-indigo selection:text-white">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-helsinki-dark px-4">
            <div class="mb-8">
                <a href="/">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-2xl ring-4 ring-slate-800 transition-transform hover:scale-110 duration-300">
                        <x-application-logo class="w-10 h-10 text-vibrant-indigo" />
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md bg-white p-8 sm:p-10 shadow-2xl rounded-3xl border border-slate-700/50 backdrop-blur-xl relative overflow-hidden">
                <!-- Decorative element -->
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-vibrant-indigo/5 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
