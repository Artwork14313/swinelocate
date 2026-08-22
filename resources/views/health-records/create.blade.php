<x-app-layout>

    <x-slot name="header">

        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Add Health Record
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Record a veterinary examination or health-related event.
            </p>
        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            {{-- Validation Errors --}}
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
                action="{{ route('health-records.store') }}"
                class="space-y-6"
            >

                @csrf


                {{-- Basic Information --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Basic Information
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Identify the swine and record the date and type of health event.
                        </p>

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
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                            >

                                <option value="">
                                    Select swine
                                </option>

                                @foreach ($swine as $animal)

                                    <option
                                        value="{{ $animal->id }}"
                                        @selected(old('swine_id') == $animal->id)
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
                                Record Date <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="date"
                                id="record_date"
                                name="record_date"
                                value="{{ old('record_date', now()->format('Y-m-d')) }}"
                                max="{{ now()->format('Y-m-d') }}"
                                required
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
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
                                Record Type <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="record_type"
                                name="record_type"
                                required
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                            >

                                <option value="">
                                    Select record type
                                </option>

                                <option
                                    value="Routine Examination"
                                    @selected(old('record_type') === 'Routine Examination')
                                >
                                    Routine Examination
                                </option>

                                <option
                                    value="Illness"
                                    @selected(old('record_type') === 'Illness')
                                >
                                    Illness
                                </option>

                                <option
                                    value="Treatment"
                                    @selected(old('record_type') === 'Treatment')
                                >
                                    Treatment
                                </option>

                                <option
                                    value="Injury"
                                    @selected(old('record_type') === 'Injury')
                                >
                                    Injury
                                </option>

                                <option
                                    value="Follow-up"
                                    @selected(old('record_type') === 'Follow-up')
                                >
                                    Follow-up
                                </option>

                                <option
                                    value="Other"
                                    @selected(old('record_type') === 'Other')
                                >
                                    Other
                                </option>

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

                        <p class="mt-1 text-sm text-gray-500">
                            Record symptoms, diagnosis, treatment, and observations.
                        </p>

                    </div>


                    <div class="space-y-6 px-6 py-6">

                        {{-- Symptoms --}}
                        <div>

                            <label
                                for="symptoms"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Symptoms
                            </label>

                            <textarea
                                id="symptoms"
                                name="symptoms"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                                placeholder="Describe observed symptoms..."
                            >{{ old('symptoms') }}</textarea>

                            @error('symptoms')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Diagnosis --}}
                        <div>

                            <label
                                for="diagnosis"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Diagnosis
                            </label>

                            <textarea
                                id="diagnosis"
                                name="diagnosis"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                                placeholder="Enter diagnosis..."
                            >{{ old('diagnosis') }}</textarea>

                            @error('diagnosis')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Treatment --}}
                        <div>

                            <label
                                for="treatment"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Treatment
                            </label>

                            <textarea
                                id="treatment"
                                name="treatment"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                                placeholder="Describe treatment or medication given..."
                            >{{ old('treatment') }}</textarea>

                            @error('treatment')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Observations --}}
                        <div>

                            <label
                                for="observations"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Observations
                            </label>

                            <textarea
                                id="observations"
                                name="observations"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                                placeholder="Record other observations..."
                            >{{ old('observations') }}</textarea>

                            @error('observations')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Veterinary Assessment --}}
                        <div>

                            <label
                                for="veterinary_assessment"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Veterinary Assessment
                            </label>

                            <textarea
                                id="veterinary_assessment"
                                name="veterinary_assessment"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                                placeholder="Enter veterinary assessment..."
                            >{{ old('veterinary_assessment') }}</textarea>

                            @error('veterinary_assessment')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Health Status --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Health Status
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Indicate the swine's health condition at the time of recording.
                        </p>

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
                            class="mt-2 block w-full rounded-lg border-gray-300
                                   shadow-sm focus:border-indigo-500
                                   focus:ring-indigo-500"
                        >

                            <option value="">
                                Select health status
                            </option>

                            <option
                                value="healthy"
                                @selected(old('health_status') === 'healthy')
                            >
                                Healthy
                            </option>

                            <option
                                value="under_observation"
                                @selected(old('health_status') === 'under_observation')
                            >
                                Under Observation
                            </option>

                            <option
                                value="sick"
                                @selected(old('health_status') === 'sick')
                            >
                                Sick
                            </option>

                            <option
                                value="recovering"
                                @selected(old('health_status') === 'recovering')
                            >
                                Recovering
                            </option>

                        </select>

                        @error('health_status')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

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

                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            class="block w-full rounded-lg border-gray-300
                                   shadow-sm focus:border-indigo-500
                                   focus:ring-indigo-500"
                            placeholder="Additional notes..."
                        >{{ old('notes') }}</textarea>

                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

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
                               hover:bg-indigo-700
                               focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Save Health Record
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>