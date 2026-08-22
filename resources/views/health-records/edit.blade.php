<x-app-layout>

    <x-slot name="header">

        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Edit Health Record
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Update the veterinary and health information for this record.
            </p>
        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            @if ($errors->any())

                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">

                    <p class="font-semibold">
                        Please correct the following errors:
                    </p>

                    <ul class="mt-2 list-disc pl-5">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif


            <form
                method="POST"
                action="{{ route('health-records.update', $healthRecord) }}"
                class="space-y-6"
            >

                @csrf
                @method('PUT')


                {{-- Basic Information --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Basic Information
                        </h3>

                    </div>


                    <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-2">

                        {{-- Swine --}}
                        <div class="sm:col-span-2">

                            <label
                                for="swine_id"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Swine <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="swine_id"
                                name="swine_id"
                                required
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
                            >

                                @foreach ($swine as $animal)

                                    <option
                                        value="{{ $animal->id }}"
                                        @selected(
                                            old(
                                                'swine_id',
                                                $healthRecord->swine_id
                                            ) == $animal->id
                                        )
                                    >
                                        {{ $animal->tag_number }}

                                        @if ($animal->name)
                                            — {{ $animal->name }}
                                        @endif
                                    </option>

                                @endforeach

                            </select>

                            @error('swine_id')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Record Date --}}
                        <div>

                            <label
                                for="record_date"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Record Date
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="date"
                                id="record_date"
                                name="record_date"
                                value="{{ old(
                                    'record_date',
                                    optional($healthRecord->record_date)->format('Y-m-d')
                                ) }}"
                                max="{{ now()->format('Y-m-d') }}"
                                required
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            @error('record_date')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Record Type --}}
                        <div>

                            <label
                                for="record_type"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Record Type
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="record_type"
                                name="record_type"
                                required
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
                            >

                                @foreach ([
                                    'Routine Examination',
                                    'Illness',
                                    'Treatment',
                                    'Injury',
                                    'Follow-up',
                                    'Other'
                                ] as $type)

                                    <option
                                        value="{{ $type }}"
                                        @selected(
                                            old(
                                                'record_type',
                                                $healthRecord->record_type
                                            ) === $type
                                        )
                                    >
                                        {{ $type }}
                                    </option>

                                @endforeach

                            </select>

                            @error('record_type')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Clinical Information --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Clinical Information
                        </h3>

                    </div>


                    <div class="space-y-6 px-6 py-6">

                        @foreach ([
                            'symptoms' => 'Symptoms',
                            'diagnosis' => 'Diagnosis',
                            'treatment' => 'Treatment',
                            'observations' => 'Observations',
                            'veterinary_assessment' => 'Veterinary Assessment'
                        ] as $field => $label)

                            <div>

                                <label
                                    for="{{ $field }}"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    {{ $label }}
                                </label>

                                <textarea
                                    id="{{ $field }}"
                                    name="{{ $field }}"
                                    rows="3"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >{{ old($field, $healthRecord->$field) }}</textarea>

                                @error($field)
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        @endforeach

                    </div>

                </div>


                {{-- Health Status --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Health Status
                        </h3>

                    </div>


                    <div class="px-6 py-6">

                        <label
                            for="health_status"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Current Health Status
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="health_status"
                            name="health_status"
                            required
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >

                            @foreach ([
                                'healthy' => 'Healthy',
                                'under_observation' => 'Under Observation',
                                'sick' => 'Sick',
                                'recovering' => 'Recovering'
                            ] as $value => $label)

                                <option
                                    value="{{ $value }}"
                                    @selected(
                                        old(
                                            'health_status',
                                            $healthRecord->health_status
                                        ) === $value
                                    )
                                >
                                    {{ $label }}
                                </option>

                            @endforeach

                        </select>

                        @error('health_status')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>


                {{-- Notes --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Additional Notes
                        </h3>

                    </div>

                    <div class="px-6 py-6">

                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            class="block w-full rounded-lg border-gray-300 shadow-sm
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >{{ old('notes', $healthRecord->notes) }}</textarea>

                    </div>

                </div>


                {{-- Actions --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a
                        href="{{ route('health-records.index') }}"
                        class="inline-flex justify-center rounded-lg border border-gray-300
                               bg-white px-5 py-2.5 text-sm font-semibold text-gray-700
                               shadow-sm hover:bg-gray-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-lg bg-indigo-600
                               px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                               hover:bg-indigo-700"
                    >
                        Update Health Record
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>