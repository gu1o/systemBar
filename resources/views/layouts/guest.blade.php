<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'System Bar') }}</title>
        <link rel="icon" href="{{ asset('assets/favicon.png') }}" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=noto-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-6 sm:pt-0 bg-[#002366]">
            <div class="mb-8">
                <a href="/" class="text-white font-bold text-4xl tracking-widest">
                    SYSTEM BAR
                </a>
            </div>

            <div class="w-full sm:max-w-md px-8 py-10 bg-white shadow-2xl overflow-hidden rounded-sm sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
