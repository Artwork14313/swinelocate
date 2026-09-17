<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h2 class="text-2xl font-bold text-gray-900">
                    Weight Records
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    View and manage recorded swine weights and growth history.
                </p>

            </div>

                <a href="{{ route('weight-records.create') }}"
                    class="inline-flex items-center justify-center rounded-lg
                           bg-[#3368A0] px-4 py-2 text-sm font-semibold
                           text-white shadow-sm transition hover:bg-[#28557F]
                           focus:outline-none focus:ring-2 focus:ring-[#3368A0]
                           focus:ring-offset-2">

                    Add Weight Record

                </a>
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


            {{-- Validation / Error Message --}}
            @if ($errors->any())

                <div class="mb-6 rounded-lg border border-red-200
                            bg-red-50 px-4 py-3">

                    <p class="text-sm font-semibold text-red-800">
                        Please correct the following errors:
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- Search --}}
            <div class="mb-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">

                <form method="GET"
                    action="{{ route('weight-records.index') }}">

                    <div class="weight-search-filter">

                        <div class="weight-search-field">

                            <label for="search"
                                class="mb-1 block text-sm font-medium text-gray-700">

                                Search

                            </label>

                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Tag number"
                                autocomplete="off"
                                class="block h-[42px] w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                            >

                        </div>


                        <div class="weight-filter-buttons">

                            <button
                                type="submit"
                                class="inline-flex w-full items-center rounded-lg
                       bg-gray-800 px-4 py-2.5 text-sm font-semibold
                       text-white transition hover:bg-gray-900
                       focus:outline-none focus:ring-2
                       focus:ring-gray-500 focus:ring-offset-2">

                                Search

                            </button>


                            @if (request()->filled('search'))

                                <a
                                    href="{{ route('weight-records.index') }}"
                                    class="inline-flex w-fit items-center rounded-lg
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


            {{-- Weight Records --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Weight History
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Weight records are displayed from newest to oldest.
                    </p>

                </div>


                @if ($weightRecords->isEmpty())

                    <div class="px-6 py-12 text-center">

                        <div class="mx-auto flex h-12 w-12 items-center justify-center
                                    rounded-full bg-gray-100">

                            <svg
                                class="h-6 w-6 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"
                                />

                            </svg>

                        </div>


                        <p class="mt-4 text-sm font-medium text-gray-900">

                            @if (request()->filled('search'))

                                No weight records found for
                                "{{ request('search') }}".

                            @else

                                No weight records found.

                            @endif

                        </p>


                        <p class="mt-1 text-sm text-gray-500">

                            @if (request()->filled('search'))

                                Try a different tag number.

                            @else

                                Start recording weights to monitor swine growth.

                            @endif

                        </p>

                            <a
                                href="{{ route('weight-records.create') }}"
                                class="mt-4 inline-flex rounded-lg bg-[#3368A0]
                                       px-4 py-2 text-sm font-semibold text-white
                                       transition hover:bg-[#28557F]">

                                Add Weight Record

                            </a>

                    </div>

                @else


                    {{-- Desktop Table --}}
                    <div class="hidden overflow-x-auto md:block">

                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">

                                <tr>

                                    <th
                                        class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500"
                                    >

                                        Swine

                                    </th>


                                    <th
                                        class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500"
                                    >

                                        Record Date

                                    </th>


                                    <th
                                        class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500"
                                    >

                                        Weight

                                    </th>


                                    <th
                                        class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500"
                                    >

                                        Swine Status

                                    </th>


                                    <th
                                        class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500"
                                    >

                                        Recorded By

                                    </th>


                                    <th
                                        class="px-6 py-3 text-right text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500"
                                    >

                                        Action

                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-100 bg-white">

                                @foreach ($weightRecords as $record)

                                    <tr class="transition hover:bg-gray-50">


                                        {{-- Swine --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            @if ($record->swine)

                                                <div class="text-sm font-semibold text-gray-900">

                                                    {{ $record->swine->tag_number }}

                                                </div>


                                                @if ($record->swine->name)

                                                    <div class="text-xs text-gray-500">

                                                        {{ $record->swine->name }}

                                                    </div>

                                                @endif

                                            @else

                                                <div class="text-sm font-medium text-gray-500">

                                                    Unknown Swine

                                                </div>

                                            @endif

                                        </td>


                                        {{-- Record Date --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span class="text-sm text-gray-700">

                                                {{ $record->record_date?->format('M d, Y') ?? '—' }}

                                            </span>

                                        </td>


                                        {{-- Weight --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span class="text-sm font-semibold text-gray-900">

                                                {{ number_format((float) $record->weight, 2) }}

                                                kg

                                            </span>

                                        </td>


                                        {{-- Swine Status --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            @php

                                                $status = $record->swine?->status;

                                                $statusClasses = match ($status) {

                                                    'active' =>
                                                        'bg-green-100 text-green-700',

                                                    'inactive' =>
                                                        'bg-gray-100 text-gray-700',

                                                    'sold' =>
                                                        'bg-blue-100 text-blue-700',

                                                    'deceased' =>
                                                        'bg-red-100 text-red-700',

                                                    default =>
                                                        'bg-gray-100 text-gray-500',

                                                };

                                            @endphp


                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1
                                                       text-xs font-semibold {{ $statusClasses }}"
                                            >

                                                {{ $status ? ucfirst($status) : 'Unknown' }}

                                            </span>

                                        </td>


                                        {{-- Recorded By --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span class="text-sm text-gray-700">

                                                {{ $record->recordedBy?->name ?? 'Unknown' }}

                                            </span>

                                        </td>


                                        {{-- Action --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-right">

                                            <a
                                                href="{{ route('weight-records.show', $record) }}"
                                                class="inline-flex rounded-lg border
                                                       border-gray-300 bg-white px-3 py-2
                                                       text-sm font-medium text-gray-700
                                                       transition hover:bg-gray-50
                                                       focus:outline-none focus:ring-2
                                                       focus:ring-gray-400
                                                       focus:ring-offset-2"
                                            >

                                                View

                                            </a>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- Mobile Cards --}}
                    <div class="divide-y divide-gray-100 md:hidden">

                        @foreach ($weightRecords as $record)

                            @php

                                $status = $record->swine?->status;

                                $statusClasses = match ($status) {

                                    'active' =>
                                        'bg-green-100 text-green-700',

                                    'inactive' =>
                                        'bg-gray-100 text-gray-700',

                                    'sold' =>
                                        'bg-blue-100 text-blue-700',

                                    'deceased' =>
                                        'bg-red-100 text-red-700',

                                    default =>
                                        'bg-gray-100 text-gray-500',

                                };

                            @endphp


                            <div class="p-5">

                                <div class="flex items-start justify-between gap-4">

                                    <div class="min-w-0">

                                        @if ($record->swine)

                                            <p class="truncate text-sm font-semibold text-gray-900">

                                                {{ $record->swine->tag_number }}

                                            </p>


                                            @if ($record->swine->name)

                                                <p class="mt-0.5 truncate text-xs text-gray-500">

                                                    {{ $record->swine->name }}

                                                </p>

                                            @endif

                                        @else

                                            <p class="text-sm font-semibold text-gray-500">

                                                Unknown Swine

                                            </p>

                                        @endif

                                    </div>


                                    <span
                                        class="shrink-0 inline-flex rounded-full
                                               px-2.5 py-1 text-xs font-semibold
                                               {{ $statusClasses }}"
                                    >

                                        {{ $status ? ucfirst($status) : 'Unknown' }}

                                    </span>

                                </div>


                                <div class="mt-4 grid grid-cols-2 gap-4">

                                    <div>

                                        <p class="text-xs font-medium uppercase
                                                  tracking-wide text-gray-500">

                                            Record Date

                                        </p>

                                        <p class="mt-1 text-sm text-gray-900">

                                            {{ $record->record_date?->format('M d, Y') ?? '—' }}

                                        </p>

                                    </div>


                                    <div>

                                        <p class="text-xs font-medium uppercase
                                                  tracking-wide text-gray-500">

                                            Weight

                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-gray-900">

                                            {{ number_format((float) $record->weight, 2) }}
                                            kg

                                        </p>

                                    </div>

                                </div>


                                <div class="mt-4">

                                    <p class="text-xs font-medium uppercase
                                              tracking-wide text-gray-500">

                                        Recorded By

                                    </p>

                                    <p class="mt-1 text-sm text-gray-900">

                                        {{ $record->recordedBy?->name ?? 'Unknown' }}

                                    </p>

                                </div>


                                <div class="mt-4">

                                    <a
                                        href="{{ route('weight-records.show', $record) }}"
                                        class="inline-flex w-full items-center justify-center
                                               rounded-lg border border-gray-300
                                               bg-white px-3 py-2 text-sm font-medium
                                               text-gray-700 transition hover:bg-gray-50"
                                    >

                                        View Weight Record

                                    </a>

                                </div>

                            </div>

                        @endforeach

                    </div>


                    {{-- Pagination --}}
                    @if ($weightRecords->hasPages())

                        <div class="border-t border-gray-200 px-6 py-4">

                            {{ $weightRecords->links() }}

                        </div>

                    @endif

                @endif

            </div>

        </div>

    </div>


    <style>

        .weight-search-filter {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .weight-search-field,
        .weight-filter-buttons {
            min-width: 0;
        }

        .weight-filter-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
        }

        @media (min-width: 768px) {

            .weight-search-filter {
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: end;
            }

            .weight-filter-buttons {
                white-space: nowrap;
            }

        }

    </style>

</x-app-layout>