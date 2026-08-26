<x-app-layout>

    <x-slot name="header">

        <div>

            <h2 class="text-2xl font-bold text-gray-900">
                Edit Weight Record
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Update the recorded weight information for this swine.
            </p>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            {{-- Validation Errors --}}
            @if ($errors->any())

                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-4">

                    <p class="text-sm font-semibold text-red-800">
                        Please correct the following errors:
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif


            <form
                method="POST"
                action="{{ route('weight-records.update', $weightRecord) }}"
                class="space-y-6"
            >

                @csrf
                @method('PUT')


                {{-- Weight Information --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Weight Information
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Update the weight information recorded for this swine.
                        </p>

                    </div>


                    <div class="space-y-5 px-6 py-6">


                        {{-- Swine --}}
                        <div>

                            <label class="block text-sm font-medium text-gray-700">
                                Swine
                            </label>

                            <div class="mt-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">

                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $weightRecord->swine?->tag_number ?? 'Unknown' }}
                                </p>

                                @if ($weightRecord->swine?->name)

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $weightRecord->swine->name }}
                                    </p>

                                @endif

                            </div>

                            <p class="mt-1 text-xs text-gray-500">
                                The swine associated with this historical record cannot be changed.
                            </p>

                        </div>


                        {{-- Record Date --}}
                        <div>

                            <label
                                for="record_date"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Record Date
                            </label>

                            <input
                                type="date"
                                id="record_date"
                                name="record_date"
                                value="{{ old('record_date', $weightRecord->record_date?->format('Y-m-d')) }}"
                                max="{{ now()->format('Y-m-d') }}"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
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
                            </label>

                            <div class="relative mt-2">

                                <input
                                    type="number"
                                    id="weight"
                                    name="weight"
                                    value="{{ old('weight', $weightRecord->weight) }}"
                                    step="0.01"
                                    min="0.01"
                                    max="9999.99"
                                    class="block w-full rounded-lg border-gray-300 pr-14 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="e.g. 45.50"
                                >

                                <span
                                    class="absolute inset-y-0 right-0 flex items-center pr-4
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
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Optional notes about the weighing..."
                            >{{ old('notes', $weightRecord->notes) }}</textarea>

                            @error('notes')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Record Information --}}
                <div class="rounded-xl bg-gray-50 p-5 ring-1 ring-gray-200">

                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Record Information
                    </p>

                    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>

                            <p class="text-xs text-gray-500">
                                Recorded By
                            </p>

                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ $weightRecord->recordedBy?->name ?? 'Unknown' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-gray-500">
                                Last Updated
                            </p>

                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ $weightRecord->updated_at?->format('M d, Y h:i A') ?? '—' }}
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Actions --}}
                <div class="flex items-center justify-between gap-3">

                    <a
                        href="{{ route('weight-records.show', $weightRecord) }}"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2
                               text-sm font-semibold text-gray-700 shadow-sm
                               hover:bg-gray-50"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="rounded-lg bg-[#3368A0] px-5 py-2
                               text-sm font-semibold text-white shadow-sm
                               hover:bg-[#28557F]"
                    >
                        Update Weight Record
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>