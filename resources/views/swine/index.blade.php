<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Swine Management
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Manage registered swine and their identification records.
                </p>
            </div>

            {{-- Register Swine --}}
            @if (auth()->user()->hasPermission('register-swine'))

                <a href="{{ route('swine.create') }}" class="inline-flex items-center justify-center rounded-lg
                               bg-indigo-600 px-4 py-2.5 text-sm font-semibold
                               text-white shadow-sm hover:bg-indigo-700
                               focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:ring-offset-2">
                    + Register Swine
                </a>

            @endif

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if (session('success'))

                <div class="mb-6 rounded-lg border border-green-200
                                bg-green-50 px-4 py-3 text-sm text-green-700">

                    {{ session('success') }}

                </div>

            @endif


            {{-- Error Message --}}
            @if (session('error'))

                <div class="mb-6 rounded-lg border border-red-200
                                bg-red-50 px-4 py-3 text-sm text-red-700">

                    {{ session('error') }}

                </div>

            @endif

            {{-- Search and Filter --}}
<div class="mb-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">

    <form method="GET" action="{{ route('swine.index') }}">

        <div class="swine-search-filter">

            {{-- Search --}}
            <div class="swine-search-field">
                <label for="search" class="mb-1 block text-sm font-medium text-gray-700">
                    Search
                </label>

                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Tag number"
                    autocomplete="off"
                    class="block h-[42px] w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            {{-- Status --}}
            <div class="swine-status-field">
                <label for="status" class="mb-1 block text-sm font-medium text-gray-700">
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                    class="block h-[42px] w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">All Statuses</option>

                    <option value="active" @selected(request('status') === 'active')>
                        Active
                    </option>

                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inactive
                    </option>

                    <option value="sold" @selected(request('status') === 'sold')>
                        Sold
                    </option>

                    <option value="deceased" @selected(request('status') === 'deceased')>
                        Deceased
                    </option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="swine-filter-buttons">

                <button type="submit" class="inline-flex w-full items-center rounded-lg
                       bg-gray-800 px-4 py-2.5 text-sm font-semibold
                       text-white transition hover:bg-gray-900
                       focus:outline-none focus:ring-2
                       focus:ring-gray-500 focus:ring-offset-2">
                            Search
                        </button>

                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('swine.index') }}" class="inline-flex w-fit items-center rounded-lg
                                   border border-gray-300 bg-white px-4 py-2.5
                                   text-sm font-semibold text-gray-700
                                   transition hover:bg-gray-50
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-400 focus:ring-offset-2">
                                Clear
                            </a>
                @endif

            </div>

        </div>

    </form>
</div>

<style>
    .swine-search-filter {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .swine-search-field,
    .swine-status-field,
    .swine-filter-buttons {
        min-width: 0;
    }

    .swine-filter-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: flex-end;
    }

    @media (min-width: 768px) {
        .swine-search-filter {
            grid-template-columns: minmax(0, 1fr) 13rem auto;
            align-items: end;
        }

        .swine-filter-buttons {
            white-space: nowrap;
        }
    }
</style>

            {{-- Main Table --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                       font-semibold uppercase tracking-wider
                                       text-gray-500">
                                    Tag Number
                                </th>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                       font-semibold uppercase tracking-wider
                                       text-gray-500">
                                    Farm
                                </th>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                       font-semibold uppercase tracking-wider
                                       text-gray-500">
                                    Current Location
                                </th>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                       font-semibold uppercase tracking-wider
                                       text-gray-500">
                                    Sex
                                </th>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                       font-semibold uppercase tracking-wider
                                       text-gray-500">
                                    Breed
                                </th>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                       font-semibold uppercase tracking-wider
                                       text-gray-500">
                                    Status
                                </th>

                                <th scope="col" class="px-6 py-4 text-right text-xs
                                       font-semibold uppercase tracking-wider
                                       text-gray-500">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100 bg-white">

                            @forelse ($swine as $pig)

                                <tr class="transition hover:bg-gray-50">

                                    {{-- Tag Number --}}
                                    <td class="whitespace-nowrap px-6 py-4">

                                        <a href="{{ route('swine.show', $pig) }}"
                                            class="font-semibold text-indigo-600 hover:text-indigo-800">
                                            {{ $pig->tag_number }}
                                        </a>

                                    </td>


                                    {{-- Farm --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">

                                        @if ($pig->farm)

                                            <div class="font-medium text-gray-900">
                                                {{ $pig->farm->name }}
                                            </div>

                                            @if ($pig->farm->farm_code)

                                                <div class="text-xs text-gray-500">
                                                    {{ $pig->farm->farm_code }}
                                                </div>

                                            @endif

                                        @else

                                            <span class="text-gray-400">
                                                —
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Current Location --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">

                                        @if ($pig->currentLocation)

                                            <div class="font-medium text-gray-900">
                                                {{ $pig->currentLocation->name }}
                                            </div>

                                            @if ($pig->currentLocation->location_code)

                                                <div class="text-xs text-gray-500">
                                                    {{ $pig->currentLocation->location_code }}
                                                </div>

                                            @endif

                                        @else

                                            <span class="text-gray-400">
                                                Unassigned
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Sex --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ ucfirst($pig->sex) }}
                                    </td>


                                    {{-- Breed --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $pig->breed ?: '—' }}
                                    </td>


                                    {{-- Status --}}
                                    <td class="whitespace-nowrap px-6 py-4">

                                        @if ($pig->status === 'active')

                                            <span class="inline-flex rounded-full
                                                                     bg-green-100 px-2.5 py-1
                                                                     text-xs font-semibold
                                                                     text-green-700">
                                                Active
                                            </span>

                                        @elseif ($pig->status === 'inactive')

                                            <span class="inline-flex rounded-full
                                                                     bg-gray-100 px-2.5 py-1
                                                                     text-xs font-semibold
                                                                     text-gray-700">
                                                Inactive
                                            </span>

                                        @elseif ($pig->status === 'sold')

                                            <span class="inline-flex rounded-full
                                                                     bg-blue-100 px-2.5 py-1
                                                                     text-xs font-semibold
                                                                     text-blue-700">
                                                Sold
                                            </span>

                                        @elseif ($pig->status === 'deceased')

                                            <span class="inline-flex rounded-full
                                                                     bg-red-100 px-2.5 py-1
                                                                     text-xs font-semibold
                                                                     text-red-700">
                                                Deceased
                                            </span>

                                        @else

                                            <span class="inline-flex rounded-full
                                                                     bg-gray-100 px-2.5 py-1
                                                                     text-xs font-semibold
                                                                     text-gray-700">
                                                {{ ucfirst($pig->status) }}
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Actions --}}
                                    <td class="whitespace-nowrap px-6 py-4">

                                        <div class="flex items-center justify-end gap-2">

                                            {{-- Move --}}
                                            @if (
                                                    $pig->status === 'active' &&
                                                    auth()->user()->hasPermission('manage-movements')
                                                )

                                                <a href="{{ route('swine.movements.create', $pig) }}" title="Move Swine"
                                                    aria-label="Move Swine" class="group relative inline-flex h-9 w-9 items-center justify-center
                                                                       rounded-lg text-[#3368A0]
                                                                       transition hover:bg-blue-50
                                                                       hover:text-[#28557F]">

                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.8" stroke="currentColor" class="h-5 w-5">

                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />

                                                    </svg>

                                                    <span class="pointer-events-none absolute bottom-full left-1/2 mb-2
                                                                           -translate-x-1/2 whitespace-nowrap rounded-md
                                                                           bg-gray-900 px-2 py-1 text-xs font-medium text-white
                                                                           opacity-0 shadow-sm transition
                                                                           group-hover:opacity-100">
                                                        Move
                                                    </span>

                                                </a>

                                            @endif


                                            {{-- View --}}
                                            <a href="{{ route('swine.show', $pig) }}" title="View Swine"
                                                aria-label="View Swine" class="group relative inline-flex h-9 w-9 items-center justify-center
                                                           rounded-lg text-gray-600
                                                           transition hover:bg-gray-100
                                                           hover:text-gray-900">

                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.8" stroke="currentColor" class="h-5 w-5">

                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />

                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />

                                                </svg>

                                                <span class="pointer-events-none absolute bottom-full left-1/2 mb-2
                                                               -translate-x-1/2 whitespace-nowrap rounded-md
                                                               bg-gray-900 px-2 py-1 text-xs font-medium text-white
                                                               opacity-0 shadow-sm transition
                                                               group-hover:opacity-100">
                                                    View
                                                </span>

                                            </a>


                                            {{-- Edit --}}
                                            @if (auth()->user()->hasPermission('manage-swine'))

                                                <a href="{{ route('swine.edit', $pig) }}" title="Edit Swine"
                                                    aria-label="Edit Swine" class="group relative inline-flex h-9 w-9 items-center justify-center
                                                                       rounded-lg text-indigo-600
                                                                       transition hover:bg-indigo-50
                                                                       hover:text-indigo-800">

                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.8" stroke="currentColor" class="h-5 w-5">

                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652l-8.25 8.25-3.684.486.486-3.684 7.109-7.109Z" />

                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19.5 7.125 16.875 4.5M18 14.25V19.5A2.25 2.25 0 0 1 15.75 21.75h-11.5A2.25 2.25 0 0 1 2 19.5V8A2.25 2.25 0 0 1 4.25 5.75H9.5" />

                                                    </svg>

                                                    <span class="pointer-events-none absolute bottom-full left-1/2 mb-2
                                                                           -translate-x-1/2 whitespace-nowrap rounded-md
                                                                           bg-gray-900 px-2 py-1 text-xs font-medium text-white
                                                                           opacity-0 shadow-sm transition
                                                                           group-hover:opacity-100">
                                                        Edit
                                                    </span>

                                                </a>

                                            @endif

                                            {{-- Deactivate / Activate / Sold / Deceased --}}
                                            @if (auth()->user()->hasPermission('manage-swine'))

                                                @if ($pig->status === 'active')

                                                                        {{-- Deactivate --}}
                                                                        <form action="{{ route('swine.destroy', $pig) }}" method="POST" class="inline"
                                                                            onsubmit="return confirm('Are you sure you want to deactivate this swine?')">

                                                                            @csrf
                                                                            @method('DELETE')

                                                                            <button type="submit" title="Deactivate Swine" aria-label="Deactivate Swine"
                                                                                class="group relative inline-flex h-9 w-9 items-center justify-center
                                                       rounded-lg text-red-600
                                                       transition hover:bg-red-50
                                                       hover:text-red-800">

                                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                                                    stroke-width="1.8" stroke="currentColor" class="h-5 w-5">

                                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />

                                                                                </svg>

                                                                                <span class="pointer-events-none absolute bottom-full left-1/2 mb-2
                                                           -translate-x-1/2 whitespace-nowrap rounded-md
                                                           bg-gray-900 px-2 py-1 text-xs font-medium text-white
                                                           opacity-0 shadow-sm transition
                                                           group-hover:opacity-100">
                                                                                    Deactivate
                                                                                </span>

                                                                            </button>

                                                                        </form>


                                                                        {{-- Mark as Sold --}}
                                                                        <form action="{{ route('swine.sold', $pig) }}" method="POST" class="inline"
                                                                            onsubmit="return confirm('Are you sure you want to mark this swine as sold?')">

                                                                            @csrf
                                                                            @method('PATCH')

                                                                            <button type="submit" title="Mark as Sold" aria-label="Mark as Sold" class="group relative inline-flex h-9 w-9 items-center justify-center
                                                       rounded-lg text-blue-600
                                                       transition hover:bg-blue-50
                                                       hover:text-blue-800">

                                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                                                    stroke-width="1.8" stroke="currentColor" class="h-5 w-5">

                                                                                    {{-- Banknote icon --}}
                                                                                    <rect x="3" y="6" width="18" height="12" rx="2" />

                                                                                    <circle cx="12" cy="12" r="2.5" />

                                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                                        d="M7 9h.01M17 15h.01" />

                                                                                </svg>

                                                                                <span class="pointer-events-none absolute bottom-full left-1/2 mb-2
                                                           -translate-x-1/2 whitespace-nowrap rounded-md
                                                           bg-gray-900 px-2 py-1 text-xs font-medium text-white
                                                           opacity-0 shadow-sm transition
                                                           group-hover:opacity-100">
                                                                                    Mark Sold
                                                                                </span>

                                                                            </button>

                                                                        </form>


                                                                        {{-- Mark as Deceased --}}
                                                                        <form action="{{ route('swine.deceased', $pig) }}" method="POST" class="inline"
                                                                            onsubmit="return confirm('Are you sure you want to mark this swine as deceased?')">

                                                                            @csrf
                                                                            @method('PATCH')

                                                                            <button type="submit" title="Mark as Deceased" aria-label="Mark as Deceased"
                                                                                class="group relative inline-flex h-9 w-9 items-center justify-center
                                                       rounded-lg text-gray-600
                                                       transition hover:bg-gray-100
                                                       hover:text-gray-900">

                                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                                                    stroke-width="1.8" stroke="currentColor" class="h-5 w-5">

                                                                                    {{-- Skull-style icon --}}
                                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                                        d="M12 3a7 7 0 0 0-7 7c0 3.1 1.9 5.75 4.6 6.75V20h4.8v-3.25A7 7 0 0 0 19 10a7 7 0 0 0-7-7Z" />

                                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                                        d="M9 10h.01M15 10h.01M9.5 14h5" />

                                                                                </svg>

                                                                                <span class="pointer-events-none absolute bottom-full left-1/2 mb-2
                                                           -translate-x-1/2 whitespace-nowrap rounded-md
                                                           bg-gray-900 px-2 py-1 text-xs font-medium text-white
                                                           opacity-0 shadow-sm transition
                                                           group-hover:opacity-100">
                                                                                    Mark Deceased
                                                                                </span>

                                                                            </button>

                                                                        </form>


                                                @elseif ($pig->status === 'inactive')

                                                                        {{-- Activate --}}
                                                                        <form action="{{ route('swine.activate', $pig) }}" method="POST" class="inline"
                                                                            onsubmit="return confirm('Are you sure you want to activate this swine?')">

                                                                            @csrf
                                                                            @method('PATCH')

                                                                            <button type="submit" title="Activate Swine" aria-label="Activate Swine"
                                                                                class="group relative inline-flex h-9 w-9 items-center justify-center
                                                       rounded-lg text-green-600
                                                       transition hover:bg-green-50
                                                       hover:text-green-800">

                                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                                                    stroke-width="1.8" stroke="currentColor" class="h-5 w-5">

                                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                                        d="M12 6v12M6 12h12" />

                                                                                </svg>

                                                                                <span class="pointer-events-none absolute bottom-full left-1/2 mb-2
                                                           -translate-x-1/2 whitespace-nowrap rounded-md
                                                           bg-gray-900 px-2 py-1 text-xs font-medium text-white
                                                           opacity-0 shadow-sm transition
                                                           group-hover:opacity-100">
                                                                                    Activate
                                                                                </span>

                                                                            </button>

                                                                        </form>

                                                @endif

                                            @endif


                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7" class="px-6 py-16 text-center">

                                        <div class="text-sm font-medium text-gray-900">
                                            No swine registered
                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- Pagination --}}
                @if ($swine->hasPages())

                    <div class="border-t border-gray-200 px-6 py-4">
                        {{ $swine->links() }}
                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>