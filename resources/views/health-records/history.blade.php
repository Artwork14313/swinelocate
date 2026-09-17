<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Health History
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Complete health history for
                    <span class="font-semibold text-gray-900">
                        {{ $swine->tag_number }}
                    </span>
                </p>
            </div>

            <div class="flex flex-wrap gap-2">

                @can('manage-health')
                    <a href="{{ route('health-records.create', ['swine_id' => $swine->id]) }}" class="inline-flex items-center justify-center rounded-lg
                                   bg-indigo-600 px-4 py-2 text-sm font-semibold
                                   text-white shadow-sm hover:bg-indigo-700
                                   focus:outline-none focus:ring-2
                                   focus:ring-indigo-500 focus:ring-offset-2">
                        Add Health Record
                    </a>
                @endcan

                <a href="{{ route('health-records.index') }}" class="inline-flex items-center justify-center rounded-lg
                           border border-gray-300 bg-white px-4 py-2
                           text-sm font-semibold text-gray-700
                           shadow-sm hover:bg-gray-50
                           focus:outline-none focus:ring-2
                           focus:ring-gray-400 focus:ring-offset-2">
                    Back
                </a>

            </div>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">


            {{-- Swine Identification --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="px-6 py-6">

                    <div class="flex flex-col gap-4 lg:justify-between lg:flex-row lg:items-center">

                        {{-- Main Identification --}}
                        <div class="lg:w-56 lg:shrink-0">

                            <p class="text-sm font-medium text-gray-500">
                                Swine Identification
                            </p>

                            <h1 class="mt-1 text-3xl font-bold text-gray-900">
                                {{ $swine->tag_number }}
                            </h1>

                            @if ($swine->name)
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $swine->name }}
                                </p>
                            @endif

                        </div>

                        
                        {{-- Sex --}}
                        <div class="rounded-lg bg-gray-50 px-4 py-3 lg:flex-1">

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Sex
                            </p>

                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $swine->sex ? ucfirst($swine->sex) : '—' }}
                            </p>

                        </div>


                        {{-- Breed --}}
                        <div class="rounded-lg bg-gray-50 px-4 py-3 lg:flex-1">

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Breed
                            </p>

                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $swine->breed ?: '—' }}
                            </p>

                        </div>


                        {{-- Farm --}}
                        <div class="rounded-lg bg-gray-50 px-4 py-3 lg:flex-1">

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Farm
                            </p>

                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $swine->farm?->name ?? '—' }}
                            </p>

                        </div>


                        {{-- Current Location --}}
                        <div class="rounded-lg bg-gray-50 px-4 py-3 lg:flex-1">

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Current Location
                            </p>

                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $swine->currentLocation?->name ?? 'No location assigned' }}
                            </p>

                        </div>


                        {{-- Status --}}
                        @php
                            $swineStatusClasses = match ($swine->status) {
                                'active' => 'bg-green-100 text-green-700',
                                'inactive' => 'bg-gray-100 text-gray-700',
                                'sold' => 'bg-blue-100 text-blue-700',
                                'deceased' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <div class="rounded-lg bg-gray-50 px-4 py-3 lg:flex-1">

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Status
                            </p>

                            <span class="mt-1 inline-flex rounded-full px-2.5 py-1
                           text-xs font-semibold {{ $swineStatusClasses }}">
                                {{ ucfirst($swine->status) }}
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Health Summary --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                {{-- Total Records --}}
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                    <p class="text-sm font-medium text-gray-500">
                        Total Health Records
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $healthRecords->count() }}
                    </p>

                </div>


                {{-- Latest Record --}}
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                    <p class="text-sm font-medium text-gray-500">
                        Latest Record
                    </p>

                    <p class="mt-2 text-lg font-bold text-gray-900">
                        {{ $healthRecords->first()?->record_date?->format('M d, Y') ?? 'No records' }}
                    </p>

                </div>


                {{-- Current Recorded Health --}}
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                    <p class="text-sm font-medium text-gray-500">
                        Current Recorded Health
                    </p>

                    @php
                        $latestStatus = $healthRecords->first()?->health_status;

                        $latestStatusClasses = match ($latestStatus) {
                            'healthy' => 'bg-green-100 text-green-700',
                            'under_observation' => 'bg-yellow-100 text-yellow-700',
                            'sick' => 'bg-red-100 text-red-700',
                            'recovering' => 'bg-blue-100 text-blue-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp

                    @if ($latestStatus)

                        <span class="mt-2 inline-flex rounded-full px-2.5 py-1
                                       text-xs font-semibold {{ $latestStatusClasses }}">
                            {{ str_replace('_', ' ', ucfirst($latestStatus)) }}
                        </span>

                    @else

                        <p class="mt-2 text-lg font-bold text-gray-900">
                            No record
                        </p>

                    @endif

                </div>

            </div>


            {{-- Next Vaccination --}}
            @php
                $nextVaccination = $healthRecords
                    ->where('record_type', 'Vaccination')
                    ->whereNotNull('next_due_date')
                    ->sortBy('next_due_date')
                    ->first();
            @endphp


            @if ($nextVaccination)

                @php
                    $today = now()->startOfDay();
                    $dueDate = $nextVaccination->next_due_date->copy()->startOfDay();

                    if ($dueDate->lt($today)) {

                        $vaccinationStatus = 'Overdue';
                        $statusClasses = 'bg-red-100 text-red-700';
                        $statusTextClasses = 'text-red-600';
                        $vaccinationMessage = 'Vaccination is overdue.';

                    } elseif ($dueDate->equalTo($today)) {

                        $vaccinationStatus = 'Due Today';
                        $statusClasses = 'bg-orange-100 text-orange-700';
                        $statusTextClasses = 'text-orange-600';
                        $vaccinationMessage = 'Vaccination is due today.';

                    } else {

                        $daysUntilDue = $today->diffInDays($dueDate);

                        if ($daysUntilDue <= 7) {

                            $vaccinationStatus = 'Due Soon';
                            $statusClasses = 'bg-yellow-100 text-yellow-700';
                            $statusTextClasses = 'text-yellow-600';

                            $vaccinationMessage =
                                'Vaccination is due in ' .
                                $daysUntilDue .
                                ' day' .
                                ($daysUntilDue === 1 ? '' : 's') .
                                '.';

                        } else {

                            $vaccinationStatus = 'Scheduled';
                            $statusClasses = 'bg-green-100 text-green-700';
                            $statusTextClasses = 'text-green-600';

                            $vaccinationMessage =
                                'Vaccination is scheduled in ' .
                                $daysUntilDue .
                                ' days.';

                        }
                    }
                @endphp


                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                            <div>

                                <h3 class="text-lg font-semibold text-gray-900">
                                    Next Vaccination
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Upcoming vaccination schedule for this swine.
                                </p>

                            </div>


                            <span class="inline-flex w-fit rounded-full px-3 py-1.5
                                           text-sm font-semibold {{ $statusClasses }}">
                                {{ $vaccinationStatus }}
                            </span>

                        </div>

                    </div>


                    <div class="px-6 py-6">

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">

                            {{-- Vaccine --}}
                            <div>

                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Vaccine
                                </p>

                                <p class="mt-1 text-sm font-semibold text-gray-900">
                                    {{ $nextVaccination->vaccine_name ?: '—' }}
                                </p>

                            </div>


                            {{-- Due Date --}}
                            <div>

                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Next Due Date
                                </p>

                                <p class="mt-1 text-sm font-semibold text-gray-900">
                                    {{ $dueDate->format('F d, Y') }}
                                </p>

                            </div>


                            {{-- Due Status --}}
                            <div>

                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Due Status
                                </p>

                                <p class="mt-1 text-sm font-semibold {{ $statusTextClasses }}">
                                    {{ $vaccinationMessage }}
                                </p>

                            </div>

                        </div>


                        <div class="mt-6 border-t border-gray-100 pt-5">

                            <a href="{{ route('health-records.show', $nextVaccination) }}" class="inline-flex items-center justify-center rounded-lg
                                           bg-indigo-600 px-4 py-2 text-sm font-semibold
                                           text-white shadow-sm hover:bg-indigo-700
                                           focus:outline-none focus:ring-2
                                           focus:ring-indigo-500 focus:ring-offset-2">
                                View Vaccination Record
                            </a>

                        </div>

                    </div>

                </div>

            @endif


            {{-- Veterinary & Health History --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                        <div>

                            <h3 class="text-lg font-semibold text-gray-900">
                                Veterinary & Health History
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Records are displayed from newest to oldest.
                            </p>

                        </div>

                        <span class="text-sm text-gray-500">
                            {{ $healthRecords->count() }}
                            {{ $healthRecords->count() === 1 ? 'record' : 'records' }}
                        </span>

                    </div>

                </div>


                @if ($healthRecords->isEmpty())

                    <div class="px-6 py-12 text-center">

                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">

                            <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6.586A2 2 0 0115 3.586L19.414 8A2 2 0 0120 9.414V19a2 2 0 01-2 2z" />
                            </svg>

                        </div>


                        <h3 class="mt-4 text-sm font-semibold text-gray-900">
                            No health records
                        </h3>

                        <p class="mx-auto mt-1 max-w-md text-sm text-gray-500">
                            No veterinary or health records have been recorded for this swine yet.
                        </p>


                        @can('manage-health')

                            <a href="{{ route('health-records.create', ['swine_id' => $swine->id]) }}" class="mt-5 inline-flex items-center justify-center rounded-lg
                                               bg-indigo-600 px-4 py-2 text-sm font-semibold
                                               text-white shadow-sm hover:bg-indigo-700">
                                Add Health Record
                            </a>

                        @endcan

                    </div>

                @else

                    {{-- Desktop Table --}}
                    <div class="hidden overflow-x-auto md:block">

                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">

                                <tr>

                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold
                                                   uppercase tracking-wide text-gray-500">
                                        Date
                                    </th>

                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold
                                                   uppercase tracking-wide text-gray-500">
                                        Record Type
                                    </th>

                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold
                                                   uppercase tracking-wide text-gray-500">
                                        Health Status
                                    </th>

                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold
                                                   uppercase tracking-wide text-gray-500">
                                        Diagnosis
                                    </th>

                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold
                                                   uppercase tracking-wide text-gray-500">
                                        Recorded By
                                    </th>

                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold
                                                   uppercase tracking-wide text-gray-500">
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-200 bg-white">

                                @foreach ($healthRecords as $record)

                                    @php
                                        $statusClasses = match ($record->health_status) {
                                            'healthy' => 'bg-green-100 text-green-700',
                                            'under_observation' => 'bg-yellow-100 text-yellow-700',
                                            'sick' => 'bg-red-100 text-red-700',
                                            'recovering' => 'bg-blue-100 text-blue-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp


                                    <tr class="hover:bg-gray-50">

                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">

                                            <div class="font-medium">
                                                {{ $record->record_date?->format('M d, Y') ?? '—' }}
                                            </div>

                                        </td>


                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $record->record_type ?: '—' }}
                                        </td>


                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span class="inline-flex rounded-full px-2.5 py-1
                                                               text-xs font-semibold {{ $statusClasses }}">
                                                {{ str_replace('_', ' ', ucfirst($record->health_status)) }}
                                            </span>

                                        </td>


                                        <td class="max-w-xs px-6 py-4 text-sm text-gray-700">

                                            <div class="truncate">
                                                {{ $record->diagnosis ?: 'No diagnosis recorded.' }}
                                            </div>

                                        </td>


                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                            {{ $record->recordedBy?->name ?? '—' }}
                                        </td>


                                        <td class="whitespace-nowrap px-6 py-4 text-right">

                                            <a href="{{ route('health-records.show', $record) }}" class="text-sm font-medium text-indigo-600
                                                               hover:text-indigo-800">
                                                View
                                            </a>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- Mobile Cards --}}
                    <div class="divide-y divide-gray-200 md:hidden">

                        @foreach ($healthRecords as $record)

                            @php
                                $statusClasses = match ($record->health_status) {
                                    'healthy' => 'bg-green-100 text-green-700',
                                    'under_observation' => 'bg-yellow-100 text-yellow-700',
                                    'sick' => 'bg-red-100 text-red-700',
                                    'recovering' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp


                            <div class="p-5">

                                <div class="flex items-start justify-between gap-4">

                                    <div>

                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $record->record_type ?: 'Health Record' }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $record->record_date?->format('M d, Y') ?? '—' }}
                                        </p>

                                    </div>


                                    <span class="inline-flex rounded-full px-2.5 py-1
                                                       text-xs font-semibold {{ $statusClasses }}">
                                        {{ str_replace('_', ' ', ucfirst($record->health_status)) }}
                                    </span>

                                </div>


                                <div class="mt-4 space-y-3">

                                    <div>

                                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                            Diagnosis
                                        </p>

                                        <p class="mt-1 text-sm text-gray-700">
                                            {{ $record->diagnosis ?: 'No diagnosis recorded.' }}
                                        </p>

                                    </div>


                                    <div>

                                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                            Recorded By
                                        </p>

                                        <p class="mt-1 text-sm text-gray-700">
                                            {{ $record->recordedBy?->name ?? '—' }}
                                        </p>

                                    </div>

                                </div>


                                <div class="mt-4">

                                    <a href="{{ route('health-records.show', $record) }}" class="text-sm font-medium text-indigo-600
                                                       hover:text-indigo-800">
                                        View Health Record
                                    </a>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif

            </div>


        </div>

    </div>

</x-app-layout>