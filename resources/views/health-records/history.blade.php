<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Health History
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Complete veterinary and health history of the swine.
                </p>
            </div>

            <div class="flex gap-2">

                <a
                    href="{{ route('health-records.create', ['swine_id' => $swine->id]) }}"
                    class="inline-flex items-center justify-center rounded-lg
                           bg-indigo-600 px-4 py-2 text-sm font-semibold
                           text-white shadow-sm hover:bg-indigo-700"
                >
                    Add Health Record
                </a>

                <a href="{{ route('health-records.index') }}" class="inline-flex items-center justify-center rounded-lg
                           border border-gray-300 bg-white px-4 py-2
                           text-sm font-semibold text-gray-700
                           shadow-sm hover:bg-gray-50">
                    Back
                </a>

            </div>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">


            {{-- Swine Information --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="px-6 py-6">

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                        <div>

                            <p class="text-sm font-medium text-gray-500">
                                Swine Identification
                            </p>

                            <h1 class="mt-1 text-3xl font-bold text-gray-900">
                                {{ $swine->tag_number }}
                            </h1>

                            @if ($swine->name)

                                <p class="mt-1 text-gray-500">
                                    {{ $swine->name }}
                                </p>

                            @endif

                        </div>


                        <div class="grid grid-cols-2 gap-4 text-sm sm:text-right">

                            <div>

                                <p class="text-xs uppercase tracking-wide text-gray-500">
                                    Farm
                                </p>

                                <p class="mt-1 font-medium text-gray-900">
                                    {{ $swine->farm?->name ?? '—' }}
                                </p>

                            </div>

                            <div>

                                <p class="text-xs uppercase tracking-wide text-gray-500">
                                    Location
                                </p>

                                <p class="mt-1 font-medium text-gray-900">
                                    {{ $swine->currentLocation?->name ?? '—' }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Summary --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                    <p class="text-sm font-medium text-gray-500">
                        Total Health Records
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $healthRecords->count() }}
                    </p>

                </div>


                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                    <p class="text-sm font-medium text-gray-500">
                        Latest Record
                    </p>

                    <p class="mt-2 text-lg font-bold text-gray-900">
                        {{ $healthRecords->first()?->record_date?->format('M d, Y') ?? 'No records' }}
                    </p>

                </div>


                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                    <p class="text-sm font-medium text-gray-500">
                        Current Recorded Health
                    </p>

                    @php
                        $latestStatus = $healthRecords->first()?->health_status;
                    @endphp

                    <p class="mt-2 text-lg font-bold text-gray-900">
                        {{ $latestStatus
                            ? str_replace('_', ' ', ucfirst($latestStatus))
                            : 'No record' }}
                    </p>

                </div>

            </div>


            {{-- Health History --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Veterinary & Health History
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Records are displayed from newest to oldest.
                    </p>

                </div>


                @if ($healthRecords->isEmpty())

                    <div class="px-6 py-12 text-center">

                        <p class="text-sm font-medium text-gray-900">
                            No health records found.
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            No veterinary or health events have been recorded for this swine.
                        </p>

                        <a
                            href="{{ route('health-records.create', ['swine_id' => $swine->id]) }}"
                            class="mt-4 inline-flex rounded-lg bg-indigo-600
                                   px-4 py-2 text-sm font-semibold text-white
                                   hover:bg-indigo-700"
                        >
                            Add First Health Record
                        </a>

                    </div>

                @else

                    <div class="divide-y divide-gray-200">

                        @foreach ($healthRecords as $record)

                            @php

                                $statusClasses = match ($record->health_status) {

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


                            <div class="px-6 py-6">

                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                                    {{-- Date --}}
                                    <div class="lg:w-32">

                                        <p class="text-sm font-bold text-gray-900">
                                            {{ $record->record_date?->format('M d, Y') }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $record->record_date?->format('l') }}
                                        </p>

                                    </div>


                                    {{-- Main information --}}
                                    <div class="flex-1">

                                        <div class="flex flex-wrap items-center gap-2">

                                            <h4 class="font-semibold text-gray-900">
                                                {{ $record->record_type }}
                                            </h4>

                                            <span
                                                class="rounded-full px-2.5 py-1 text-xs
                                                       font-semibold {{ $statusClasses }}"
                                            >
                                                {{ str_replace(
                                                    '_',
                                                    ' ',
                                                    ucfirst($record->health_status)
                                                ) }}
                                            </span>

                                        </div>


                                        @if ($record->diagnosis)

                                            <div class="mt-3">

                                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                    Diagnosis
                                                </p>

                                                <p class="mt-1 text-sm text-gray-700">
                                                    {{ $record->diagnosis }}
                                                </p>

                                            </div>

                                        @endif


                                        @if ($record->treatment)

                                            <div class="mt-3">

                                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                    Treatment
                                                </p>

                                                <p class="mt-1 text-sm text-gray-700">
                                                    {{ $record->treatment }}
                                                </p>

                                            </div>

                                        @endif


                                        @if ($record->observations)

                                            <div class="mt-3">

                                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                    Observations
                                                </p>

                                                <p class="mt-1 text-sm text-gray-700">
                                                    {{ $record->observations }}
                                                </p>

                                            </div>

                                        @endif


                                        <p class="mt-4 text-xs text-gray-500">

                                            Recorded by:
                                            <span class="font-medium text-gray-700">
                                                {{ $record->recordedBy?->name ?? 'Unknown' }}
                                            </span>

                                        </p>

                                    </div>


                                    {{-- View --}}
                                    <div>

                                        <a
                                            href="{{ route(
                                                'health-records.show',
                                                $record
                                            ) }}"
                                            class="inline-flex rounded-lg border
                                                   border-gray-300 bg-white px-3 py-2
                                                   text-sm font-medium text-gray-700
                                                   hover:bg-gray-50"
                                        >
                                            View Details
                                        </a>

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif

            </div>


        </div>

    </div>

</x-app-layout>