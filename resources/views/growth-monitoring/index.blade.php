<x-app-layout>

    <x-slot name="header">

        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Growth Monitoring
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Monitor swine weight progression and growth performance.
            </p>
        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">


            {{-- ================================================================
                SELECT SWINE
            ================================================================= --}}

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Select Swine
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Select a swine to view its growth monitoring information.
                    </p>

                </div>


                <div class="px-6 py-6">

                    <form
                        method="GET"
                        action="{{ route('growth-monitoring.index') }}"
                        class="flex flex-col gap-4 sm:flex-row sm:items-end"
                    >

                        <div class="flex-1">

                            <label
                                for="swine_id"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Swine
                            </label>

                            <select
                                id="swine_id"
                                name="swine_id"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
                            >

                                <option value="">
                                    Select swine
                                </option>

                                @foreach ($swines as $swine)

                                    <option
                                        value="{{ $swine->id }}"
                                        @selected($selectedSwine?->id == $swine->id)
                                    >
                                        {{ $swine->tag_number }}

                                        @if ($swine->name)
                                            — {{ $swine->name }}
                                        @endif
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <button
                            type="submit"
                            class="rounded-lg bg-[#3368A0] px-5 py-2.5
                                   text-sm font-semibold text-white shadow-sm
                                   hover:bg-[#28557F]"
                        >
                            View Growth
                        </button>

                    </form>

                </div>

            </div>


            @if ($selectedSwine)


                {{-- ============================================================
                    SWINE INFORMATION
                ============================================================= --}}

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="px-6 py-6">

                        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                            <div>

                                <p class="text-sm font-medium text-gray-500">
                                    Swine Identification
                                </p>

                                <h1 class="mt-1 text-3xl font-bold text-gray-900">
                                    {{ $selectedSwine->tag_number }}
                                </h1>

                                @if ($selectedSwine->name)

                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $selectedSwine->name }}
                                    </p>

                                @endif

                            </div>


                            <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:text-right">

                                {{-- Breed --}}
                                <div>

                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                        Breed
                                    </p>

                                    <p class="mt-1 font-semibold text-gray-900">
                                        {{ $selectedSwine->breed ?? '—' }}
                                    </p>

                                </div>


                                {{-- Current Location --}}
                                <div>

                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                        Current Location
                                    </p>

                                    <p class="mt-1 font-semibold text-gray-900">
                                        {{ $selectedSwine->currentLocation?->name ?? '—' }}
                                    </p>

                                </div>


                                {{-- Status --}}
                                <div>

                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                        Status
                                    </p>

                                    @php
                                        $statusClasses = match ($selectedSwine->status) {
                                            'active' => 'bg-green-100 text-green-700',
                                            'inactive' => 'bg-gray-100 text-gray-700',
                                            'sold' => 'bg-blue-100 text-blue-700',
                                            'deceased' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp

                                    <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ $statusClasses }}">
                                        {{ str_replace('_', ' ', $selectedSwine->status ?? 'Unknown') }}
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ============================================================
                    SUMMARY CARDS
                ============================================================= --}}

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">


                    {{-- Current Weight --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <p class="text-sm font-medium text-gray-500">
                            Current Weight
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900">

                            @if ($currentWeight !== null)

                                {{ number_format($currentWeight, 2) }}

                                <span class="text-sm font-medium text-gray-500">
                                    kg
                                </span>

                            @else

                                —

                            @endif

                        </p>

                    </div>


                    {{-- Previous Weight --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <p class="text-sm font-medium text-gray-500">
                            Previous Weight
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900">

                            @if ($previousWeight !== null)

                                {{ number_format($previousWeight, 2) }}

                                <span class="text-sm font-medium text-gray-500">
                                    kg
                                </span>

                            @else

                                —

                            @endif

                        </p>

                    </div>


                    {{-- Weight Gain / Loss --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <p class="text-sm font-medium text-gray-500">
                            Weight Change
                        </p>

                        @if ($totalWeightGain !== null)

                            @if ($totalWeightGain >= 0)

                                <p class="mt-2 text-3xl font-bold text-green-600">
                                    +{{ number_format($totalWeightGain, 2) }}

                                    <span class="text-sm font-medium text-gray-500">
                                        kg
                                    </span>
                                </p>

                                <p class="mt-1 text-xs text-green-600">
                                    Weight gain since previous record
                                </p>

                            @else

                                <p class="mt-2 text-3xl font-bold text-red-600">
                                    {{ number_format($totalWeightGain, 2) }}

                                    <span class="text-sm font-medium text-gray-500">
                                        kg
                                    </span>
                                </p>

                                <p class="mt-1 text-xs text-red-600">
                                    Weight loss since previous record
                                </p>

                            @endif

                        @else

                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                —
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                Requires at least two records
                            </p>

                        @endif

                    </div>


                    {{-- Average Daily Gain --}}
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <p class="text-sm font-medium text-gray-500">
                            Average Daily Gain
                        </p>

                        @if ($averageDailyGain !== null)

                            <p class="mt-2 text-3xl font-bold text-[#3368A0]">
                                {{ number_format($averageDailyGain, 2) }}

                                <span class="text-sm font-medium text-gray-500">
                                    kg/day
                                </span>
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                Based on the two latest measurements
                            </p>

                        @else

                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                —
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                Requires two records on different dates
                            </p>

                        @endif

                    </div>

                </div>


                {{-- ============================================================
                    WEIGHT PROGRESSION CHART
                ============================================================= --}}

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Weight Progression
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Recorded weight measurements over time.
                        </p>

                    </div>


                    <div class="p-6">

                        @if ($weightRecords->count() >= 2)

                            <div class="h-80">

                                <canvas id="weightGrowthChart"></canvas>

                            </div>

                        @else

                            <div class="py-12 text-center">

                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">

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
                                            d="M11 3v18m4-14v10m4-6v6M7 7v10m-4-6v6"
                                        />
                                    </svg>

                                </div>

                                <p class="mt-4 text-sm font-medium text-gray-900">
                                    Not enough data for a growth chart.
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    At least two weight records are required.
                                </p>

                            </div>

                        @endif

                    </div>

                </div>


                {{-- ============================================================
                    WEIGHT HISTORY
                ============================================================= --}}

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Weight History
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            All recorded weight measurements for this swine.
                        </p>

                    </div>


                    @if ($weightRecords->isEmpty())

                        <div class="px-6 py-12 text-center">

                            <p class="text-sm font-medium text-gray-900">
                                No weight records found.
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                Record at least one weight measurement to begin monitoring growth.
                            </p>

                            @can('record-weight')

                                <a
                                    href="{{ route('weight-records.create', [
                                        'swine_id' => $selectedSwine->id
                                    ]) }}"
                                    class="mt-4 inline-flex rounded-lg bg-[#3368A0]
                                           px-4 py-2 text-sm font-semibold text-white
                                           hover:bg-[#28557F]"
                                >
                                    Add Weight Record
                                </a>

                            @endcan

                        </div>

                    @else

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | Calculate chronological weight changes
                            |--------------------------------------------------------------------------
                            */

                            $chronologicalRecords = $weightRecords
                                ->sortBy(function ($record) {
                                    return $record->record_date?->timestamp ?? 0;
                                })
                                ->values();

                            $weightChanges = [];

                            $previousRecordedWeight = null;

                            foreach ($chronologicalRecords as $chronologicalRecord) {

                                if ($previousRecordedWeight !== null) {

                                    $weightChanges[$chronologicalRecord->id] =
                                        (float) $chronologicalRecord->weight
                                        - $previousRecordedWeight;

                                } else {

                                    $weightChanges[$chronologicalRecord->id] = null;

                                }

                                $previousRecordedWeight =
                                    (float) $chronologicalRecord->weight;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Display newest record first
                            |--------------------------------------------------------------------------
                            */

                            $displayRecords = $weightRecords
                                ->sortByDesc(function ($record) {
                                    return $record->record_date?->timestamp ?? 0;
                                })
                                ->values();

                        @endphp


                        {{-- Desktop Table --}}
                        <div class="hidden overflow-x-auto md:block">

                            <table class="min-w-full divide-y divide-gray-200">

                                <thead class="bg-gray-50">

                                    <tr>

                                        <th class="px-6 py-3 text-left text-xs
                                                   font-semibold uppercase tracking-wide
                                                   text-gray-500">
                                            Date
                                        </th>

                                        <th class="px-6 py-3 text-left text-xs
                                                   font-semibold uppercase tracking-wide
                                                   text-gray-500">
                                            Weight
                                        </th>

                                        <th class="px-6 py-3 text-left text-xs
                                                   font-semibold uppercase tracking-wide
                                                   text-gray-500">
                                            Change
                                        </th>

                                        <th class="px-6 py-3 text-left text-xs
                                                   font-semibold uppercase tracking-wide
                                                   text-gray-500">
                                            Recorded By
                                        </th>

                                    </tr>

                                </thead>


                                <tbody class="divide-y divide-gray-100 bg-white">

                                    @foreach ($displayRecords as $record)

                                        @php
                                            $change = $weightChanges[$record->id] ?? null;
                                        @endphp

                                        <tr class="hover:bg-gray-50">

                                            {{-- Date --}}
                                            <td class="whitespace-nowrap px-6 py-4">

                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $record->record_date?->format('M d, Y') ?? '—' }}
                                                </p>

                                                @if ($record->record_date)

                                                    <p class="text-xs text-gray-500">
                                                        {{ $record->record_date->format('l') }}
                                                    </p>

                                                @endif

                                            </td>


                                            {{-- Weight --}}
                                            <td class="whitespace-nowrap px-6 py-4">

                                                <span class="text-sm font-semibold text-gray-900">
                                                    {{ number_format((float) $record->weight, 2) }}
                                                    kg
                                                </span>

                                            </td>


                                            {{-- Change --}}
                                            <td class="whitespace-nowrap px-6 py-4">

                                                @if ($change !== null)

                                                    @if ($change > 0)

                                                        <span class="text-sm font-semibold text-green-600">
                                                            +{{ number_format($change, 2) }} kg
                                                        </span>

                                                    @elseif ($change < 0)

                                                        <span class="text-sm font-semibold text-red-600">
                                                            {{ number_format($change, 2) }} kg
                                                        </span>

                                                    @else

                                                        <span class="text-sm font-semibold text-gray-500">
                                                            0.00 kg
                                                        </span>

                                                    @endif

                                                @else

                                                    <span class="text-sm text-gray-400">
                                                        —
                                                    </span>

                                                @endif

                                            </td>


                                            {{-- Recorded By --}}
                                            <td class="whitespace-nowrap px-6 py-4">

                                                <span class="text-sm text-gray-700">
                                                    {{ $record->recordedBy?->name ?? 'Unknown' }}
                                                </span>

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>


                        {{-- Mobile Cards --}}
                        <div class="divide-y divide-gray-100 md:hidden">

                            @foreach ($displayRecords as $record)

                                @php
                                    $change = $weightChanges[$record->id] ?? null;
                                @endphp

                                <div class="px-5 py-5">

                                    <div class="flex items-start justify-between gap-4">

                                        <div>

                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $record->record_date?->format('M d, Y') ?? '—' }}
                                            </p>

                                            @if ($record->record_date)

                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $record->record_date->format('l') }}
                                                </p>

                                            @endif

                                        </div>


                                        <div class="text-right">

                                            <p class="text-lg font-bold text-gray-900">
                                                {{ number_format((float) $record->weight, 2) }}
                                                kg
                                            </p>

                                            @if ($change !== null)

                                                @if ($change > 0)

                                                    <p class="text-xs font-semibold text-green-600">
                                                        +{{ number_format($change, 2) }} kg
                                                    </p>

                                                @elseif ($change < 0)

                                                    <p class="text-xs font-semibold text-red-600">
                                                        {{ number_format($change, 2) }} kg
                                                    </p>

                                                @else

                                                    <p class="text-xs font-semibold text-gray-500">
                                                        0.00 kg
                                                    </p>

                                                @endif

                                            @endif

                                        </div>

                                    </div>


                                    <div class="mt-3">

                                        <p class="text-xs text-gray-500">
                                            Recorded By
                                        </p>

                                        <p class="mt-1 text-sm text-gray-700">
                                            {{ $record->recordedBy?->name ?? 'Unknown' }}
                                        </p>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endif

                </div>


            @else


                {{-- ============================================================
                    EMPTY STATE
                ============================================================= --}}

                <div class="rounded-xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-gray-200">

                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">

                        <svg
                            class="h-7 w-7 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 3v18m4-14v10m4-6v6M7 7v10m-4-6v6"
                            />
                        </svg>

                    </div>

                    <h3 class="mt-4 text-lg font-semibold text-gray-900">
                        Select a swine
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Select a swine above to view its weight progression
                        and growth performance.
                    </p>

                </div>


            @endif

        </div>

    </div>


    {{-- ================================================================
        CHART.JS
    ================================================================= --}}

    @if ($selectedSwine && $weightRecords->count() >= 2)

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>

            const chartLabels = @json($chartLabels);
            const chartWeights = @json($chartWeights);

            const chartCanvas = document.getElementById('weightGrowthChart');

            if (chartCanvas) {

                new Chart(chartCanvas, {

                    type: 'line',

                    data: {

                        labels: chartLabels,

                        datasets: [{

                            label: 'Weight (kg)',

                            data: chartWeights,

                            tension: 0.3,

                            borderWidth: 2,

                            pointRadius: 4,

                            pointHoverRadius: 6,

                            fill: false

                        }]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        interaction: {
                            mode: 'index',
                            intersect: false
                        },

                        scales: {

                            y: {

                                beginAtZero: true,

                                title: {
                                    display: true,
                                    text: 'Weight (kg)'
                                }

                            },

                            x: {

                                title: {
                                    display: true,
                                    text: 'Record Date'
                                }

                            }

                        },

                        plugins: {

                            legend: {
                                display: true
                            },

                            tooltip: {

                                callbacks: {

                                    label: function (context) {

                                        return 'Weight: ' +
                                            Number(context.parsed.y).toFixed(2) +
                                            ' kg';

                                    }

                                }

                            }

                        }

                    }

                });

            }

        </script>

    @endif

</x-app-layout>