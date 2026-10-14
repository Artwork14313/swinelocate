<style>
    @media (max-width: 767px) {
  .mobile-top-space {
    margin-top: 64px; 
  }
}
</style>
<x-guest-layout :show-navigation="true">
    <div class="min-h-screen bg-gray-100 px-4 py-8 sm:px-6 lg:px-8">
        <main class="mx-auto max-w-4xl space-y-6">
            <header class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 mobile-top-space">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">SwineLocate Marketplace</p>
                <h1 class="mt-2 text-3xl font-bold text-gray-900">Find swine nearby</h1>
                <p class="mt-2 text-gray-600">
                    Browse active swine listings from other farmers. Share your location to sort by distance, or search by municipality or province.
                </p>
            </header>

            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form method="POST" action="{{ route('farmers.nearby.search') }}" id="nearby-search-form" class="space-y-5">
                    @csrf
                    <input type="hidden" name="latitude" id="buyer-latitude" value="{{ old('latitude', $latitude ?? '') }}">
                    <input type="hidden" name="longitude" id="buyer-longitude" value="{{ old('longitude', $longitude ?? '') }}">

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="municipality" class="block text-sm font-medium text-gray-700">Municipality / city</label>
                            <input id="municipality" name="municipality" type="text" maxlength="120"
                                value="{{ old('municipality', $municipality ?? '') }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g. San Jose">
                            @error('municipality')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700">Province (optional)</label>
                            <input id="province" name="province" type="text" maxlength="120"
                                value="{{ old('province', $province ?? '') }}"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g. Antique">
                            @error('province')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @error('latitude')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('longitude')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-800">
                            Search by town
                        </button>
                        <button type="button" id="use-my-location"
                            class="inline-flex items-center justify-center rounded-lg border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-800 hover:bg-indigo-50">
                            Use my location
                        </button>
                    </div>
                    <p id="location-status" class="text-sm text-gray-600" role="status" aria-live="polite">
                        Your location is only used to sort results by approximate distance; it is not displayed to farmers.
                    </p>
                </form>
            </section>

            @if (isset($results))
                <section class="space-y-4">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Available listings</h2>
                            <p class="mt-1 text-sm text-gray-600">{{ $results->total() }} farmer{{ $results->total() === 1 ? '' : 's' }} found</p>
                        </div>
                    </div>

                    @forelse ($results as $farmer)
                        <article class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                            <div class="flex flex-col gap-2 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $farmer->name }}</h3>
                                    <p class="text-sm text-gray-600">{{ $farmer->farm->name }}</p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ collect([$farmer->farm->municipality, $farmer->farm->province])->filter()->join(', ') ?: 'General location not provided' }}
                                    </p>
                                </div>
                                @if ($farmer->distance_km !== null)
                                    <span class="inline-flex w-fit rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-800">
                                        About {{ number_format($farmer->distance_km, 0) }} km away
                                    </span>
                                @elseif ($latitude !== null && $longitude !== null)
                                    <span class="text-sm text-gray-500">Distance unavailable</span>
                                @endif
                            </div>

                            <ul class="divide-y divide-gray-100">
                                @foreach ($farmer->swine as $swine)
                                    <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $swine->breed ?: 'Breed not specified' }}</p>
                                            <p class="mt-1 text-sm text-gray-600">{{ ucfirst($swine->sex) }}</p>
                                        </div>
                                        <div class="flex items-center justify-between gap-4 sm:justify-end">
                                            <p class="font-semibold text-indigo-700">
                                                {{ $swine->asking_price !== null ? '₱'.number_format((float) $swine->asking_price, 2) : 'Ask the farmer' }}
                                            </p>
                                            <a href="{{ route('farmers.public-profile', $farmer->qr_token) }}"
                                                class="inline-flex shrink-0 items-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                                View listing
                                            </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </article>
                    @empty
                        <div class="rounded-xl bg-white px-6 py-10 text-center shadow-sm ring-1 ring-gray-200">
                            <p class="font-medium text-gray-900">No available listings found.</p>
                            <p class="mt-1 text-sm text-gray-600">Try another municipality or province, or search using your location.</p>
                        </div>
                    @endforelse

                    @if ($results->lastPage() > 1)
                        <nav class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200" aria-label="Listing pages">
                            @foreach ([
                                'Previous' => $results->currentPage() - 1,
                                'Next' => $results->currentPage() + 1,
                            ] as $label => $page)
                                @if (($label === 'Previous' && $results->currentPage() > 1) || ($label === 'Next' && $results->hasMorePages()))
                                    <form method="POST" action="{{ route('farmers.nearby.search') }}">
                                        @csrf
                                        <input type="hidden" name="latitude" value="{{ $latitude }}">
                                        <input type="hidden" name="longitude" value="{{ $longitude }}">
                                        <input type="hidden" name="municipality" value="{{ $municipality }}">
                                        <input type="hidden" name="province" value="{{ $province }}">
                                        <input type="hidden" name="page" value="{{ $page }}">
                                        <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                            {{ $label }}
                                        </button>
                                    </form>
                                @else
                                    <span></span>
                                @endif
                            @endforeach
                        </nav>
                    @endif
                </section>
            @endif

            <footer class="text-center text-xs text-gray-500">
                Distances are approximate. Confirm availability and details directly with the farmer.
            </footer>
        </main>
    </div>

    <script>
        document.getElementById('use-my-location').addEventListener('click', function () {
            const status = document.getElementById('location-status');
            const button = this;

            if (!navigator.geolocation) {
                status.textContent = 'This browser does not support location sharing. Enter a municipality or province instead.';
                return;
            }

            button.disabled = true;
            status.textContent = 'Requesting your location permission...';
            navigator.geolocation.getCurrentPosition(function (position) {
                document.getElementById('buyer-latitude').value = position.coords.latitude;
                document.getElementById('buyer-longitude').value = position.coords.longitude;
                document.getElementById('municipality').value = '';
                document.getElementById('province').value = '';
                status.textContent = 'Location received. Searching nearby listings...';
                document.getElementById('nearby-search-form').requestSubmit();
            }, function (error) {
                const messages = {
                    1: 'Location permission was denied. Allow location access or enter a municipality/province instead.',
                    2: 'Your location could not be determined. Try again or enter a municipality/province instead.',
                    3: 'Location request timed out. Try again or enter a municipality/province instead.'
                };
                status.textContent = messages[error.code] || 'Location could not be accessed. Enter a municipality or province instead.';
                button.disabled = false;
            }, {
                enableHighAccuracy: false,
                timeout: 10000,
                maximumAge: 300000
            });
        });
    </script>
</x-guest-layout>
