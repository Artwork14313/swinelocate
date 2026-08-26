<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Swine Movements
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Track the movement and location history of swine.
                </p>
            </div>


            <a href="{{ route('swine.index') }}" class="inline-flex items-center justify-center rounded-lg
                       bg-[#3368A0] px-4 py-2 text-sm font-semibold
                       text-white hover:bg-[#28557F]">
                Select Swine to Move
            </a>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">


            {{-- ==========================================================
            SUMMARY CARDS
            =========================================================== --}}

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">


                {{-- Total --}}

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                    <p class="text-sm font-medium text-gray-500">
                        Total Movements
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $totalMovements }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        All recorded movements
                    </p>

                </div>


                {{-- Today --}}

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                    <p class="text-sm font-medium text-gray-500">
                        Today's Movements
                    </p>

                    <p class="mt-2 text-3xl font-bold text-[#3368A0]">
                        {{ $todayMovements }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Movements recorded today
                    </p>

                </div>


                {{-- This Month --}}

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                    <p class="text-sm font-medium text-gray-500">
                        This Month
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $thisMonthMovements }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Movements this month
                    </p>

                </div>

            </div>

            {{-- ==========================================================
            FILTERS
            =========================================================== --}}

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Search & Filter
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Find movement records by swine or date.
                    </p>

                </div>


                <form method="GET" action="{{ route('swine-movements.index') }}"
                    class="grid grid-cols-1 gap-5 px-6 py-6 sm:grid-cols-2 lg:grid-cols-4">

                    {{-- Search --}}
                    <div>

                        <label for="search" class="block text-sm font-medium text-gray-700">
                            Search Swine
                        </label>

                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                            placeholder="Tag number" class="mt-2 block w-full rounded-lg border-gray-300
                                   shadow-sm focus:border-indigo-500
                                   focus:ring-indigo-500">

                    </div>


                    {{-- From Date --}}
                    <div>

                        <label for="from_date" class="block text-sm font-medium text-gray-700">
                            From Date
                        </label>

                        <input type="date" id="from_date" name="from_date" value="{{ request('from_date') }}" class="mt-2 block w-full rounded-lg border-gray-300
                                   shadow-sm focus:border-indigo-500
                                   focus:ring-indigo-500">

                    </div>


                    {{-- To Date --}}
                    <div>

                        <label for="to_date" class="block text-sm font-medium text-gray-700">
                            To Date
                        </label>

                        <input type="date" id="to_date" name="to_date" value="{{ request('to_date') }}" class="mt-2 block w-full rounded-lg border-gray-300
                                   shadow-sm focus:border-indigo-500
                                   focus:ring-indigo-500">

                    </div>


                    {{-- Buttons --}}
                    <div class="flex items-end gap-2">

                        <button type="submit" class="inline-flex flex-1 items-center justify-center
                                   rounded-lg bg-[#3368A0] px-4 py-2.5
                                   text-sm font-semibold text-white
                                   hover:bg-[#28557F]">
                            Search
                        </button>


                        <a href="{{ route('swine-movements.index') }}" class="inline-flex items-center justify-center
                                   rounded-lg border border-gray-300
                                   bg-white px-4 py-2.5
                                   text-sm font-semibold text-gray-700
                                   hover:bg-gray-50">
                            Reset
                        </a>

                    </div>

                </form>

            </div>

            {{-- ==========================================================
            MOVEMENT HISTORY
            =========================================================== --}}

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">


                {{-- Header --}}

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Movement History
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Recent swine location movements.
                    </p>

                </div>


                @if ($movements->isEmpty())

                    {{-- Empty State --}}

                    <div class="px-6 py-12 text-center">

                        <p class="text-sm font-semibold text-gray-900">
                            No movement records found.
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            No swine movements have been recorded yet.
                        </p>

                        <a href="{{ route('swine-movements.create') }}" class="mt-4 inline-flex rounded-lg
                                                       bg-[#3368A0] px-4 py-2
                                                       text-sm font-semibold text-white
                                                       hover:bg-[#28557F]">
                            Record First Movement
                        </a>

                    </div>

                @else

                    {{-- Desktop Table --}}

                    <div class="hidden overflow-x-auto md:block">

                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">

                                <tr>

                                    <th class="px-6 py-3 text-left text-xs
                                                                   font-semibold uppercase
                                                                   tracking-wider text-gray-500">
                                        Date
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                                                   font-semibold uppercase
                                                                   tracking-wider text-gray-500">
                                        Swine
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                                                   font-semibold uppercase
                                                                   tracking-wider text-gray-500">
                                        From
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                                                   font-semibold uppercase
                                                                   tracking-wider text-gray-500">
                                        To
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                                                   font-semibold uppercase
                                                                   tracking-wider text-gray-500">
                                        Reason
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                                                   font-semibold uppercase
                                                                   tracking-wider text-gray-500">
                                        Recorded By
                                    </th>

                                    <th class="px-6 py-3 text-right text-xs
                                                                   font-semibold uppercase
                                                                   tracking-wider text-gray-500">
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-200 bg-white">

                                @foreach ($movements as $movement)

                                                        <tr class="hover:bg-gray-50">

                                                            {{-- Date --}}

                                                            <td class="whitespace-nowrap px-6 py-4">

                                                                <p class="text-sm font-semibold text-gray-900">
                                                                    {{ $movement->movement_date?->format('M d, Y') }}
                                                                </p>

                                                                <p class="mt-1 text-xs text-gray-500">
                                                                    {{ $movement->movement_date?->format('h:i A') }}
                                                                </p>

                                                            </td>


                                                            {{-- Swine --}}

                                                            <td class="whitespace-nowrap px-6 py-4">

                                                                <a href="{{ route('swine.show', $movement->swine) }}"
                                                                    class="text-sm font-semibold text-[#3368A0]
                                                                                                                                                                                                   hover:underline">
                                                                    {{ $movement->swine?->tag_number ?? '—' }}
                                                                </a>

                                                                @if ($movement->swine?->name)

                                                                    <p class="mt-1 text-xs text-gray-500">
                                                                        {{ $movement->swine->name }}
                                                                    </p>

                                                                @endif

                                                            </td>


                                                            {{-- From --}}

                                                            <td class="px-6 py-4">

                                                                <span class="text-sm text-gray-700">
                                                                    {{ $movement->fromLocation?->name ?? 'No location' }}
                                                                </span>

                                                            </td>


                                                            {{-- To --}}

                                                            <td class="px-6 py-4">

                                                                <span class="text-sm font-medium text-gray-900">
                                                                    {{ $movement->toLocation?->name ?? '—' }}
                                                                </span>

                                                            </td>


                                                            {{-- Reason --}}

                                                            <td class="px-6 py-4">

                                                                @if ($movement->reason)

                                                                    <span
                                                                        class="inline-flex rounded-full
                                                                                                                                                                                                                             bg-gray-100 px-2.5 py-1
                                                                                                                                                                                                                             text-xs font-medium
                                                                                                                                                                                                                             text-gray-700">
                                                                        {{ $movement->reason }}
                                                                    </span>

                                                                @else

                                                                    <span class="text-sm text-gray-400">
                                                                        —
                                                                    </span>

                                                                @endif

                                                            </td>


                                                            {{-- Recorded By --}}

                                                            <td class="whitespace-nowrap px-6 py-4">

                                                                <span class="text-sm text-gray-700">
                                                                    {{ $movement->recordedBy?->name ?? 'Unknown' }}
                                                                </span>

                                                            </td>


                                                            {{-- Action --}}

                                                            <td class="whitespace-nowrap px-6 py-4 text-right">

                                                                <a href="{{ route(
                                        'swine-movements.show',
                                        $movement
                                    ) }}"
                                                                    class="inline-flex rounded-lg
                                                                                                                                                                                                   border border-gray-300
                                                                                                                                                                                                   bg-white px-3 py-2
                                                                                                                                                                                                   text-sm font-medium
                                                                                                                                                                                                   text-gray-700
                                                                                                                                                                                                   hover:bg-gray-50">
                                                                    View
                                                                </a>

                                                            </td>

                                                        </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- ======================================================
                    MOBILE CARDS
                    ======================================================= --}}

                    <div class="divide-y divide-gray-200 md:hidden">

                        @foreach ($movements as $movement)

                                        <div class="px-6 py-5">

                                            <div class="flex items-start justify-between gap-4">

                                                <div>

                                                    <a href="{{ route('swine.show', $movement->swine) }}"
                                                        class="font-semibold text-[#3368A0]">
                                                        {{ $movement->swine?->tag_number ?? '—' }}
                                                    </a>

                                                    <p class="mt-1 text-xs text-gray-500">
                                                        {{ $movement->movement_date?->format('M d, Y h:i A') }}
                                                    </p>

                                                </div>


                                                @if ($movement->reason)

                                                    <span
                                                        class="rounded-full bg-gray-100
                                                                                                                                                                             px-2.5 py-1 text-xs
                                                                                                                                                                             font-medium text-gray-700">
                                                        {{ $movement->reason }}
                                                    </span>

                                                @endif

                                            </div>


                                            <div class="mt-4 grid grid-cols-2 gap-4">

                                                <div>

                                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                                        From
                                                    </p>

                                                    <p class="mt-1 text-sm text-gray-900">
                                                        {{ $movement->fromLocation?->name ?? 'No location' }}
                                                    </p>

                                                </div>


                                                <div>

                                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                                        To
                                                    </p>

                                                    <p class="mt-1 text-sm font-medium text-gray-900">
                                                        {{ $movement->toLocation?->name ?? '—' }}
                                                    </p>

                                                </div>

                                            </div>


                                            <div class="mt-4 flex items-center justify-between">

                                                <p class="text-xs text-gray-500">
                                                    By:
                                                    <span class="font-medium text-gray-700">
                                                        {{ $movement->recordedBy?->name ?? 'Unknown' }}
                                                    </span>
                                                </p>


                                                <a href="{{ route(
                                'swine-movements.show',
                                $movement
                            ) }}"
                                                    class="text-sm font-medium text-[#3368A0]
                                                                                                                                                   hover:underline">
                                                    View Details
                                                </a>

                                            </div>

                                        </div>

                        @endforeach

                    </div>


                    {{-- Pagination --}}

                    @if ($movements->hasPages())

                        <div class="border-t border-gray-200 px-6 py-4">
                            {{ $movements->links() }}
                        </div>

                    @endif

                @endif

            </div>

        </div>

    </div>

</x-app-layout>