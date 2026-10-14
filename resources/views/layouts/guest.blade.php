<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#3368A0">

    <title>{{ config('app.name', 'SwineLocate') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
        rel="stylesheet"
    />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">

    @if ($showNavigation && auth()->check())
        @include('components.sidebar')
        <div class="pt-16 lg:pl-64 lg:pt-0">
            <main class="min-h-screen">
                {{ $slot }}
            </main>
        </div>
    @else
        <main class="min-h-screen">
            {{ $slot }}
        </main>
    @endif

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js')
                    .then(function (registration) {
                        console.log('SwineLocate Service Worker registered:', registration.scope);
                    })
                    .catch(function (error) {
                        console.error('SwineLocate Service Worker registration failed:', error);
                    });
            });
        }
    </script>

</body>

</html>