<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="farm_id" class="block text-sm font-medium text-gray-700">Farm <span class="text-red-500">*</span></label>
        <select id="farm_id" name="farm_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
            <option value="">Select farm</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected(old('farm_id', $farmer->farm_id) == $farm->id)>{{ $farm->farm_code }} — {{ $farm->name }}</option>
            @endforeach
        </select>
        @error('farm_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700">Farmer Name <span class="text-red-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $farmer->name) }}" required maxlength="255" autocomplete="name" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number <span class="text-red-500">*</span></label>
        <input id="contact_number" name="contact_number" type="tel" value="{{ old('contact_number', $farmer->contact_number) }}" required maxlength="30" autocomplete="tel" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
        @error('contact_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $farmer->email) }}" maxlength="255" autocomplete="email" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="block text-sm font-medium text-gray-700">Farmer Address <span class="text-red-500">*</span></label>
        <textarea id="address" name="address" rows="3" required maxlength="2000" autocomplete="street-address" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">{{ old('address', $farmer->address) }}</textarea>
        @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
