<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <link
        rel="apple-touch-icon"
        sizes="180x180"
        href="{{ asset('icons/SwineLocateLogo-180.png') }}"
    >

    <meta name="theme-color" content="#3368A0">

    <meta name="mobile-web-app-capable" content="yes">

    <meta name="apple-mobile-web-app-capable" content="yes">

    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <meta name="apple-mobile-web-app-title" content="SwineLocate">

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

    {{-- =========================================================
        GLOBAL PAGE LOADING OVERLAY
    ========================================================== --}}
    <div
        id="page-loader"
        class="fixed inset-0 z-[9999] hidden items-center justify-center bg-white/90 backdrop-blur-sm"
    >
        <div class="flex flex-col items-center justify-center px-6 text-center">

            {{-- Loading Spinner --}}
            <div class="relative h-12 w-12">

                <div
                    class="absolute inset-0 rounded-full border-4 border-gray-200"
                ></div>

                <div
                    class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-[#3368A0]"
                ></div>

            </div>

            {{-- Loading Text --}}
            <p
                id="page-loader-text"
                class="mt-4 text-sm font-semibold text-gray-700"
            >
                Loading...
            </p>

            <p class="mt-1 text-xs text-gray-500">
                Please wait
            </p>

        </div>
    </div>


    <div class="min-h-screen">

        {{-- Desktop Sidebar --}}
        @include('components.sidebar')


        {{-- Main Area --}}
        <div class="lg:pl-64">

            {{-- Existing top navigation --}}
            <!-- @include('layouts.navigation') -->


            {{-- Page Heading --}}
            @isset($header)

                <header class="bg-white border-b border-gray-200">

                    <div class="px-4 py-6 sm:px-6 lg:px-8 mt-11 lg:mt-0 pt-11 lg:pt-6">

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


    {{-- =========================================================
        PAGE LOADING SCRIPT
    ========================================================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const loader = document.getElementById('page-loader');
            const loaderText = document.getElementById('page-loader-text');

            if (!loader) {
                return;
            }


            /*
             * Show loading overlay
             */
            function showPageLoader(text = 'Loading...') {

                if (loaderText) {
                    loaderText.textContent = text;
                }

                loader.classList.remove('hidden');
                loader.classList.add('flex');
            }


            /*
             * Hide loading overlay
             */
            function hidePageLoader() {

                loader.classList.add('hidden');
                loader.classList.remove('flex');
            }


            /*
             * Make functions available globally.
             * Other JavaScript files can also use them.
             */
            window.showPageLoader = showPageLoader;
            window.hidePageLoader = hidePageLoader;


            /*
             * Hide loader when the current page has finished loading.
             */
            window.addEventListener('load', function () {
                hidePageLoader();
            });


            /*
             * Show loader when navigating to another page.
             */
            document.addEventListener('click', function (event) {

                const link = event.target.closest('a');

                if (!link) {
                    return;
                }


                /*
                 * Ignore links that should not trigger
                 * a page loading screen.
                 */
                if (
                    link.target === '_blank' ||
                    link.hasAttribute('download') ||
                    link.href.startsWith('javascript:') ||
                    link.href.startsWith('#') ||
                    link.getAttribute('href') === '#' ||
                    link.closest('[data-no-page-loader]') ||
                    event.ctrlKey ||
                    event.metaKey ||
                    event.shiftKey ||
                    event.altKey
                ) {
                    return;
                }


                const url = new URL(link.href, window.location.href);


                /*
                 * Only show the loader for pages
                 * within the SwineLocate application.
                 */
                if (url.origin !== window.location.origin) {
                    return;
                }


                /*
                 * Ignore the current page.
                 */
                if (
                    url.pathname === window.location.pathname &&
                    url.search === window.location.search
                ) {
                    return;
                }


                /*
                 * Get link text for a more useful loading message.
                 */
                let linkText = link.textContent
                    .replace(/\s+/g, ' ')
                    .trim();


                if (linkText.length > 40) {
                    linkText = linkText.substring(0, 40) + '...';
                }


                showPageLoader(
                    linkText
                        ? `Loading ${linkText}...`
                        : 'Loading...'
                );

            });


            /*
             * Browser back/forward navigation.
             */
            window.addEventListener('pageshow', function () {
                hidePageLoader();
            });


            /*
             * Show loader immediately before the browser
             * leaves the current page.
             */
            window.addEventListener('beforeunload', function () {

                /*
                 * Do not show loader if the page is being
                 * restored from browser history.
                 */
                if (!document.visibilityState) {
                    return;
                }

                showPageLoader('Loading...');

            });


            /*
             * Make the initial page loader hidden.
             */
            hidePageLoader();

        });
    </script>


    {{-- =========================================================
        SERVICE WORKER
    ========================================================== --}}
    <script>
        if ('serviceWorker' in navigator) {

            window.addEventListener('load', function () {

                navigator.serviceWorker.register('/sw.js')

                    .then(function (registration) {

                        console.log(
                            'SwineLocate Service Worker registered:',
                            registration.scope
                        );

                    })

                    .catch(function (error) {

                        console.error(
                            'SwineLocate Service Worker registration failed:',
                            error
                        );

                    });

            });

        }
    </script>


    @stack('scripts')

</body>

</html>