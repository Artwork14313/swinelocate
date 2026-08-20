<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'SwineLocate') }}
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
        rel="stylesheet"
    />

    <!-- Scripts -->
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="font-sans antialiased text-gray-900">

    <div class="min-h-screen bg-gray-100">

        {{-- Navigation --}}
        @include('layouts.navigation')


        {{-- Page Header --}}
        @isset($header)

            <header class="border-b border-gray-200 bg-white">

                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

                    {{ $header }}

                </div>

            </header>

        @endisset


        {{-- Main Content --}}
        <main>

            {{ $slot }}

        </main>

    </div>

</body>

</html>