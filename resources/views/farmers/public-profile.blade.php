
<x-guest-layout :show-navigation="true">
    <div class="min-h-screen bg-gray-100 px-4 py-8 sm:px-6 lg:px-8">
        <main class="mx-auto max-w-4xl space-y-6 ">
            <header class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 ">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">SwineLocate Farmer Profile</p>
                <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ $farmer->name }}</h1>
                <p class="mt-2 text-gray-600">{{ $farmer->farm->name }}</p>
                <p class="mt-1 text-sm text-gray-500">
                    {{ collect([$farmer->farm->municipality, $farmer->farm->province, $farmer->farm->region])->filter()->join(', ') ?: 'Location not provided' }}
                </p>
            </header>

            <section class="rounded-xl bg-indigo-50 p-5 ring-1 ring-indigo-100 sm:flex sm:items-center sm:justify-between sm:gap-6">
                <div>
                    <h2 class="font-semibold text-indigo-950">Looking for a different swine?</h2>
                    <p class="mt-1 text-sm text-indigo-800">Browse farmers with available swine in Sagay City, Negros Occidental.</p>
                </div>
                <a href="{{ route('farmers.nearby') }}"
                    class="mt-4 inline-flex shrink-0 items-center justify-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-800 sm:mt-0">
                    Find farmers on the map
                </a>
            </section>

            <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="flex items-center justify-between gap-4 border-b border-gray-200 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Swine Available for Sale</h2>
                        <p class="mt-1 text-sm text-gray-500">Only active animals the farmer has listed for sale are shown.</p>
                    </div>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700">
                        {{ $farmer->swine->count() }}
                    </span>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($farmer->swine as $swine)
                        <article class="grid gap-4 px-6 py-5 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Breed</p>
                                <p class="mt-1 font-semibold text-gray-900">{{ $swine->breed ?: 'Breed not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Sex / Age</p>
                                <p class="mt-1 text-sm text-gray-800">{{ ucfirst($swine->sex) }}</p>
                                @if ($swine->birth_date)
                                    <p class="mt-1 text-sm text-gray-600">
                                        Approx. {{ (int) abs($swine->birth_date->diffInMonths(now())) }} months old
                                    </p>
                                @else
                                    <p class="mt-1 text-sm text-gray-600">Age not recorded</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Latest Recorded Weight</p>
                                @if ($swine->latestWeightRecord)
                                    <p class="mt-1 text-sm font-medium text-gray-800">
                                        {{ number_format((float) $swine->latestWeightRecord->weight, 2) }} kg
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Recorded {{ $swine->latestWeightRecord->record_date->format('M d, Y') }}
                                    </p>
                                @else
                                    <p class="mt-1 text-sm text-gray-600">Not recorded</p>
                                @endif
                            </div>
                            <div class="sm:col-span-2 lg:col-span-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Asking Price</p>
                                @if ($swine->asking_price !== null)
                                    <p class="mt-1 text-lg font-semibold text-indigo-700">₱{{ number_format((float) $swine->asking_price, 2) }}</p>
                                @else
                                    <p class="mt-1 text-sm text-gray-600">Ask the farmer</p>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="px-6 py-10 text-center text-sm text-gray-500">
                            No swine are currently listed for sale.
                        </p>
                    @endforelse
                </div>
            </section>

            <p class="text-center text-sm text-gray-500">
                Confirm current availability and request health and traceability records directly from the farmer.
            </p>

            <footer class="text-center text-xs text-gray-500">
                SwineLocate · QR-Based Swine Traceability and Management
            </footer>
        </main>
    </div>
</x-guest-layout>
