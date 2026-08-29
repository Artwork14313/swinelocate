<x-app-layout>

    <x-slot name="header">

        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Record Swine Movement
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Record a new location movement for this swine.
            </p>
        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">


                {{-- ==========================================================
                SWINE INFORMATION
                =========================================================== --}}

                <div class="border-b border-gray-200 bg-gray-50 px-6 py-5">

                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Selected Swine
                    </p>

                    <h3 class="mt-1 text-xl font-bold text-gray-900">
                        {{ $swine->tag_number }}
                    </h3>

                    @if ($swine->name)

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $swine->name }}
                        </p>

                    @endif

                </div>


                {{-- ==========================================================
                FORM
                =========================================================== --}}

                <form id="swine-movement-form" method="POST" action="{{ route('swine-movements.store', $swine) }}"
                    data-swine-id="{{ $swine->id }}">

                    @csrf


                    <div class="space-y-6">


                        {{-- ==================================================
                        CURRENT LOCATION
                        =================================================== --}}

                        <div>

                            <label class="block text-sm font-medium text-gray-700">
                                Current Location
                            </label>

                            <div class="mt-2 rounded-lg bg-gray-100 px-4 py-3">

                                <p class="text-sm font-medium text-gray-900">
                                    {{ $swine->currentLocation?->name ?? 'No location assigned' }}
                                </p>

                                @if ($swine->currentLocation?->location_code)

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $swine->currentLocation->location_code }}
                                    </p>

                                @endif

                            </div>

                        </div>


                        {{-- ==================================================
                        DESTINATION
                        =================================================== --}}

                        <div>

                            <label for="to_location_id" class="block text-sm font-medium text-gray-700">
                                Destination Location

                                <span class="text-red-500">*</span>
                            </label>


                            <select id="to_location_id" name="to_location_id" required class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500">

                                <option value="">
                                    Select destination
                                </option>


                                @foreach ($locations as $location)

                                    <option value="{{ $location->id }}" @selected(
                                        old('to_location_id') == $location->id
                                    )>

                                        {{ $location->name }}

                                        @if ($location->location_code)

                                            — {{ $location->location_code }}

                                        @endif

                                    </option>

                                @endforeach

                            </select>


                            @error('to_location_id')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- ==================================================
                        MOVEMENT DATE
                        =================================================== --}}

                        <div>

                            <label for="movement_date" class="block text-sm font-medium text-gray-700">
                                Movement Date

                                <span class="text-red-500">*</span>
                            </label>


                            <input type="datetime-local" id="movement_date" name="movement_date" value="{{ old(
    'movement_date',
    now()->format('Y-m-d\TH:i')
) }}" required class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500">


                            @error('movement_date')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- ==================================================
                        REASON
                        =================================================== --}}

                        <div>

                            <label for="reason" class="block text-sm font-medium text-gray-700">
                                Reason
                            </label>


                            <select id="reason" name="reason" class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500">

                                <option value="">
                                    Select reason
                                </option>

                                <option value="Growth" @selected(old('reason') === 'Growth')>
                                    Growth / Development
                                </option>

                                <option value="Health" @selected(old('reason') === 'Health')>
                                    Health / Medical
                                </option>

                                <option value="Breeding" @selected(old('reason') === 'Breeding')>
                                    Breeding
                                </option>

                                <option value="Management" @selected(old('reason') === 'Management')>
                                    Farm Management
                                </option>

                                <option value="Sale" @selected(old('reason') === 'Sale')>
                                    Sale / Transfer
                                </option>

                                <option value="Other" @selected(old('reason') === 'Other')>
                                    Other
                                </option>

                            </select>


                            @error('reason')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- ==================================================
                        NOTES
                        =================================================== --}}

                        <div>

                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Notes
                            </label>


                            <textarea id="notes" name="notes" rows="4"
                                placeholder="Add additional information about this movement..." class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500">{{ old('notes') }}</textarea>


                            @error('notes')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>


                    {{-- ======================================================
                    ACTIONS
                    ======================================================= --}}

                    <div class="mt-8 flex items-center justify-end gap-3">

                        <a href="{{ route('swine-movements.index') }}" class="rounded-lg border border-gray-300
                                   bg-white px-4 py-2 text-sm font-medium
                                   text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>


                        <button type="submit" class="rounded-lg bg-[#3368A0] px-5 py-2
                                   text-sm font-semibold text-white
                                   hover:bg-[#28557F]">
                            Record Movement
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>