<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- System Overview --}}
            <div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-2">
                    <div class="p-6 text-gray-900">
                        <div>

                            <h3 class="text-lg font-semibold text-gray-900">
                                System Overview
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Current overview of farms, swine, and health records.
                            </p>

                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 pb-6">

                    {{-- Total Swine --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm font-medium text-gray-500">
                                    Total Swine
                                </p>

                                <p class="mt-2 text-3xl font-bold text-gray-900">
                                    {{ $totalSwine }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Registered swine
                                </p>

                            </div>

                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100">

                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 12h16M7 8h10M7 16h10" />

                                </svg>

                            </div>

                        </div>

                    </div>


                    {{-- Active Swine --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm font-medium text-gray-500">
                                    Active Swine
                                </p>

                                <p class="mt-2 text-3xl font-bold text-green-600">
                                    {{ $activeSwine }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Currently active
                                </p>

                            </div>

                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-green-100">

                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />

                                </svg>

                            </div>

                        </div>

                    </div>


                    {{-- Total Farms --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm font-medium text-gray-500">
                                    Total Farms
                                </p>

                                <p class="mt-2 text-3xl font-bold text-gray-900">
                                    {{ $totalFarms }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Registered farms
                                </p>

                            </div>

                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-100">

                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />

                                </svg>

                            </div>

                        </div>

                    </div>


                    {{-- Health Records --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm font-medium text-gray-500">
                                    Health Records
                                </p>

                                <p class="mt-2 text-3xl font-bold text-purple-600">
                                    {{ $totalHealthRecords }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Recorded health events
                                </p>

                            </div>

                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-purple-100">

                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />

                                </svg>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- Vaccination Monitoring --}}
            <div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-2">
                    <div class="p-6 text-gray-900">
                        <div>

                            <h3 class="text-lg font-semibold text-gray-900">
                                Vaccination Monitoring
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Monitor upcoming and overdue vaccinations across your swine records.
                            </p>

                        </div>
                    </div>
                </div>



                {{-- Vaccination Summary --}}
                @php
                    $vaccinationRecords = \App\Models\HealthRecord::query()
                        ->where('record_type', 'Vaccination')
                        ->whereNotNull('next_due_date')
                        ->get();

                    $totalVaccinations = $vaccinationRecords->count();

                    $overdueVaccinations = $vaccinationRecords
                        ->filter(fn($record) => $record->next_due_date->isPast())
                        ->count();

                    $dueTodayVaccinations = $vaccinationRecords
                        ->filter(fn($record) => $record->next_due_date->isToday())
                        ->count();

                    $dueSoonVaccinations = $vaccinationRecords
                        ->filter(function ($record) {
                            return $record->next_due_date->isFuture()
                                && now()->diffInDays($record->next_due_date) <= 7;
                        })
                        ->count();
                @endphp


                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                    {{-- Total Vaccinations --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    Total Vaccinations
                                </p>

                                <p class="mt-2 text-3xl font-bold text-gray-900">
                                    {{ $totalVaccinations }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    With scheduled due dates
                                </p>
                            </div>

                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100">
                                <span class="text-lg text-blue-600">
                                    💉
                                </span>
                            </div>

                        </div>

                    </div>


                    {{-- Overdue --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    Overdue
                                </p>

                                <p class="mt-2 text-3xl font-bold text-red-600">
                                    {{ $overdueVaccinations }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Require attention
                                </p>
                            </div>

                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-red-100">
                                <span class="text-lg text-red-600">
                                    ⚠
                                </span>
                            </div>

                        </div>

                    </div>


                    {{-- Due Today --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    Due Today
                                </p>

                                <p class="mt-2 text-3xl font-bold text-orange-600">
                                    {{ $dueTodayVaccinations }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Scheduled for today
                                </p>
                            </div>

                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-orange-100">
                                <span class="text-lg text-orange-600">
                                    !
                                </span>
                            </div>

                        </div>

                    </div>


                    {{-- Due Within 7 Days --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    Due Within 7 Days
                                </p>

                                <p class="mt-2 text-3xl font-bold text-yellow-600">
                                    {{ $dueSoonVaccinations }}
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Upcoming vaccinations
                                </p>
                            </div>

                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-yellow-100">
                                <span class="text-lg text-yellow-600">
                                    ◷
                                </span>
                            </div>

                        </div>

                    </div>

                </div>


                {{-- Vaccination Alerts --}}
                <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                            <div>

                                <h3 class="text-lg font-semibold text-gray-900">
                                    Vaccination Alerts
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Overdue and upcoming vaccinations requiring attention.
                                </p>

                            </div>

                            <a href="{{ route('health-records.index') }}"
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">

                                View Health Records

                            </a>

                        </div>

                    </div>


                    @if ($vaccinationAlerts->isEmpty())

                        <div class="px-6 py-10 text-center">

                            <p class="text-sm font-medium text-gray-900">
                                No vaccination alerts
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                There are no overdue or upcoming vaccinations within the next 7 days.
                            </p>

                        </div>

                    @else

                        <div class="overflow-x-auto">

                            <table class="min-w-full divide-y divide-gray-200">

                                <thead class="bg-gray-50">

                                    <tr>

                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            Swine
                                        </th>

                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            Vaccine
                                        </th>

                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            Next Due
                                        </th>

                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            Status
                                        </th>

                                        <th
                                            class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody class="divide-y divide-gray-200 bg-white">

                                    @foreach ($vaccinationAlerts as $vaccination)

                                        @php

                                            if ($vaccination->next_due_date->isPast()) {

                                                $status = 'Overdue';
                                                $statusClasses = 'bg-red-100 text-red-700';

                                            } elseif ($vaccination->next_due_date->isToday()) {

                                                $status = 'Due Today';
                                                $statusClasses = 'bg-orange-100 text-orange-700';

                                            } else {

                                                $days = now()->diffInDays(
                                                    $vaccination->next_due_date
                                                );

                                                $status = 'Due in ' . $days . ' day' . ($days == 1 ? '' : 's');
                                                $statusClasses = 'bg-yellow-100 text-yellow-700';

                                            }

                                        @endphp


                                        <tr class="hover:bg-gray-50">

                                            {{-- Swine --}}
                                            <td class="whitespace-nowrap px-6 py-4">

                                                <p class="text-sm font-semibold text-gray-900">
                                                    {{ $vaccination->swine?->tag_number ?? '—' }}
                                                </p>

                                            </td>


                                            {{-- Vaccine --}}
                                            <td class="px-6 py-4">

                                                <p class="text-sm text-gray-900">
                                                    {{ $vaccination->vaccine_name ?: '—' }}
                                                </p>

                                            </td>


                                            {{-- Due Date --}}
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">

                                                {{ $vaccination->next_due_date->format('M d, Y') }}

                                            </td>


                                            {{-- Status --}}
                                            <td class="whitespace-nowrap px-6 py-4">

                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">

                                                    {{ $status }}

                                                </span>

                                            </td>


                                            {{-- Action --}}
                                            <td class="whitespace-nowrap px-6 py-4 text-right">

                                                <a href="{{ route('health-records.show', $vaccination) }}"
                                                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">

                                                    View

                                                </a>

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @endif

                </div>

                {{-- Swine Population Overview --}}
                <div class="mt-6">

                    <div class="mb-4">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Swine Population Overview
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Distribution of registered swine by status and breed.
                        </p>

                    </div>


                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                        {{-- Swine by Status --}}
                        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">

                            <div class="mb-5">

                                <h4 class="font-semibold text-gray-900">
                                    Swine by Status
                                </h4>

                                <p class="mt-1 text-sm text-gray-500">
                                    Current status of registered swine.
                                </p>

                            </div>

                            <div class="relative h-72">

                                <canvas id="swineStatusChart"></canvas>

                            </div>

                        </div>


                        {{-- Swine by Breed --}}
                        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">

                            <div class="mb-5">

                                <h4 class="font-semibold text-gray-900">
                                    Swine by Breed
                                </h4>

                                <p class="mt-1 text-sm text-gray-500">
                                    Distribution of swine according to breed.
                                </p>

                            </div>

                            <div class="relative h-72">

                                <canvas id="swineBreedChart"></canvas>

                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </div>

        @push('scripts')

            <script>
                document.addEventListener('DOMContentLoaded', function () {

                    /*
                    |--------------------------------------------------------------------------
                    | Swine by Status
                    |--------------------------------------------------------------------------
                    */

                    const statusCanvas = document.getElementById('swineStatusChart');

                    if (statusCanvas) {

                        new Chart(statusCanvas, {

                            type: 'doughnut',

                            data: {

                                labels: @json(
                                    $swineByStatus->map(
                                        fn($item) => str_replace(
                                            '_',
                                            ' ',
                                            ucfirst($item->status)
                                        )
                                    )
                                ),

                                datasets: [{

                                    data: @json(
                                        $swineByStatus->pluck('total')
                                    ),

                                    borderWidth: 1

                                }]

                            },

                            options: {

                                responsive: true,

                                maintainAspectRatio: false,

                                plugins: {

                                    legend: {

                                        position: 'bottom'

                                    }

                                }

                            }

                        });

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Swine by Breed
                    |--------------------------------------------------------------------------
                    */

                    const breedCanvas = document.getElementById('swineBreedChart');

                    if (breedCanvas) {

                        new Chart(breedCanvas, {

                            type: 'bar',

                            data: {

                                labels: @json(
                                    $swineByBreed->map(
                                        fn($item) => $item->breed ?: 'Unknown'
                                    )
                                ),

                                datasets: [{

                                    label: 'Number of Swine',

                                    data: @json(
                                        $swineByBreed->pluck('total')
                                    ),

                                    borderWidth: 1

                                }]

                            },

                            options: {

                                responsive: true,

                                maintainAspectRatio: false,

                                scales: {

                                    y: {

                                        beginAtZero: true,

                                        ticks: {

                                            precision: 0

                                        }

                                    }

                                },

                                plugins: {

                                    legend: {

                                        display: false

                                    }

                                }

                            }

                        });

                    }

                });
            </script>

        @endpush
</x-app-layout>