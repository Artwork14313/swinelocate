<x-app-layout>

    <x-slot name="header">

        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Record Swine Movement
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Record a new location movement for a swine.
            </p>
        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">


                {{-- ==========================================================
                    SWINE INFORMATION / SELECTION
                =========================================================== --}}

                <div class="border-b border-gray-200 bg-gray-50 px-6 py-5">

                    @if ($swine)

                        {{-- Swine already selected from Swine Profile --}}

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

                    @else

                        {{-- Select Swine from Movement Module --}}

                        <label
                            for="swine_id"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Select Swine
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="swine_id"
                            name="swine_id"
                            form="movement-form"
                            required
                            class="mt-2 block w-full rounded-lg border-gray-300
                                   shadow-sm focus:border-indigo-500
                                   focus:ring-indigo-500"
                        >

                            <option value="">
                                Select a swine
                            </option>

                            @foreach ($swines as $item)

                                <option
                                    value="{{ $item->id }}"
                                    @selected(old('swine_id') == $item->id)
                                >

                                    {{ $item->tag_number }}

                                    @if ($item->name)
                                        — {{ $item->name }}
                                    @endif

                                </option>

                            @endforeach

                        </select>

                        @error('swine_id')

                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                    @endif

                </div>


                {{-- ==========================================================
                    FORM
                =========================================================== --}}

                <form
                    id="movement-form"
                    method="POST"
                    action="{{ $swine
                        ? route('swine.movements.store', $swine)
                        : route('swine-movements.store') }}"
                    class="px-6 py-6"
                >

                    @csrf


                    <div class="space-y-6">


                        {{-- ==================================================
                            CURRENT LOCATION
                        =================================================== --}}

                        <div>

                            <label class="block text-sm font-medium text-gray-700">
                                Current Location
                            </label>

                            @if ($swine)

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

                            @else

                                <div
                                    id="current-location"
                                    class="mt-2 rounded-lg bg-gray-100 px-4 py-3"
                                >

                                    <p class="text-sm text-gray-500">
                                        Select a swine first.
                                    </p>

                                </div>

                            @endif

                        </div>


                        {{-- ==================================================
                            DESTINATION
                        =================================================== --}}

                        <div>

                            <label
                                for="to_location_id"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Destination Location

                                <span class="text-red-500">*</span>
                            </label>


                            <select
                                id="to_location_id"
                                name="to_location_id"
                                required
                                @disabled(!$swine)
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500
                                       disabled:bg-gray-100
                                       disabled:text-gray-400"
                            >

                                @if ($swine)

                                    <option value="">
                                        Select destination
                                    </option>

                                    @foreach ($locations as $location)

                                        <option
                                            value="{{ $location->id }}"
                                            @selected(old('to_location_id') == $location->id)
                                        >

                                            {{ $location->name }}

                                            @if ($location->location_code)
                                                — {{ $location->location_code }}
                                            @endif

                                        </option>

                                    @endforeach

                                @else

                                    <option value="">
                                        Select a swine first
                                    </option>

                                @endif

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

                            <label
                                for="movement_date"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Movement Date

                                <span class="text-red-500">*</span>
                            </label>


                            <input
                                type="datetime-local"
                                id="movement_date"
                                name="movement_date"
                                value="{{ old(
                                    'movement_date',
                                    now()->format('Y-m-d\TH:i')
                                ) }}"
                                required
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                            >


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

                            <label
                                for="reason"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Reason
                            </label>


                            <select
                                id="reason"
                                name="reason"
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                            >

                                <option value="">
                                    Select reason
                                </option>


                                <option
                                    value="Growth"
                                    @selected(old('reason') === 'Growth')
                                >
                                    Growth / Development
                                </option>


                                <option
                                    value="Health"
                                    @selected(old('reason') === 'Health')
                                >
                                    Health / Medical
                                </option>


                                <option
                                    value="Breeding"
                                    @selected(old('reason') === 'Breeding')
                                >
                                    Breeding
                                </option>


                                <option
                                    value="Management"
                                    @selected(old('reason') === 'Management')
                                >
                                    Farm Management
                                </option>


                                <option
                                    value="Sale"
                                    @selected(old('reason') === 'Sale')
                                >
                                    Sale / Transfer
                                </option>


                                <option
                                    value="Other"
                                    @selected(old('reason') === 'Other')
                                >
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
                                placeholder="Add additional information about this movement..."
                                class="mt-2 block w-full rounded-lg border-gray-300
                                       shadow-sm focus:border-indigo-500
                                       focus:ring-indigo-500"
                            >{{ old('notes') }}</textarea>


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


                        @if ($swine)

                            <a
                                href="{{ route('swine.show', $swine) }}"
                                class="rounded-lg border border-gray-300
                                       bg-white px-4 py-2 text-sm font-medium
                                       text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </a>

                        @else

                            <a
                                href="{{ route('swine-movements.index') }}"
                                class="rounded-lg border border-gray-300
                                       bg-white px-4 py-2 text-sm font-medium
                                       text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </a>

                        @endif


                        <button
                            type="submit"
                            class="rounded-lg bg-[#3368A0] px-5 py-2
                                   text-sm font-semibold text-white
                                   hover:bg-[#28557F]"
                        >
                            Record Movement
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    {{-- ================================================================
        DYNAMIC SWINE / LOCATION LOADING
    ================================================================= --}}

    @if (!$swine)

        <script>

            document.addEventListener('DOMContentLoaded', function () {

                const swineSelect =
                    document.getElementById('swine_id');

                const locationSelect =
                    document.getElementById('to_location_id');

                const currentLocation =
                    document.getElementById('current-location');


                if (!swineSelect || !locationSelect) {
                    return;
                }


                swineSelect.addEventListener('change', async function () {

                    const swineId = this.value;


                    /*
                    |--------------------------------------------------------------------------
                    | No swine selected
                    |--------------------------------------------------------------------------
                    */

                    if (!swineId) {

                        locationSelect.innerHTML = `
                            <option value="">
                                Select a swine first
                            </option>
                        `;

                        locationSelect.disabled = true;


                        if (currentLocation) {

                            currentLocation.innerHTML = `
                                <p class="text-sm text-gray-500">
                                    Select a swine first.
                                </p>
                            `;

                        }

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Loading
                    |--------------------------------------------------------------------------
                    */

                    locationSelect.innerHTML = `
                        <option value="">
                            Loading locations...
                        </option>
                    `;

                    locationSelect.disabled = true;


                    if (currentLocation) {

                        currentLocation.innerHTML = `
                            <p class="text-sm text-gray-500">
                                Loading current location...
                            </p>
                        `;

                    }


                    try {

                        const response = await fetch(
                            `/swine-movements/${swineId}/locations`,
                            {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            }
                        );


                        if (!response.ok) {
                            throw new Error(
                                'Failed to load locations.'
                            );
                        }


                        const data = await response.json();


                        /*
                        |--------------------------------------------------------------------------
                        | Current Location
                        |--------------------------------------------------------------------------
                        */

                        if (currentLocation) {

                            if (data.current_location) {

                                currentLocation.innerHTML = `

                                    <p class="text-sm font-medium text-gray-900">
                                        ${data.current_location.name}
                                    </p>

                                    ${
                                        data.current_location.location_code
                                            ? `
                                                <p class="mt-1 text-xs text-gray-500">
                                                    ${data.current_location.location_code}
                                                </p>
                                            `
                                            : ''
                                    }

                                `;

                            } else {

                                currentLocation.innerHTML = `

                                    <p class="text-sm text-gray-500">
                                        No location assigned
                                    </p>

                                `;

                            }

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Destination Locations
                        |--------------------------------------------------------------------------
                        */

                        locationSelect.innerHTML = `

                            <option value="">
                                Select destination
                            </option>

                        `;


                        if (!data.locations.length) {

                            locationSelect.innerHTML = `

                                <option value="">
                                    No active locations available
                                </option>

                            `;

                            locationSelect.disabled = true;

                            return;
                        }


                        data.locations.forEach(function (location) {

                            const option =
                                document.createElement('option');


                            option.value = location.id;


                            option.textContent =
                                location.name +
                                (
                                    location.location_code
                                        ? ` — ${location.location_code}`
                                        : ''
                                );


                            locationSelect.appendChild(option);

                        });


                        locationSelect.disabled = false;

                    } catch (error) {

                        console.error(error);


                        locationSelect.innerHTML = `

                            <option value="">
                                Unable to load locations
                            </option>

                        `;

                        locationSelect.disabled = true;


                        if (currentLocation) {

                            currentLocation.innerHTML = `

                                <p class="text-sm text-red-600">
                                    Unable to load current location.
                                </p>

                            `;

                        }

                    }

                });

            });

        </script>

    @endif

</x-app-layout>