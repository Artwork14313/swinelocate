<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Reports &amp; Analytics</h2>
                <p class="mt-1 text-sm text-gray-500">Farm population, health activity, movements, and weight trends.</p>
            </div>
            <form method="GET" action="{{ route('reports.export') }}" class="flex flex-col gap-2 sm:flex-row">
                <input type="hidden" name="farm_id" value="{{ $selectedFarmId }}">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <label class="sr-only" for="export_type">CSV data</label>
                <select id="export_type" name="type" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    <option value="swine">Swine summary</option>
                    <option value="health">Health records</option>
                    <option value="movements">Movement history</option>
                    <option value="weights">Weight records</option>
                </select>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#3368A0] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#28557F]">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3" />
                    </svg>
                    Export CSV
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <form method="GET" action="{{ route('reports.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
                    <div>
                        <label for="farm_id" class="block text-sm font-medium text-gray-700">Farm</label>
                        <select id="farm_id" name="farm_id" class="mt-1.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                            <option value="">All farms</option>
                            @foreach ($farms as $farm)
                                <option value="{{ $farm->id }}" @selected((string) $selectedFarmId === (string) $farm->id)>{{ $farm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">From</label>
                        <input id="start_date" name="start_date" type="date" value="{{ $startDate }}" class="mt-1.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">To</label>
                        <input id="end_date" name="end_date" type="date" value="{{ $endDate }}" class="mt-1.5 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                    </div>
                    <button type="submit" class="inline-flex justify-center rounded-md bg-[#3368A0] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#28557F]">
                        Apply filters
                    </button>
                </form>
                @if ($errors->any())
                    <p role="alert" class="mt-3 text-sm text-red-700">{{ $errors->first() }}</p>
                @endif
            </section>

            <section aria-label="Report summary" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <article class="rounded-lg border-l-4 border-blue-700 bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Total swine</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($totalSwine) }}</p>
                </article>
                <article class="rounded-lg border-l-4 border-emerald-600 bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Active swine</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($activeSwine) }}</p>
                </article>
                <article class="rounded-lg border-l-4 border-amber-500 bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Health records</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($totalHealthRecords) }}</p>
                    <p class="mt-1 text-xs text-gray-500">Selected date range</p>
                </article>
                <article class="rounded-lg border-l-4 border-teal-600 bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Movements</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($totalMovements) }}</p>
                    <p class="mt-1 text-xs text-gray-500">Selected date range</p>
                </article>
                <article class="rounded-lg border-l-4 border-rose-600 bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">Average recorded weight</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">
                        {{ $averageWeight !== null ? number_format((float) $averageWeight, 2) : '—' }}
                        @if ($averageWeight !== null)<span class="text-base font-medium text-gray-500">kg</span>@endif
                    </p>
                    <p class="mt-1 text-xs text-gray-500">Selected date range</p>
                </article>
            </section>

            <section id="analytics" class="grid gap-6 lg:grid-cols-2">
                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">Population by status</h3>
                    <div class="mt-5 space-y-4">
                        @forelse ($populationByStatus as $statusRow)
                            @php($populationPercent = $totalSwine ? (int) round($statusRow->total / $totalSwine * 100) : 0)
                            <div>
                                <div class="mb-1.5 flex items-center justify-between text-sm">
                                    <span class="capitalize text-gray-700">{{ str_replace('_', ' ', $statusRow->status) }}</span>
                                    <span class="font-medium text-gray-900">{{ number_format($statusRow->total) }} <span class="text-gray-500">({{ $populationPercent }}%)</span></span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-blue-700" style="width: {{ $populationPercent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm text-gray-500">No swine found for this farm.</p>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">Health records by status</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $startDate }} to {{ $endDate }}</p>
                    <div class="mt-4 divide-y divide-gray-100">
                        @forelse ($healthByStatus as $healthRow)
                            <div class="flex items-center justify-between py-3 text-sm">
                                <span class="capitalize text-gray-700">{{ str_replace('_', ' ', $healthRow->health_status) }}</span>
                                <span class="font-semibold text-gray-900">{{ number_format($healthRow->total) }}</span>
                            </div>
                        @empty
                            <p class="py-8 text-center text-sm text-gray-500">No health records in this date range.</p>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <article class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Recent health activity</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                                <tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Swine</th><th class="px-5 py-3">Record</th><th class="px-5 py-3">Status</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($recentHealthRecords as $record)
                                    <tr>
                                        <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $record->record_date->format('M j, Y') }}</td>
                                        <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $record->swine?->tag_number ?? 'Deleted swine' }}</td>
                                        <td class="px-5 py-3 text-gray-600">{{ $record->record_type }}</td>
                                        <td class="px-5 py-3 capitalize text-gray-600">{{ str_replace('_', ' ', $record->health_status) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No health activity in this date range.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Recent movements</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                                <tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Swine</th><th class="px-5 py-3">From</th><th class="px-5 py-3">To</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($recentMovements as $movement)
                                    <tr>
                                        <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $movement->movement_date->format('M j, Y') }}</td>
                                        <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $movement->swine?->tag_number ?? 'Deleted swine' }}</td>
                                        <td class="px-5 py-3 text-gray-600">{{ $movement->fromLocation?->name_with_address ?? '—' }}</td>
                                        <td class="px-5 py-3 text-gray-600">{{ $movement->toLocation?->name_with_address ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No movements in this date range.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </div>
    </div>
</x-app-layout>