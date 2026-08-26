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


            {{-- Select Swine --}}
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

                    <form method="GET" action="{{ route('growth-monitoring.index') }}"
                        class="flex flex-col gap-4 sm:flex-row sm:items-end">

                        <div class="flex-1">

                            <label for="swine_id" class="block text-sm font-medium text-gray-700">
                                Swine
                            </label>

                            <select id="swine_id" name="swine_id" class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500">

                                <option value="">
                                    Select swine
                                </option>

                                @foreach ($swines as $swine)

                                    <option value="{{ $swine->id }}" @selected(
                                        $selectedSwine?->id == $swine->id
                                    )>

                                        {{ $swine->tag_number }}

                                        @if ($swine->name)
                                            — {{ $swine->name }}
                                        @endif

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <button type="submit" class="rounded-lg bg-[#3368A0] px-5 py-2.5
                                   text-sm font-semibold text-white shadow-sm
                                   hover:bg-[#28557F]">
                            View Growth
                        </button>

                    </form>

                </div>

            </div>


            @if ($selectedSwine)


                    {{-- Swine Information --}}
                    <div class="overflow-hidden rounded-xl bg-white
                                    shadow-sm ring-1 ring-gray-200">

                        <div class="px-6 py-6">

                            <div class="flex flex-col gap-5 sm:flex-row
                                            sm:items-center sm:justify-between">

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


                                <div class="text-sm sm:text-right">

                                    <p class="text-xs uppercase tracking-wide text-gray-500">
                                        Breed
                                    </p>

                                    <p class="mt-1 font-semibold text-gray-900">
                                        {{ $selectedSwine->breed ?? '—' }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Summary Cards --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">


                        {{-- Current Weight --}}
                        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                            <p class="text-sm font-medium text-gray-500">
                                Current Weight
                            </p>

                            <p class="mt-2 text-3xl font-bold text-gray-900">

                                {{ $currentWeight !== null
                ? number_format($currentWeight, 2)
                : '—' }}

                                @if ($currentWeight !== null)
                                    <span class="text-sm font-medium text-gray-500">
                                        kg
                                    </span>
                                @endif

                            </p>

                        </div>


                        {{-- Previous Weight --}}
                        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                            <p class="text-sm font-medium text-gray-500">
                                Previous Weight
                            </p>

                            <p class="mt-2 text-3xl font-bold text-gray-900">

                                {{ $previousWeight !== null
                ? number_format($previousWeight, 2)
                : '—' }}

                                @if ($previousWeight !== null)
                                    <span class="text-sm font-medium text-gray-500">
                                        kg
                                    </span>
                                @endif

                            </p>

                        </div>


                        {{-- Weight Gain --}}
                        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                            <p class="text-sm font-medium text-gray-500">
                                Weight Gain
                            </p>

                            <p class="mt-2 text-3xl font-bold
                                    {{ $totalWeightGain !== null && $totalWeightGain >= 0
                ? 'text-green-600'
                : 'text-red-600' }}">

                                {{ $totalWeightGain !== null
                ? number_format($totalWeightGain, 2)
                : '—' }}

                                @if ($totalWeightGain !== null)
                                    <span class="text-sm font-medium text-gray-500">
                                        kg
                                    </span>
                                @endif

                            </p>

                        </div>


                        {{-- Average Daily Gain --}}
                        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                            <p class="text-sm font-medium text-gray-500">
                                Average Daily Gain
                            </p>

                            <p class="mt-2 text-3xl font-bold text-[#3368A0]">

                                {{ $averageDailyGain !== null
                ? number_format($averageDailyGain, 2)
                : '—' }}

                                @if ($averageDailyGain !== null)
                                    <span class="text-sm font-medium text-gray-500">
                                        kg/day
                                    </span>
                                @endif

                            </p>

                        </div>

                    </div>


                    {{-- Weight Chart --}}
                    <div class="overflow-hidden rounded-xl bg-white
                                    shadow-sm ring-1 ring-gray-200">

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

                                    <p class="text-sm font-medium text-gray-900">
                                        Not enough data for a growth chart.
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        At least two weight records are required.
                                    </p>

                                </div>

                            @endif

                        </div>

                    </div>


                    {{-- Weight History --}}
                    <div class="overflow-hidden rounded-xl bg-white
                                    shadow-sm ring-1 ring-gray-200">

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

                                        <a href="{{ route('weight-records.create', [
                                'swine_id' => $selectedSwine->id
                            ]) }}" class="mt-4 inline-flex rounded-lg bg-[#3368A0]
                                                           px-4 py-2 text-sm font-semibold text-white
                                                           hover:bg-[#28557F]">
                                            Add Weight Record
                                        </a>

                                    </div>

                        @else

                            <div class="overflow-x-auto">

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

                                        @php

                                            /*
                                            |--------------------------------------------------------------------------
                                            | Calculate changes in chronological order
                                            |--------------------------------------------------------------------------
                                            */

                                            $chronologicalRecords = $weightRecords
                                                ->sortBy('record_date')
                                                ->values();

                                            $weightChanges = [];

                                            $previousWeight = null;

                                            foreach ($chronologicalRecords as $chronologicalRecord) {

                                                if ($previousWeight !== null) {

                                                    $weightChanges[$chronologicalRecord->id] =
                                                        (float) $chronologicalRecord->weight - $previousWeight;

                                                } else {

                                                    $weightChanges[$chronologicalRecord->id] = null;

                                                }

                                                $previousWeight = (float) $chronologicalRecord->weight;
                                            }

                                        @endphp


                                        {{-- Display newest first --}}
                                        @foreach ($weightRecords->sortByDesc('record_date') as $record)

                                            @php
                                                $change = $weightChanges[$record->id] ?? null;
                                            @endphp

                                            <tr class="hover:bg-gray-50">

                                                {{-- Date --}}
                                                <td class="whitespace-nowrap px-6 py-4">

                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $record->record_date?->format('M d, Y') }}
                                                    </p>

                                                    <p class="text-xs text-gray-500">
                                                        {{ $record->record_date?->format('l') }}
                                                    </p>

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

                                                                        <span class="text-sm font-semibold
                                                            {{ $change >= 0
                                                        ? 'text-green-600'
                                                        : 'text-red-600' }}">

                                                                            {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2) }}
                                                                            kg

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
                                                        {{ $record->recordedBy?->name ?? 'Unknown' }}
                                                    </span>

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        @endif

                    </div>


            @else

                {{-- Empty State --}}
                <div class="rounded-xl bg-white px-6 py-16 text-center
                                shadow-sm ring-1 ring-gray-200">

                    <h3 class="text-lg font-semibold text-gray-900">
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


    {{-- Chart.js --}}
    @if ($selectedSwine && $weightRecords->count() >= 2)

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>

            const chartLabels = @json($chartLabels);

            const chartWeights = @json($chartWeights);

            const ctx = document
                .getElementById('weightGrowthChart');

            new Chart(ctx, {

                type: 'line',

                data: {

                    labels: chartLabels,

                    datasets: [{

                        label: 'Weight (kg)',

                        data: chartWeights,

                        tension: 0.3,

                        borderWidth: 2,

                        pointRadius: 4,

                        fill: false

                    }]

                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

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
                        }

                    }

                }

            });

        </script>

    @endif

</x-app-layout>