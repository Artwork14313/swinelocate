<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ config('app.name', 'SwineLocate') }}
    </title>

    <link rel="preconnect" href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
        rel="stylesheet"
    />

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>


<body class="font-sans antialiased bg-gray-100">

    <div class="min-h-screen">

        {{-- Desktop Sidebar --}}
        @include('components.sidebar')


        {{-- Main Area --}}
        <div class="lg:pl-64">

            {{-- Existing top navigation --}}
            @include('layouts.navigation')


            {{-- Page Heading --}}
            @isset($header)

                <header class="bg-white border-b border-gray-200">

                    <div class="px-4 py-6 sm:px-6 lg:px-8">

                        {{ $header }}

                    </div>

                </header>

            @endisset


            {{-- Page Content --}}
            <main>

                {{ $slot }}

            </main>

        </div>

    </div>
@stack('scripts')
</body>

</html>