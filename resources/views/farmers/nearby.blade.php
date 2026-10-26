<x-guest-layout :show-navigation="!$embedded">
    @php
        $listingCount = $farmers->sum(fn ($farmer) => $farmer->swine->count());
    @endphp

    <div class="min-h-screen bg-stone-50">
        @unless ($embedded)
            <header class="border-b border-stone-200 bg-white">
                <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8" aria-label="Main navigation">
                    <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="SwineLocate home">
                        <img src="{{ asset('images/SwineLocateLogo2.png') }}" alt="" class="h-11 w-11 object-contain">
                        <span class="text-xl font-extrabold tracking-tight text-slate-900">Swine<span class="text-emerald-700">Locate</span></span>
                    </a>
                    <div class="flex items-center gap-3">
                        <a href="#farmers" class="hidden text-sm font-semibold text-slate-600 transition hover:text-emerald-700 sm:inline">Browse farmers</a>
                        <a href="{{ route('android-app') }}" class="hidden rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-stone-100 sm:inline-flex">Get the app</a>
                        @guest
                            <a href="{{ route('login') }}" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">Log in</a>
                        @endguest
                    </div>
                </nav>
            </header>
        @endunless

        <main>
            <section class="relative isolate overflow-hidden bg-slate-950">
                <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-emerald-900/50 via-slate-950 to-slate-950"></div>
                <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1.1fr_0.9fr] lg:px-8 lg:py-24">
                    <div>
                        <p class="inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3.5 py-1.5 text-sm font-semibold text-emerald-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            SwineLocate Marketplace
                        </p>
                        <h1 class="mt-6 max-w-2xl text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                            Find local farmers. <span class="text-emerald-300">Buy with confidence.</span>
                        </h1>
                        <p class="mt-5 max-w-xl text-lg leading-8 text-slate-300">
                            Discover farmers and swine available near Sagay City, Negros Occidental. Explore the map, compare listings, and connect directly with local farms.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="#farmers" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-400 px-6 py-3.5 font-bold text-slate-950 shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-300">
                                Explore available swine
                                <span aria-hidden="true">→</span>
                            </a>
                            <a href="#map" class="inline-flex items-center justify-center rounded-lg border border-white/20 px-6 py-3.5 font-semibold text-white transition hover:bg-white/10">
                                View farmer map
                            </a>
                        </div>
                        <p class="mt-5 text-sm text-slate-400">Local listings from Sagay City and nearby farms.</p>
                    </div>

                    <div class="relative mx-auto w-full max-w-md">
                        <div class="absolute -inset-5 rounded-[2rem] bg-emerald-400/10 blur-2xl"></div>
                        <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/[0.06] p-6 shadow-2xl backdrop-blur sm:p-8">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-300">Local marketplace</p>
                                    <h2 class="mt-1 text-2xl font-bold text-white">Sagay City</h2>
                                </div>
                                <img src="{{ asset('images/SwineLocateLogo2.png') }}" alt="" class="h-16 w-16 object-contain">
                            </div>
                            <div class="my-7 h-px bg-white/10"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-2xl bg-white/[0.07] p-4">
                                    <p class="text-3xl font-extrabold text-emerald-300">{{ $farmers->count() }}</p>
                                    <p class="mt-1 text-sm text-slate-300">local farmers</p>
                                </div>
                                <div class="rounded-2xl bg-white/[0.07] p-4">
                                    <p class="text-3xl font-extrabold text-emerald-300">{{ $listingCount }}</p>
                                    <p class="mt-1 text-sm text-slate-300">available listings</p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-start gap-3 rounded-2xl border border-white/10 bg-slate-900/60 p-4">
                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-400/15 text-emerald-300" aria-hidden="true">⌖</span>
                                <div>
                                    <p class="font-semibold text-white">Made for local connections</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-400">See farm locations and reach out to farmers directly to confirm availability.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="map" class="scroll-mt-8 px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wider text-emerald-700">Explore the area</p>
                            <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Farmers near Sagay City</h2>
                            <p class="mt-2 max-w-2xl text-slate-600">Use the map to find nearby farms. Select a marker to open that farmer’s profile.</p>
                        </div>
                        <a href="#farmers" class="inline-flex items-center gap-2 self-start text-sm font-bold text-emerald-800 transition hover:text-emerald-600 sm:self-auto">
                            Browse all listings <span aria-hidden="true">↓</span>
                        </a>
                    </div>

                    <div class="overflow-hidden rounded-2xl bg-white shadow-lg shadow-slate-900/5 ring-1 ring-slate-200">
                        <div id="farmer-map" class="h-[52vh] min-h-[340px] w-full" aria-label="Map of farmers in Sagay City"></div>
                        <p class="border-t border-slate-100 px-5 py-3 text-xs text-slate-500">
                            Showing {{ $mapFarmers->count() }} farmer pin{{ $mapFarmers->count() === 1 ? '' : 's' }} using each farm’s saved latitude and longitude. Map data © OpenStreetMap contributors.
                        </p>
                    </div>
                </div>
            </section>

            <section id="farmers" class="scroll-mt-8 bg-white px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wider text-emerald-700">Browse the marketplace</p>
                            <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Available from local farmers</h2>
                            <p class="mt-2 text-slate-600">Explore active swine listings and view each farmer’s profile for more details.</p>
                        </div>
                        @if ($farmers->isNotEmpty())
                            <span class="inline-flex self-start rounded-full bg-emerald-50 px-3.5 py-1.5 text-sm font-semibold text-emerald-800 sm:self-auto">
                                {{ $farmers->count() }} farmer{{ $farmers->count() === 1 ? '' : 's' }} · {{ $listingCount }} listing{{ $listingCount === 1 ? '' : 's' }}
                            </span>
                        @endif
                    </div>

                    @if ($farmers->isEmpty())
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-stone-50 px-6 py-12 text-center">
                            <h3 class="text-lg font-bold text-slate-900">No active listings just yet</h3>
                            <p class="mt-2 text-slate-600">There are no farmers with swine available for sale in Sagay City right now. Please check back soon.</p>
                        </div>
                    @else
                        @if ($mapFarmers->isEmpty())
                            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                                Farmer listings are available, but farm coordinates have not been set, so they cannot appear as map markers yet.
                            </div>
                        @endif
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($farmers as $farmer)
                                <a href="{{ route('farmers.public-profile', $farmer->qr_token) }}"
                                    class="group rounded-2xl border border-slate-200 bg-white p-5 transition duration-200 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg hover:shadow-slate-900/5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-2xl" aria-hidden="true">🐖</div>
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">Available</span>
                                    </div>
                                    <h3 class="mt-5 text-lg font-bold text-slate-900 group-hover:text-emerald-800">{{ $farmer->name }}</h3>
                                    <p class="mt-1 text-sm text-slate-600">{{ $farmer->farm->name }}</p>
                                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                                        <span class="text-sm text-slate-500">{{ $farmer->swine->count() }} active listing{{ $farmer->swine->count() === 1 ? '' : 's' }}</span>
                                        <span class="text-sm font-bold text-emerald-800">View profile <span aria-hidden="true">→</span></span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-stone-50 px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <p class="font-semibold text-slate-700">SwineLocate <span class="font-normal text-slate-500">· Connecting local farms and buyers</span></p>
                <p>Farm locations are approximate. Confirm availability and details directly with the farmer.</p>
            </div>
        </footer>
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
                `<a href="${escapeHtml(farmer.profile_url)}">View SwineLocate Farmer Profile</a><br>` +
                `<a href="https://www.google.com/maps/dir/?api=1&destination=${farmer.latitude},${farmer.longitude}&travelmode=driving" target="_blank" rel="noopener">Get directions from my location</a>`
            ));

    </script>
</x-guest-layout>
