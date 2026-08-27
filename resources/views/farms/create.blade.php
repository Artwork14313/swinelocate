<x-app-layout>

    <x-slot name="header">

        <div>

            <h2 class="text-2xl font-bold text-gray-900">
                Add Weight Record
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Record the current weight of a swine.
            </p>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            {{-- Offline Status --}}
            <div
                id="offline-status"
                class="mb-6 hidden rounded-lg border px-4 py-3 text-sm"
            ></div>


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
                            Enter the swine and its recorded weight.
                        </p>

                    </div>


                    <div class="space-y-5 px-6 py-6">


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
                                            old('swine_id', $selectedSwine?->id) == $swine->id
                                        )
                                    >

                                        {{ $swine->tag_number }}

                                        @if ($swine->name)
                                            — {{ $swine->name }}
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
                                value="{{ old('record_date', now()->format('Y-m-d')) }}"
                                max="{{ now()->format('Y-m-d') }}"
                                required
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                            >

                            <p class="mt-1 text-xs text-gray-500">
                                Date when the swine was weighed.
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

                            @error('notes')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3">

                    <a
                        href="{{ route('weight-records.index') }}"
                        class="rounded-lg border border-gray-300 bg-white
                               px-4 py-2 text-sm font-semibold text-gray-700
                               shadow-sm hover:bg-gray-50"
                    >
                        Cancel
                    </a>

                    <button
                        id="save-weight-record"
                        type="submit"
                        class="rounded-lg bg-[#3368A0] px-5 py-2
                               text-sm font-semibold text-white shadow-sm
                               hover:bg-[#28557F]"
                    >
                        Save Weight Record
                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- ================================================================
        OFFLINE WEIGHT RECORD SUPPORT
    ================================================================= --}}

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const form = document.getElementById(
                'weight-record-form'
            );

            const button = document.getElementById(
                'save-weight-record'
            );

            const statusBox = document.getElementById(
                'offline-status'
            );


            if (!form) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Display connection status
            |--------------------------------------------------------------------------
            */

            function updateConnectionStatus() {

                if (navigator.onLine) {

                    statusBox.classList.add('hidden');

                    return;
                }


                statusBox.classList.remove('hidden');

                statusBox.className =
                    'mb-6 rounded-lg border border-yellow-200 ' +
                    'bg-yellow-50 px-4 py-3 text-sm text-yellow-800';

                statusBox.textContent =
                    'You are offline. Weight records will be saved ' +
                    'locally and synchronized when the connection returns.';

            }


            updateConnectionStatus();


            window.addEventListener(
                'offline',
                updateConnectionStatus
            );

            window.addEventListener(
                'online',
                updateConnectionStatus
            );


            /*
            |--------------------------------------------------------------------------
            | Form submission
            |--------------------------------------------------------------------------
            */

            form.addEventListener(
                'submit',
                async function (event) {

                    /*
                     * Let the browser handle normal
                     * validation first.
                     */
                    if (!form.checkValidity()) {

                        return;
                    }


                    /*
                     * If internet is available,
                     * allow normal Laravel submission.
                     */
                    if (navigator.onLine) {

                        return;
                    }


                    /*
                     * Prevent normal form submission
                     * when offline.
                     */
                    event.preventDefault();


                    if (
                        !window.SwineLocateOffline ||
                        !window.SwineLocateOffline.addToSyncQueue
                    ) {

                        alert(
                            'Offline storage is not available. ' +
                            'Please reconnect to the internet and try again.'
                        );

                        return;
                    }


                    const formData = new FormData(form);


                    const payload = {

                        swine_id:
                            formData.get('swine_id'),

                        record_date:
                            formData.get('record_date'),

                        weight:
                            formData.get('weight'),

                        notes:
                            formData.get('notes'),

                    };


                    /*
                     * Disable button while saving
                     * to IndexedDB.
                     */
                    button.disabled = true;

                    button.textContent =
                        'Saving Offline...';


                    try {

                        await window.SwineLocateOffline
                            .addToSyncQueue({

                                type: 'weight_record',

                                endpoint:
                                    '{{ route('weight-records.sync') }}',

                                method: 'POST',

                                payload: payload,

                            });


                        /*
                         * Show success message.
                         */
                        statusBox.classList.remove('hidden');

                        statusBox.className =
                            'mb-6 rounded-lg border border-blue-200 ' +
                            'bg-blue-50 px-4 py-3 text-sm text-blue-800';

                        statusBox.textContent =
                            'Weight record saved offline. ' +
                            'It will automatically synchronize ' +
                            'when the internet connection returns.';


                        button.textContent =
                            'Saved Offline';


                        /*
                         * Reset form after successful
                         * local storage.
                         */
                        form.reset();


                        /*
                         * Restore default date.
                         */
                        document.getElementById(
                            'record_date'
                        ).value =
                            '{{ now()->format('Y-m-d') }}';


                    } catch (error) {

                        console.error(
                            'Offline weight record error:',
                            error
                        );


                        alert(
                            'Unable to save the weight record offline. ' +
                            'Please try again.'
                        );


                        button.disabled = false;

                        button.textContent =
                            'Save Weight Record';

                    }

                }
            );

        });

    </script>

</x-app-layout>