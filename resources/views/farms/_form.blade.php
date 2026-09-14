<div class="grid grid-cols-1 md:grid-cols-2 gap-6">


{{-- Farm Code --}}
<div>
    <x-input-label for="farm_code" value="Farm Code" />

    <x-text-input
        id="farm_code"
        name="farm_code"
        type="text"
        class="mt-1 block w-full"
        value="{{ old('farm_code', $farm->farm_code ?? '') }}"
        required
    />

    <x-input-error
        :messages="$errors->get('farm_code')"
        class="mt-2"
    />
</div>


{{-- Farm Name --}}
<div>
    <x-input-label for="name" value="Farm Name" />

    <x-text-input
        id="name"
        name="name"
        type="text"
        class="mt-1 block w-full"
        value="{{ old('name', $farm->name ?? '') }}"
        required
    />

    <x-input-error
        :messages="$errors->get('name')"
        class="mt-2"
    />
</div>


{{-- Complete Address --}}
<div class="md:col-span-2">

    <x-input-label
        for="address"
        value="Complete Address"
    />

    <textarea
        id="address"
        name="address"
        rows="3"
        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
    >{{ old('address', $farm->address ?? '') }}</textarea>

    <x-input-error
        :messages="$errors->get('address')"
        class="mt-2"
    />

</div>


{{-- Municipality / City --}}
<div>

    <x-input-label
        for="municipality"
        value="Municipality / City"
    />

    <x-text-input
        id="municipality"
        name="municipality"
        type="text"
        class="mt-1 block w-full"
        value="{{ old('municipality', $farm->municipality ?? '') }}"
    />

    <x-input-error
        :messages="$errors->get('municipality')"
        class="mt-2"
    />

</div>


{{-- Province --}}
<div>

    <x-input-label
        for="province"
        value="Province"
    />

    <x-text-input
        id="province"
        name="province"
        type="text"
        class="mt-1 block w-full"
        value="{{ old('province', $farm->province ?? '') }}"
    />

    <x-input-error
        :messages="$errors->get('province')"
        class="mt-2"
    />

</div>


{{-- Region --}}
<div>

    <x-input-label
        for="region"
        value="Region"
    />

    <x-text-input
        id="region"
        name="region"
        type="text"
        class="mt-1 block w-full"
        value="{{ old('region', $farm->region ?? '') }}"
    />

    <x-input-error
        :messages="$errors->get('region')"
        class="mt-2"
    />

</div>


{{-- Contact Number --}}
<div>

    <x-input-label
        for="contact_number"
        value="Contact Number"
    />

    <x-text-input
        id="contact_number"
        name="contact_number"
        type="text"
        class="mt-1 block w-full"
        value="{{ old('contact_number', $farm->contact_number ?? '') }}"
    />

    <x-input-error
        :messages="$errors->get('contact_number')"
        class="mt-2"
    />

</div>


{{-- Email --}}
<div>

    <x-input-label
        for="email"
        value="Email Address"
    />

    <x-text-input
        id="email"
        name="email"
        type="email"
        class="mt-1 block w-full"
        value="{{ old('email', $farm->email ?? '') }}"
    />

    <x-input-error
        :messages="$errors->get('email')"
        class="mt-2"
    />

</div>


{{-- Latitude --}}
<div>

    <x-input-label
        for="latitude"
        value="Latitude"
    />

    <x-text-input
        id="latitude"
        name="latitude"
        type="number"
        step="any"
        class="mt-1 block w-full"
        value="{{ old('latitude', $farm->latitude ?? '') }}"
    />

    <x-input-error
        :messages="$errors->get('latitude')"
        class="mt-2"
    />

</div>


{{-- Longitude --}}
<div>

    <x-input-label
        for="longitude"
        value="Longitude"
    />

    <x-text-input
        id="longitude"
        name="longitude"
        type="number"
        step="any"
        class="mt-1 block w-full"
        value="{{ old('longitude', $farm->longitude ?? '') }}"
    />

    <x-input-error
        :messages="$errors->get('longitude')"
        class="mt-2"
    />

</div>


</div>
