<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Health Record
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Detailed veterinary and health information.
                </p>
            </div>

            <div class="flex gap-2">
                <!-- <a href="{{ route('health-records.history', $swine) }}" class="inline-flex items-center justify-center rounded-lg
           bg-indigo-600 px-4 py-2 text-sm font-semibold
           text-white shadow-sm hover:bg-indigo-700">
                    Health History
                </a> -->

                <a href="{{ route('health-records.edit', $healthRecord) }}" class="inline-flex items-center justify-center rounded-lg
                           bg-indigo-600 px-4 py-2 text-sm font-semibold
                           text-white shadow-sm hover:bg-indigo-700">
                    Edit Record
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


            {{-- Swine Identification --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                        <div>

                            <p class="text-sm font-medium text-gray-500">
                                Swine Identification
                            </p>

                            <h1 class="mt-1 text-2xl font-bold text-gray-900">
                                {{ $healthRecord->swine?->tag_number ?? '—' }}
                            </h1>

                            @if ($healthRecord->swine?->name)

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $healthRecord->swine->name }}
                                </p>

                            @endif

                        </div>


                        {{-- Health Status --}}
                        @php
                            $status = $healthRecord->health_status;

                            $statusClasses = match ($status) {
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

                        <span class="inline-flex w-fit rounded-full px-3 py-1
                                   text-sm font-semibold {{ $statusClasses }}">
                            {{ str_replace('_', ' ', ucfirst($status)) }}
                        </span>

                    </div>

                </div>


                {{-- Basic Information --}}
                <div class="px-6 py-6">

                    <h3 class="mb-5 text-lg font-semibold text-gray-900">
                        Record Information
                    </h3>


                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                        <div>

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Record Date
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $healthRecord->record_date?->format('F d, Y') ?? '—' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Record Type
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $healthRecord->record_type ?: '—' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Sex
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $healthRecord->swine?->sex
    ? ucfirst($healthRecord->swine->sex)
    : '—' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Breed
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $healthRecord->swine?->breed ?? '—' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Farm
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $healthRecord->swine?->farm?->name ?? '—' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Recorded By
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $healthRecord->recordedBy?->name ?? '—' }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>

            {{-- Vaccination Details --}}
            @if ($healthRecord->record_type === 'Vaccination')

                @php
                    $vaccinationStatus = null;
                    $statusClasses = '';
                    $statusIcon = '';

                    if ($healthRecord->next_due_date) {
                        $today = now()->startOfDay();
                        $dueDate = $healthRecord->next_due_date->startOfDay();

                        if ($dueDate->isPast()) {
                            $vaccinationStatus = 'Overdue';
                            $statusClasses = 'bg-red-100 text-red-700 ring-red-200';
                            $statusIcon = '⚠';
                        } elseif ($dueDate->isToday()) {
                            $vaccinationStatus = 'Due Today';
                            $statusClasses = 'bg-orange-100 text-orange-700 ring-orange-200';
                            $statusIcon = '!';
                        } elseif ($today->diffInDays($dueDate) <= 7) {
                            $vaccinationStatus = 'Due Soon';
                            $statusClasses = 'bg-yellow-100 text-yellow-700 ring-yellow-200';
                            $statusIcon = '◷';
                        } else {
                            $vaccinationStatus = 'Scheduled';
                            $statusClasses = 'bg-green-100 text-green-700 ring-green-200';
                            $statusIcon = '✓';
                        }
                    } else {
                        $vaccinationStatus = 'No Due Date';
                        $statusClasses = 'bg-gray-100 text-gray-700 ring-gray-200';
                        $statusIcon = '—';
                    }
                @endphp


                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    {{-- Header --}}
                    <div class="border-b border-gray-200 px-6 py-5">

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Vaccination Details
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Vaccine information recorded for this swine.
                                </p>
                            </div>


                            {{-- Vaccination Status --}}
                            <span class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1.5
                                         text-sm font-semibold ring-1 {{ $statusClasses }}">

                                <span>
                                    {{ $statusIcon }}
                                </span>

                                {{ $vaccinationStatus }}

                            </span>

                        </div>

                    </div>


                    {{-- Vaccination Information --}}
                    <div class="px-6 py-6">

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                            {{-- Vaccine Name --}}
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Vaccine Name
                                </p>

                                <p class="mt-1 text-sm font-semibold text-gray-900">
                                    {{ $healthRecord->vaccine_name ?: '—' }}
                                </p>
                            </div>


                            {{-- Dose --}}
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Dose
                                </p>

                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $healthRecord->dose ?: '—' }}
                                </p>
                            </div>


                            {{-- Batch Number --}}
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Batch / Lot Number
                                </p>

                                <p class="mt-1 font-mono text-sm text-gray-900">
                                    {{ $healthRecord->batch_number ?: '—' }}
                                </p>
                            </div>


                            {{-- Date Administered --}}
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Date Administered
                                </p>

                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $healthRecord->record_date?->format('F d, Y') ?? '—' }}
                                </p>
                            </div>


                            {{-- Next Due Date --}}
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Next Due Date
                                </p>

                                <p class="mt-1 text-sm font-semibold text-gray-900">
                                    {{ $healthRecord->next_due_date?->format('F d, Y') ?? '—' }}
                                </p>
                            </div>


                            {{-- Due Information --}}
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Due Status
                                </p>

                                <p class="mt-1 text-sm text-gray-900">

                                    @if (!$healthRecord->next_due_date)

                                        No next vaccination date recorded.

                                    @elseif ($vaccinationStatus === 'Overdue')

                                        <span class="font-semibold text-red-600">
                                            Vaccination is overdue.
                                        </span>

                                    @elseif ($vaccinationStatus === 'Due Today')

                                        <span class="font-semibold text-orange-600">
                                            Vaccination is due today.
                                        </span>

                                    @elseif ($vaccinationStatus === 'Due Soon')

                                        @php
                                            $daysUntilDue = now()
                                                ->startOfDay()
                                                ->diffInDays(
                                                    $healthRecord->next_due_date->startOfDay()
                                                );
                                        @endphp

                                        <span class="font-semibold text-yellow-600">
                                            Vaccination is due in {{ $daysUntilDue }} day{{ $daysUntilDue == 1 ? '' : 's' }}.
                                        </span>

                                    @else

                                        @php
                                            $daysUntilDue = now()
                                                ->startOfDay()
                                                ->diffInDays(
                                                    $healthRecord->next_due_date->startOfDay()
                                                );
                                        @endphp

                                        <span class="font-semibold text-green-600">
                                            Vaccination is scheduled in {{ $daysUntilDue }} days.
                                        </span>

                                    @endif

                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            @endif

            {{-- Clinical Information --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Clinical Information
                    </h3>

                </div>


                <div class="space-y-6 px-6 py-6">


                    {{-- Symptoms --}}
                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Symptoms
                        </p>

                        <div class="mt-2 rounded-lg bg-gray-50 p-4 text-sm leading-6 text-gray-700">
                            {{ $healthRecord->symptoms ?: 'No symptoms recorded.' }}
                        </div>

                    </div>


                    {{-- Diagnosis --}}
                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Diagnosis
                        </p>

                        <div class="mt-2 rounded-lg bg-gray-50 p-4 text-sm leading-6 text-gray-700">
                            {{ $healthRecord->diagnosis ?: 'No diagnosis recorded.' }}
                        </div>

                    </div>


                    {{-- Treatment --}}
                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Treatment
                        </p>

                        <div class="mt-2 rounded-lg bg-gray-50 p-4 text-sm leading-6 text-gray-700">
                            {{ $healthRecord->treatment ?: 'No treatment recorded.' }}
                        </div>

                    </div>


                    {{-- Observations --}}
                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Observations
                        </p>

                        <div class="mt-2 rounded-lg bg-gray-50 p-4 text-sm leading-6 text-gray-700">
                            {{ $healthRecord->observations ?: 'No observations recorded.' }}
                        </div>

                    </div>


                    {{-- Veterinary Assessment --}}
                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Veterinary Assessment
                        </p>

                        <div class="mt-2 rounded-lg bg-gray-50 p-4 text-sm leading-6 text-gray-700">
                            {{ $healthRecord->veterinary_assessment ?: 'No veterinary assessment recorded.' }}
                        </div>

                    </div>

                </div>

            </div>

            {{-- Additional Notes --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Additional Notes
                    </h3>

                </div>


                <div class="px-6 py-6">

                    <p class="whitespace-pre-line text-sm leading-6 text-gray-700">
                        {{ $healthRecord->notes ?: 'No additional notes.' }}
                    </p>

                </div>

            </div>


            {{-- Record Metadata --}}
            <div class="overflow-hidden rounded-xl bg-gray-50 ring-1 ring-gray-200">

                <div class="px-6 py-5">

                    <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">

                        <div>

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Created
                            </p>

                            <p class="mt-1 text-gray-700">
                                {{ $healthRecord->created_at?->format('F d, Y h:i A') ?? '—' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Last Updated
                            </p>

                            <p class="mt-1 text-gray-700">
                                {{ $healthRecord->updated_at?->format('F d, Y h:i A') ?? '—' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Record ID
                            </p>

                            <p class="mt-1 font-mono text-gray-700">
                                #{{ $healthRecord->id }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>

</x-app-layout>