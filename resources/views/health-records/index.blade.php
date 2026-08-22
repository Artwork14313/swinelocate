<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h2 class="text-2xl font-bold text-gray-900">
                    Health Records
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Current health status and latest health record for each swine.
                </p>

            </div>

            <a
                href="{{ route('health-records.create') }}"
                class="inline-flex items-center justify-center rounded-lg
                       bg-indigo-600 px-4 py-2 text-sm font-semibold
                       text-white shadow-sm hover:bg-indigo-700"
            >
                Add Health Record
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


            {{-- Health Records --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Swine Health Overview
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        One latest health record is shown for each swine.
                        Open Health History to view previous records.
                    </p>

                </div>


                @if ($healthRecords->isEmpty())

                    <div class="px-6 py-12 text-center">

                        <p class="text-sm font-medium text-gray-900">
                            No health records found.
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            Start by adding a health record for a swine.
                        </p>

                        <a
                            href="{{ route('health-records.create') }}"
                            class="mt-4 inline-flex rounded-lg bg-indigo-600
                                   px-4 py-2 text-sm font-semibold text-white
                                   hover:bg-indigo-700"
                        >
                            Add Health Record
                        </a>

                    </div>

                @else

                    {{-- Desktop Table --}}
                    <div class="hidden overflow-x-auto md:block">

                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">

                                <tr>

                                    <th class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500">
                                        Swine
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500">
                                        Latest Record
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500">
                                        Health Status
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500">
                                        Date
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500">
                                        Diagnosis
                                    </th>

                                    <th class="px-6 py-3 text-right text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-200 bg-white">

                                @foreach ($healthRecords as $healthRecord)

                                    @php

                                        $statusClasses = match (
                                            $healthRecord->health_status
                                        ) {

                                            'healthy' =>
                                                'bg-green-100 text-green-700',

                                            'under_observation' =>
                                                'bg-yellow-100 text-yellow-700',

                                            'sick' =>
                                                'bg-red-100 text-red-700',

                                            'recovering' =>
                                                'bg-blue-100 text-blue-700',

                                            default =>
                                                'bg-gray-100 text-gray-700',

                                        };

                                    @endphp


                                    <tr class="hover:bg-gray-50">

                                        {{-- Swine --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <div class="font-semibold text-gray-900">

                                                {{ $healthRecord->swine?->tag_number ?? '—' }}

                                            </div>

                                            @if ($healthRecord->swine?->name)

                                                <div class="text-sm text-gray-500">
                                                    {{ $healthRecord->swine->name }}
                                                </div>

                                            @endif

                                            <div class="text-xs text-gray-400">

                                                {{ $healthRecord->swine?->farm?->name ?? '—' }}

                                            </div>

                                        </td>


                                        {{-- Latest Record --}}
                                        <td class="px-6 py-4">

                                            <div class="font-medium text-gray-900">

                                                {{ $healthRecord->record_type }}

                                            </div>

                                            @if ($healthRecord->treatment)

                                                <div class="mt-1 max-w-xs truncate text-sm text-gray-500">

                                                    {{ $healthRecord->treatment }}

                                                </div>

                                            @endif

                                        </td>


                                        {{-- Health Status --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1
                                                       text-xs font-semibold
                                                       {{ $statusClasses }}"
                                            >
                                                {{ str_replace(
                                                    '_',
                                                    ' ',
                                                    ucfirst($healthRecord->health_status)
                                                ) }}
                                            </span>

                                        </td>


                                        {{-- Date --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">

                                            {{ $healthRecord->record_date?->format('M d, Y') ?? '—' }}

                                        </td>


                                        {{-- Diagnosis --}}
                                        <td class="max-w-xs px-6 py-4">

                                            <p class="truncate text-sm text-gray-700">

                                                {{ $healthRecord->diagnosis ?: '—' }}

                                            </p>

                                        </td>


                                        {{-- Actions --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-right">

                                            <div class="flex justify-end gap-3">

                                                <a
                                                    href="{{ route(
                                                        'health-records.show',
                                                        $healthRecord
                                                    ) }}"
                                                    class="text-sm font-medium text-indigo-600
                                                           hover:text-indigo-800"
                                                >
                                                    View
                                                </a>


                                                <a
                                                    href="{{ route(
    'health-records.history',
    $healthRecord->swine
) }}"
                                                    class="text-sm font-medium text-gray-600
                                                           hover:text-gray-900"
                                                >
                                                    History
                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- Mobile Cards --}}
                    <div class="divide-y divide-gray-200 md:hidden">

                        @foreach ($healthRecords as $healthRecord)

                            @php

                                $statusClasses = match (
                                    $healthRecord->health_status
                                ) {

                                    'healthy' =>
                                        'bg-green-100 text-green-700',

                                    'under_observation' =>
                                        'bg-yellow-100 text-yellow-700',

                                    'sick' =>
                                        'bg-red-100 text-red-700',

                                    'recovering' =>
                                        'bg-blue-100 text-blue-700',

                                    default =>
                                        'bg-gray-100 text-gray-700',

                                };

                            @endphp


                            <div class="px-5 py-5">

                                <div class="flex items-start justify-between gap-4">

                                    <div>

                                        <h4 class="font-bold text-gray-900">
                                            {{ $healthRecord->swine?->tag_number ?? '—' }}
                                        </h4>

                                        @if ($healthRecord->swine?->name)

                                            <p class="text-sm text-gray-500">
                                                {{ $healthRecord->swine->name }}
                                            </p>

                                        @endif

                                    </div>


                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs
                                               font-semibold {{ $statusClasses }}"
                                    >
                                        {{ str_replace(
                                            '_',
                                            ' ',
                                            ucfirst($healthRecord->health_status)
                                        ) }}
                                    </span>

                                </div>


                                <div class="mt-4 space-y-2 text-sm">

                                    <div class="flex justify-between gap-4">

                                        <span class="text-gray-500">
                                            Latest Record
                                        </span>

                                        <span class="font-medium text-gray-900">
                                            {{ $healthRecord->record_type }}
                                        </span>

                                    </div>


                                    <div class="flex justify-between gap-4">

                                        <span class="text-gray-500">
                                            Date
                                        </span>

                                        <span class="text-gray-900">
                                            {{ $healthRecord->record_date?->format('M d, Y') ?? '—' }}
                                        </span>

                                    </div>


                                    <div>

                                        <span class="text-gray-500">
                                            Diagnosis
                                        </span>

                                        <p class="mt-1 text-gray-900">
                                            {{ $healthRecord->diagnosis ?: '—' }}
                                        </p>

                                    </div>

                                </div>


                                <div class="mt-4 flex gap-4 border-t border-gray-100 pt-4">

                                    <a
                                        href="{{ route(
                                            'health-records.show',
                                            $healthRecord
                                        ) }}"
                                        class="text-sm font-medium text-indigo-600"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="{{ route(
    'health-records.history',
    $healthRecord->swine
) }}"
                                        class="text-sm font-medium text-gray-600"
                                    >
                                        Health History
                                    </a>

                                </div>

                            </div>

                        @endforeach

                    </div>


                    {{-- Pagination --}}
                    <div class="border-t border-gray-200 px-6 py-4">

                        {{ $healthRecords->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>