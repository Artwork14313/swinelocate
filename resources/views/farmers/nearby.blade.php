<x-guest-layout :show-navigation="!$embedded">
    <div class="min-h-screen bg-gray-100 px-4 py-8 sm:px-6 lg:px-8">
        <main class="mx-auto max-w-6xl space-y-6">
            <header class="flex flex-col gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">SwineLocate Marketplace</p>
                    <h1 class="mt-2 text-3xl font-bold text-gray-900">Farmers near Sagay City</h1>
                    <p class="mt-2 text-gray-600">
                        Explore farmers in Sagay City, Negros Occidental with active swine listings. Select a map marker to view the farmer profile.
                    </p>
                </div>
                @unless ($embedded)
                    <div class="flex shrink-0 flex-wrap gap-3">
                        <a href="{{ route('android-app') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-indigo-200 px-4 py-2.5 text-sm font-semibold text-indigo-800 hover:bg-indigo-50">
                            Get the Android app
                        </a>
                        @guest
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-800">
                                Login
                            </a>
                        @endguest
                    </div>
                @endunless
            </header>

            <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div id="farmer-map" class="h-[60vh] min-h-[360px] w-full" aria-label="Map of farmers in Sagay City"></div>
                <p class="border-t border-gray-100 px-5 py-3 text-xs text-gray-500">
                    Showing {{ $mapFarmers->count() }} farmer pin{{ $mapFarmers->count() === 1 ? '' : 's' }} using each farm’s saved latitude and longitude. Map data © OpenStreetMap contributors.
                </p>
            </section>

            @if ($farmers->isEmpty())
                <section class="rounded-xl bg-white px-6 py-10 text-center shadow-sm ring-1 ring-gray-200">
                    <p class="font-medium text-gray-900">No farmers with active sale listings were found in Sagay City.</p>
                </section>
            @else
                @if ($mapFarmers->isEmpty())
                    <section class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                        Farmer listings are available, but farm coordinates have not been set, so they cannot appear as map markers yet.
                    </section>
                @endif

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold text-gray-900">Farmers with available swine</h2>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($farmers as $farmer)
                            <a href="{{ route('farmers.public-profile', $farmer->qr_token) }}"
                                class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-indigo-300">
                                <h3 class="font-semibold text-gray-900">{{ $farmer->name }}</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ $farmer->farm->name }}</p>
                                <p class="mt-2 text-sm text-gray-500">{{ $farmer->swine->count() }} active listing{{ $farmer->swine->count() === 1 ? '' : 's' }}</p>
                                <span class="mt-3 inline-flex text-sm font-semibold text-indigo-700">View SwineLocate Farmer Profile →</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="text-center text-xs text-gray-500">
                Farm locations are approximate. Confirm availability and details directly with the farmer.
            </footer>
        </main>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const farmers = @js($mapFarmers);
        const map = L.map('farmer-map', {
            scrollWheelZoom: false,
            minZoom: 10,
            maxZoom: 18
        }).setView([10.8976, 123.4145], 11);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[character]);

        const markers = farmers.map((farmer) => L.marker([farmer.latitude, farmer.longitude])
            .addTo(map)
            .bindPopup(
                `<strong>${escapeHtml(farmer.name)}</strong><br>${escapeHtml(farmer.farm)}<br>` +
                `${farmer.listing_count} active listing${farmer.listing_count === 1 ? '' : 's'}<br>` +
                `<a href="${escapeHtml(farmer.profile_url)}">View SwineLocate Farmer Profile</a>`
            ));

    </script>
</x-guest-layout>
