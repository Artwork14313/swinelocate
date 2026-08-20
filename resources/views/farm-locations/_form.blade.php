<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div>
        <x-input-label
            for="location_code"
            value="Location Code"
        />

        <x-text-input
            id="location_code"
            name="location_code"
            type="text"
            class="mt-1 block w-full"
            value="{{ old('location_code', $location->location_code ?? '') }}"
            placeholder="Example: PEN-001"
            required
        />

        <p class="mt-1 text-xs text-gray-500">
            Must be unique within this farm.
        </p>

        <x-input-error
            :messages="$errors->get('location_code')"
            class="mt-2"
        />
    </div>


    <div>
        <x-input-label
            for="name"
            value="Location Name"
        />

        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-1 block w-full"
            value="{{ old('name', $location->name ?? '') }}"
            placeholder="Example: Grower Pen 1"
            required
        />

        <x-input-error
            :messages="$errors->get('name')"
            class="mt-2"
        />
    </div>


    <div>

        <x-input-label
            for="type"
            value="Location Type"
        />

        <select
            id="type"
            name="type"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >

            <option value="">
                Select type
            </option>

            @foreach([
                'Pen',
                'Nursery',
                'Gestation',
                'Farrowing',
                'Grower',
                'Finisher',
                'Quarantine',
                'Isolation',
                'Boar',
                'Other',
            ] as $type)

                <option
                    value="{{ $type }}"
                    @selected(old('type', $location->type ?? '') === $type)
                >
                    {{ $type }}
                </option>

            @endforeach

        </select>

        <x-input-error
            :messages="$errors->get('type')"
            class="mt-2"
        />

    </div>


    <div>

        <x-input-label
            for="capacity"
            value="Capacity"
        />

        <x-text-input
            id="capacity"
            name="capacity"
            type="number"
            min="1"
            class="mt-1 block w-full"
            value="{{ old('capacity', $location->capacity ?? '') }}"
            placeholder="Example: 50"
        />

        <x-input-error
            :messages="$errors->get('capacity')"
            class="mt-2"
        />

    </div>


    <div class="md:col-span-2">

        <x-input-label
            for="description"
            value="Description"
        />

        <textarea
            id="description"
            name="description"
            rows="4"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Describe this location..."
        >{{ old('description', $location->description ?? '') }}</textarea>

        <x-input-error
            :messages="$errors->get('description')"
            class="mt-2"
        />

    </div>

</div>