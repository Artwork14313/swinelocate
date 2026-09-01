<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Edit Swine
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Update the information and current location of this swine.
            </p>
        </div>
    </x-slot>

    <div class="py-8">

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">

                    <div class="font-semibold text-red-800">
                        Please correct the following errors:
                    </div>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>
            @endif


            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                {{-- Header --}}
                <div class="border-b border-gray-200 px-6 py-5">

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ $swine->tag_number }}
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Update swine information
                            </p>
                        </div>

                        <a
                            href="{{ route('swine.show', $swine) }}"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                        >
                            View Swine
                        </a>

                    </div>

                </div>


                {{-- Form --}}
                <form
    method="POST"
    action="{{ route('swine.update', $swine) }}"
    id="swine-edit-form"
    data-swine-id="{{ $swine->id }}"
    data-sync-endpoint="/swine/{{ $swine->id }}/sync"
    data-redirect-url="{{ route('swine.index') }}"
>

                    @csrf
                    @method('PUT')
                    <input
                            type="hidden"
                            name="original_updated_at"
                            value="{{ $swine->updated_at?->toISOString() }}"
                        >

                    <div class="px-6 py-6">

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                            {{-- Farm --}}
                            <div>
                                <label
                                    for="farm_id"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Farm <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="farm_id"
                                    name="farm_id"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">
                                        Select farm
                                    </option>

                                    @foreach ($farms as $farm)
                                        <option
                                            value="{{ $farm->id }}"
                                            @selected(old('farm_id', $swine->farm_id) == $farm->id)
                                        >
                                            {{ $farm->farm_code }} - {{ $farm->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('farm_id')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>


                            {{-- Current Location --}}
                            <div>
                                <label
                                    for="current_location_id"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Current Location
                                </label>

                                <select
                                    id="current_location_id"
                                    name="current_location_id"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">
                                        No location assigned
                                    </option>

                                    @foreach ($locations as $location)
                                        <option
                                            value="{{ $location->id }}"
                                            @selected(
                                                old(
                                                    'current_location_id',
                                                    $swine->current_location_id
                                                ) == $location->id
                                            )
                                        >
                                            {{ $location->location_code }} -
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <p class="mt-1 text-xs text-gray-500">
                                    The current pen or housing location.
                                </p>

                                @error('current_location_id')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>


                            {{-- Tag Number --}}
                            <div>
                                <label
                                    for="tag_number"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Tag Number <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="tag_number"
                                    name="tag_number"
                                    type="text"
                                    value="{{ old('tag_number', $swine->tag_number) }}"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                <p class="mt-1 text-xs text-gray-500">
                                    Must be unique.
                                </p>

                                @error('tag_number')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>


                            {{-- Name --}}
                            <!-- <div>
                                <label
                                    for="name"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Swine Name
                                </label>

                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name', $swine->name) }}"
                                    placeholder="Optional animal name"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div> -->


                            {{-- Sex --}}
                            <div>
                                <label
                                    for="sex"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Sex <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="sex"
                                    name="sex"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">
                                        Select sex
                                    </option>

                                    <option
                                        value="male"
                                        @selected(old('sex', $swine->sex) === 'male')
                                    >
                                        Male
                                    </option>

                                    <option
                                        value="female"
                                        @selected(old('sex', $swine->sex) === 'female')
                                    >
                                        Female
                                    </option>
                                </select>

                                @error('sex')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>


                            {{-- Breed --}}
                            <div>
                                <label
                                    for="breed"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Breed
                                </label>

                                <input
                                    id="breed"
                                    name="breed"
                                    type="text"
                                    value="{{ old('breed', $swine->breed) }}"
                                    placeholder="Example: Large White"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                @error('breed')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>


                            {{-- Birth Date --}}
                            <div>
                                <label
                                    for="birth_date"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Birth Date
                                </label>

                                <input
                                    id="birth_date"
                                    name="birth_date"
                                    type="date"
                                    value="{{ old(
                                        'birth_date',
                                        $swine->birth_date?->format('Y-m-d')
                                    ) }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                @error('birth_date')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>


                            {{-- Acquisition Date --}}
                            <div>
                                <label
                                    for="acquisition_date"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Acquisition Date
                                </label>

                                <input
                                    id="acquisition_date"
                                    name="acquisition_date"
                                    type="date"
                                    value="{{ old(
                                        'acquisition_date',
                                        $swine->acquisition_date?->format('Y-m-d')
                                    ) }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                @error('acquisition_date')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>


                            {{-- Source --}}
                            <div>
                                <label
                                    for="source"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Source
                                </label>

                                <input
                                    id="source"
                                    name="source"
                                    type="text"
                                    value="{{ old('source', $swine->source) }}"
                                    placeholder="Example: Farm breeding"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                @error('source')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>


                            {{-- Status --}}
                            <div>
                                <label
                                    for="status"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Status <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    @foreach ([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'sold' => 'Sold',
                                        'deceased' => 'Deceased',
                                    ] as $value => $label)

                                        <option
                                            value="{{ $value }}"
                                            @selected(old('status', $swine->status) === $value)
                                        >
                                            {{ $label }}
                                        </option>

                                    @endforeach
                                </select>

                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>


                            {{-- Notes --}}
                            <div class="md:col-span-2">

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
                                    placeholder="Additional information..."
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >{{ old('notes', $swine->notes) }}</textarea>

                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </div>


                    {{-- Actions --}}
                    <div class="flex flex-col-reverse gap-3 border-t border-gray-200
                                bg-gray-50 px-6 py-4 sm:flex-row sm:justify-between">

                        

                        <div class="flex flex-col gap-3 sm:flex-row">

                            <a
                                href="{{ route('swine.index') }}"
                                class="rounded-lg border border-gray-300 bg-white
                                       px-4 py-2.5 text-center text-sm font-semibold
                                       text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="rounded-lg bg-indigo-600 px-5 py-2.5
                                       text-sm font-semibold text-white
                                       hover:bg-indigo-700
                                       focus:outline-none focus:ring-2
                                       focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Save Changes
                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>