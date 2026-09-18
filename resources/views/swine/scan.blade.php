<x-app-layout>
    <div class="py-6">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Swine Traceability
                        </h1>

                        <p class="mt-1 text-sm text-gray-600">
                            QR-based swine identification and traceability record
                        </p>
                    </div>

                    <div>
                        <a href="{{ route('qr.scanner') }}"
                            class="inline-flex items-center rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700">
                            Scan Another QR
                        </a>
                    </div>
                </div>
            </div>


            {{-- Swine Identification Card --}}
            <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Swine Identification
                            </p>

                            <h2 class="mt-1 text-2xl font-bold text-gray-900">
                                {{ $swine->tag_number }}
                            </h2>

                            @if($swine->name)
                                <p class="text-sm text-gray-600">
                                    {{ $swine->name }}
                                </p>
                            @endif
                        </div>

                        <div>
                            @php
                                $status = strtolower($swine->status ?? 'unknown');
                            @endphp

                            @if($status === 'active')
                                <span
                                    class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                    Active
                                </span>
                            @elseif($status === 'sold')
                                <span
                                    class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                    Sold
                                </span>
                            @elseif($status === 'deceased')
                                <span
                                    class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                    Deceased
                                </span>
                            @else
                                <span
                                    class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                    {{ ucfirst($swine->status ?? 'Unknown') }}
                                </span>
                            @endif
                        </div>

                    </div>
                </div>


                {{-- Basic Information --}}
                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-3">

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Tag Number
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $swine->tag_number }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Sex
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $swine->sex ? ucfirst($swine->sex) : '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Breed
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $swine->breed ?: '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Farm
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $swine->farm->name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Birth Date
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $swine->birth_date?->format('M d, Y') ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Acquisition Date
                        </p>
                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $swine->acquisition_date?->format('M d, Y') ?? '—' }}
                        </p>
                    </div>

                </div>
            </div>


            {{-- Current Location --}}
            <div class="mb-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                <div class="flex items-start gap-4">

                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Current Location
                        </p>

                        <p class="mt-1 text-lg font-bold text-gray-900">
                            {{ $swine->currentLocation->name ?? 'No location assigned' }}
                        </p>

                        @if($swine->currentLocation)
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $swine->currentLocation->type ?? 'Farm Location' }}
                            </p>
                        @endif
                    </div>

                </div>
            </div>


            {{-- Traceability Summary --}}
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">

                {{-- Movements --}}
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">
                        Total Movements
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $swine->movements->count() }}
                    </p>
                </div>


                {{-- Health Records --}}
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">
                        Health Records
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $swine->healthRecords->count() }}
                    </p>
                </div>


                {{-- Weight Records --}}
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <p class="text-sm font-medium text-gray-500">
                        Weight Records
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $swine->weightRecords->count() }}
                    </p>
                </div>

            </div>


            {{-- Health Status --}}
            <div class="mb-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 class="text-lg font-bold text-gray-900">
                        Health Status
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Latest recorded health condition of the swine
                    </p>
                </div>

                <div class="p-5">

                    @php
                        $latestHealth = $swine->healthRecords
                            ->sortByDesc('record_date')
                            ->first();
                    @endphp

                    @if($latestHealth)

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Health Status
                                </p>

                                @php
                                    $healthStatus = strtolower($latestHealth->health_status ?? 'unknown');
                                @endphp

                                <div class="mt-2">
                                    @if($healthStatus === 'healthy')
                                        <span
                                            class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                            Healthy
                                        </span>
                                    @elseif($healthStatus === 'under_observation')
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700">
                                            Under Observation
                                        </span>
                                    @elseif($healthStatus === 'sick')
                                        <span
                                            class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                            Sick
                                        </span>

                                    @elseif($healthStatus === 'recovering')
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-700">
                                            Recovering
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                            {{ ucfirst($latestHealth->health_status ?? 'Unknown') }}
                                        </span>
                                    @endif
                                </div>
                            </div>


                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Record Date
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ $latestHealth->record_date?->format('M d, Y') ?? '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Record Type
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ $latestHealth->record_type ?: '—' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Diagnosis
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ $latestHealth->diagnosis ?: 'None recorded' }}
                                </p>
                            </div>


                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Treatment
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ $latestHealth->treatment ?: 'None recorded' }}
                                </p>
                            </div>

                        </div>

                        @if($latestHealth->observations || $latestHealth->veterinary_assessment || $latestHealth->notes)

                            <div class="mt-5 border-t border-gray-100 pt-5">

                                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

                                    @if($latestHealth->observations)
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                Observations
                                            </p>

                                            <p class="mt-1 text-sm leading-6 text-gray-700">
                                                {{ $latestHealth->observations }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($latestHealth->veterinary_assessment)
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                Veterinary Assessment
                                            </p>

                                            <p class="mt-1 text-sm leading-6 text-gray-700">
                                                {{ $latestHealth->veterinary_assessment }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($latestHealth->notes)
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                Notes
                                            </p>

                                            <p class="mt-1 text-sm leading-6 text-gray-700">
                                                {{ $latestHealth->notes }}
                                            </p>
                                        </div>
                                    @endif

                                </div>

                            </div>

                        @endif

                    @else

                        <div class="py-6 text-center">
                            <p class="text-sm text-gray-500">
                                No health records available for this swine.
                            </p>
                        </div>

                    @endif

                </div>
            </div>


            {{-- Vaccination History --}}
            <div class="mb-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 class="text-lg font-bold text-gray-900">
                        Vaccination History
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Vaccinations recorded through the health records module
                    </p>
                </div>

                <div class="p-5">

                    @php
                        $vaccinations = $swine->healthRecords
                            ->filter(function ($record) {
                                return strtolower($record->record_type ?? '') === 'vaccination';
                            })
                            ->sortByDesc('record_date');
                    @endphp

                    @if($vaccinations->count())

                        <div class="space-y-4">

                            @foreach($vaccinations as $vaccination)

                                <div class="rounded-lg border border-gray-200 p-4">

                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                                        <div>
                                            <h3 class="font-semibold text-gray-900">
                                                {{ $vaccination->vaccine_name ?: 'Unnamed Vaccine' }}
                                            </h3>

                                            <p class="mt-1 text-sm text-gray-500">
                                                Vaccination
                                            </p>
                                        </div>

                                        @php
                                            $dueDate = $vaccination->next_due_date;
                                            $today = now()->startOfDay();
                                        @endphp

                                        @if($dueDate)

                                            @if($dueDate->lt($today))

                                                <span
                                                    class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                                    Overdue
                                                </span>

                                            @elseif($dueDate->lte($today->copy()->addDays(7)))

                                                <span
                                                    class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">
                                                    Due Soon
                                                </span>

                                            @else

                                                <span
                                                    class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                    Up to Date
                                                </span>

                                            @endif

                                        @endif

                                    </div>


                                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                                        {{-- Date Administered --}}
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Date Administered
                                            </p>

                                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                                {{ $vaccination->record_date?->format('M d, Y') ?? '—' }}
                                            </p>
                                        </div>


                                        {{-- Dose --}}
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Dose
                                            </p>

                                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                                {{ $vaccination->dose ?: '—' }}
                                            </p>
                                        </div>


                                        {{-- Batch Number --}}
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Batch Number
                                            </p>

                                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                                {{ $vaccination->batch_number ?: '—' }}
                                            </p>
                                        </div>


                                        {{-- Next Due Date --}}
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Next Due Date
                                            </p>

                                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                                {{ $vaccination->next_due_date?->format('M d, Y') ?? '—' }}
                                            </p>
                                        </div>

                                    </div>


                                    @if($vaccination->notes)

                                        <div class="mt-4 border-t border-gray-100 pt-4">

                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Notes
                                            </p>

                                            <p class="mt-1 text-sm leading-6 text-gray-700">
                                                {{ $vaccination->notes }}
                                            </p>

                                        </div>

                                    @endif

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="py-6 text-center">

                            <p class="text-sm text-gray-500">
                                No vaccination records available for this swine.
                            </p>

                        </div>

                    @endif

                </div>

            </div>


            {{-- Growth / Weight History --}}
            <div class="mb-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-5 py-4">

                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <h2 class="text-lg font-bold text-gray-900">
                                Growth & Weight History
                            </h2>

                            <p class="text-sm text-gray-500">
                                Recorded weight measurements over time
                            </p>
                        </div>

                        @php
                            $latestWeight = $swine->weightRecords
                                ->sortByDesc('record_date')
                                ->first();
                        @endphp

                        @if($latestWeight)
                            <div class="mt-2 sm:mt-0 sm:text-right">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Latest Weight
                                </p>

                                <p class="text-xl font-bold text-gray-900">
                                    {{ number_format((float) $latestWeight->weight, 2) }} kg
                                </p>
                            </div>
                        @endif

                    </div>

                </div>


                <div class="p-5">

                    @if($swine->weightRecords->count())

                        <div class="overflow-x-auto">

                            <table class="min-w-full divide-y divide-gray-200">

                                <thead>
                                    <tr>
                                        <th
                                            class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            Date
                                        </th>

                                        <th
                                            class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            Weight
                                        </th>

                                        <th
                                            class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            Notes
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">

                                    @foreach($swine->weightRecords->sortByDesc('record_date') as $weight)

                                        <tr>

                                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700">
                                                {{ $weight->record_date?->format('M d, Y') ?? '—' }}
                                            </td>

                                            <td class="whitespace-nowrap px-3 py-3 text-sm font-semibold text-gray-900">
                                                {{ number_format((float) $weight->weight, 2) }} kg
                                            </td>

                                            <td class="px-3 py-3 text-sm text-gray-600">
                                                {{ $weight->notes ?: '—' }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="py-6 text-center">
                            <p class="text-sm text-gray-500">
                                No weight records available for this swine.
                            </p>
                        </div>

                    @endif

                </div>
            </div>


            {{-- Movement / Traceability History --}}
            <div class="mb-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h2 class="text-lg font-bold text-gray-900">
                        Movement & Traceability History
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Recorded movement of the swine between farm locations
                    </p>

                </div>

                <div class="p-5">

                    @if($swine->movements->count())

                        <div class="space-y-4">

                            @foreach($swine->movements->sortByDesc('movement_date') as $movement)

                                @php
                                    $movementStatus = strtolower(
                                        trim($movement->status ?? 'completed')
                                    );

                                    $movementResolution = strtolower(
                                        trim($movement->conflict_resolution ?? '')
                                    );
                                @endphp


                                <div class="relative rounded-lg border border-gray-200 p-4">

                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                                        <div>

                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Movement Date
                                            </p>

                                            <p class="mt-1 font-semibold text-gray-900">
                                                {{ $movement->movement_date?->format('M d, Y h:i A') ?? '—' }}
                                            </p>

                                        </div>


                                        {{-- Movement Status --}}
                                        <div class="text-left sm:text-right">

                                            @if($movementStatus === 'completed')

                                                <span class="inline-flex rounded-full
                                                                               bg-green-100 px-3 py-1
                                                                               text-xs font-semibold text-green-700">
                                                    Completed
                                                </span>


                                            @elseif($movementStatus === 'superseded')

                                                <span class="inline-flex rounded-full
                                                                               bg-yellow-100 px-3 py-1
                                                                               text-xs font-semibold text-yellow-700">
                                                    Superseded
                                                </span>


                                            @elseif($movementStatus === 'conflict')

                                                <span class="inline-flex rounded-full
                                                                               bg-red-100 px-3 py-1
                                                                               text-xs font-semibold text-red-700">
                                                    Conflict
                                                </span>


                                            @elseif($movementStatus === 'pending')

                                                <span class="inline-flex rounded-full
                                                                               bg-yellow-100 px-3 py-1
                                                                               text-xs font-semibold text-yellow-700">
                                                    Pending
                                                </span>


                                            @elseif($movementStatus === 'cancelled')

                                                <span class="inline-flex rounded-full
                                                                               bg-red-100 px-3 py-1
                                                                               text-xs font-semibold text-red-700">
                                                    Cancelled
                                                </span>


                                            @else

                                                <span class="inline-flex rounded-full
                                                                               bg-gray-100 px-3 py-1
                                                                               text-xs font-semibold text-gray-700">
                                                    {{ ucfirst($movementStatus) }}
                                                </span>

                                            @endif

                                        </div>

                                    </div>


                                    {{-- Movement Route --}}
                                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3 md:items-center">

                                        {{-- From --}}
                                        <div class="rounded-lg bg-gray-50 p-3">

                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                From
                                            </p>

                                            <p class="mt-1 font-semibold text-gray-900">
                                                {{ $movement->fromLocation?->name ?? 'Initial Location' }}
                                            </p>

                                            @if($movement->fromLocation?->location_code)

                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $movement->fromLocation->location_code }}
                                                </p>

                                            @endif

                                        </div>


                                        {{-- Arrow --}}
                                        <div class="hidden justify-center md:flex">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                            </svg>

                                        </div>


                                        {{-- To --}}
                                        <div class="rounded-lg bg-blue-50 p-3">

                                            <p class="text-xs font-medium uppercase tracking-wide text-blue-600">
                                                To
                                            </p>

                                            <p class="mt-1 font-semibold text-gray-900">
                                                {{ $movement->toLocation?->name ?? 'Unknown Location' }}
                                            </p>

                                            @if($movement->toLocation?->location_code)

                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $movement->toLocation->location_code }}
                                                </p>

                                            @endif

                                        </div>

                                    </div>


                                    {{-- Reason --}}
                                    @if($movement->reason)

                                        <div class="mt-4">

                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Reason
                                            </p>

                                            <p class="mt-1 text-sm text-gray-700">
                                                {{ $movement->reason }}
                                            </p>

                                        </div>

                                    @endif


                                    {{-- Notes --}}
                                    @if($movement->notes)

                                        <div class="mt-3">

                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Notes
                                            </p>

                                            <p class="mt-1 whitespace-pre-line text-sm text-gray-700">
                                                {{ $movement->notes }}
                                            </p>

                                        </div>

                                    @endif


                                    {{-- Conflict Resolution --}}
                                    @if($movementResolution === 'offline')

                                        <div class="mt-4 border-t border-gray-100 pt-3">

                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Conflict Resolution
                                            </p>

                                            <p class="mt-1 text-sm font-semibold text-blue-700">
                                                Offline Version Kept
                                            </p>

                                        </div>


                                    @elseif($movementResolution === 'online')

                                        <div class="mt-4 border-t border-gray-100 pt-3">

                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                Conflict Resolution
                                            </p>

                                            <p class="mt-1 text-sm font-semibold text-purple-700">
                                                Online Version Kept
                                            </p>

                                        </div>

                                    @endif

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="py-6 text-center">

                            <p class="text-sm text-gray-500">
                                No movement history available for this swine.
                            </p>

                        </div>

                    @endif

                </div>

            </div>

            {{-- Footer --}}
            <div class="py-6 text-center">

                <p class="text-xs text-gray-500">
                    SwineLocate — QR-Based Swine Traceability and Management
                </p>

            </div>

        </div>
    </div>
</x-app-layout>