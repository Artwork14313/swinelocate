<x-app-layout>

    <x-slot name="header">

        <div>

            <h2 class="text-2xl font-bold text-gray-900">
                Add Weight Record
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Record the current weight of an active swine.
            </p>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            @if ($errors->any())

                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">

                    <p class="text-sm font-semibold text-red-800">
                        Please correct the following errors:
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            @if (Auth::user()->hasPermission('record-weight'))

                <form
                    id="weight-record-form"
                    method="POST"
                    action="{{ route('weight-records.store') }}"
                    class="space-y-6"
                >

                    @csrf


                    {{-- Weight Information --}}
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                        <div class="border-b border-gray-200 px-6 py-5">

                            <h3 class="text-lg font-semibold text-gray-900">
                                Weight Information
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Enter the swine, weighing date, and recorded weight.
                            </p>

                        </div>


                        <div class="space-y-6 px-6 py-6">


                            {{-- Swine --}}
                            <div>

                                <label
                                    for="swine_id"
                                    class="block text-sm font-medium text-gray-700"
                                >

                                    Swine
                                    <span class="text-red-500">*</span>

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


                                    @foreach ($swines as $swine)

                                        <option
                                            value="{{ $swine->id }}"
                                            @selected(
                                                old(
                                                    'swine_id',
                                                    $selectedSwine?->id
                                                ) == $swine->id
                                            )
                                        >

                                            {{ $swine->tag_number }}

                                            @if ($swine->name)

                                                — {{ $swine->name }}

                                            @endif

                                        </option>

                                    @endforeach

                                </select>


                                <p class="mt-1 text-xs text-gray-500">
                                    Only active swine can receive new weight records.
                                </p>


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
                                    value="{{ old('record_date', now()->format('Y-m-d')) }}"
                                    max="{{ now()->format('Y-m-d') }}"
                                    required
                                    class="mt-2 block w-full rounded-lg border-gray-300
                                           shadow-sm focus:border-indigo-500
                                           focus:ring-indigo-500"
                                >


                                <p class="mt-1 text-xs text-gray-500">
                                    Date when the swine was weighed. Future dates are not allowed.
                                </p>


                                @error('record_date')

                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>


                            {{-- Weight --}}
                            <div>

                                <label
                                    for="weight"
                                    class="block text-sm font-medium text-gray-700"
                                >

                                    Weight (kg)
                                    <span class="text-red-500">*</span>

                                </label>


                                <div class="relative mt-2">

                                    <input
                                        type="number"
                                        id="weight"
                                        name="weight"
                                        value="{{ old('weight') }}"
                                        step="0.01"
                                        min="0.01"
                                        max="9999.99"
                                        required
                                        inputmode="decimal"
                                        class="block w-full rounded-lg border-gray-300
                                               pr-14 shadow-sm
                                               focus:border-indigo-500
                                               focus:ring-indigo-500"
                                        placeholder="e.g. 45.50"
                                    >


                                    <span
                                        class="absolute inset-y-0 right-0
                                               flex items-center pr-4
                                               text-sm text-gray-500"
                                    >

                                        kg

                                    </span>

                                </div>


                                <p class="mt-1 text-xs text-gray-500">
                                    Enter the recorded body weight in kilograms.
                                </p>


                                @error('weight')

                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>


                            {{-- Notes --}}
                            <div>

                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-gray-700"
                                >

                                    Notes

                                </label>


                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="4"
                                    maxlength="2000"
                                    class="mt-2 block w-full rounded-lg border-gray-300
                                           shadow-sm focus:border-indigo-500
                                           focus:ring-indigo-500"
                                    placeholder="Optional notes about the weighing..."
                                >{{ old('notes') }}</textarea>


                                <div class="mt-1 flex justify-between gap-4">

                                    <p class="text-xs text-gray-500">
                                        Optional information about the weight measurement.
                                    </p>

                                    <p
                                        id="notes-counter"
                                        class="shrink-0 text-xs text-gray-400"
                                    >
                                        0 / 2000
                                    </p>

                                </div>


                                @error('notes')

                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                        </div>

                    </div>


                    {{-- Actions --}}
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">

                        <a
                            href="{{ route('weight-records.index') }}"
                            class="inline-flex items-center justify-center rounded-lg
                                   border border-gray-300 bg-white px-4 py-2
                                   text-sm font-semibold text-gray-700 shadow-sm
                                   transition hover:bg-gray-50
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-400 focus:ring-offset-2"
                        >

                            Cancel

                        </a>


                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg
                                   bg-[#3368A0] px-5 py-2
                                   text-sm font-semibold text-white shadow-sm
                                   transition hover:bg-[#28557F]
                                   focus:outline-none focus:ring-2
                                   focus:ring-[#3368A0]
                                   focus:ring-offset-2"
                        >

                            Save Weight Record

                        </button>

                    </div>

                </form>

            @else

                <div class="rounded-xl bg-white px-6 py-12 text-center
                            shadow-sm ring-1 ring-gray-200">

                    <p class="text-sm font-semibold text-gray-900">
                        You do not have permission to record weights.
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        Contact an administrator if you need access to this function.
                    </p>

                    <a
                        href="{{ route('weight-records.index') }}"
                        class="mt-4 inline-flex rounded-lg border border-gray-300
                               bg-white px-4 py-2 text-sm font-semibold text-gray-700
                               hover:bg-gray-50"
                    >

                        Back to Weight Records

                    </a>

                </div>

            @endif

        </div>

    </div>


    {{-- Character Counter --}}
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const notes = document.getElementById('notes');
            const counter = document.getElementById('notes-counter');

            if (!notes || !counter) {
                return;
            }


            function updateCounter() {

                counter.textContent =
                    `${notes.value.length} / 2000`;

            }


            notes.addEventListener(
                'input',
                updateCounter
            );


            updateCounter();

        });

    </script>

</x-app-layout>