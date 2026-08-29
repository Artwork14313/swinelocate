<x-app-layout>

    <x-slot name="header">

        <div>

            <h2 class="text-2xl font-bold text-gray-900">
                Register Farm
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Add a new farm to the SwineLocate system.
            </p>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            <form
                method="POST"
                action="{{ route('farms.store') }}"
                class="space-y-6"
            >

                @csrf


                {{-- Farm Information --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Farm Information
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Enter the basic information of the farm.
                        </p>

                    </div>


                    <div class="grid grid-cols-1 gap-5 px-6 py-6 sm:grid-cols-2">


                        {{-- Farm Code --}}
                        <div>

                            <label
                                for="farm_code"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Farm Code
                            </label>

                            <input
                                type="text"
                                id="farm_code"
                                name="farm_code"
                                value="{{ old('farm_code') }}"
                                maxlength="50"
                                required
                                placeholder="e.g. FARM-001"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            @error('farm_code')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Farm Name --}}
                        <div>

                            <label
                                for="name"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Farm Name
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                maxlength="255"
                                required
                                placeholder="Enter farm name"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            @error('name')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                    </div>

                </div>


                {{-- Location Information --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Location Information
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Enter the farm's location and geographic coordinates.
                        </p>

                    </div>


                    <div class="space-y-5 px-6 py-6">


                        {{-- Address --}}
                        <div>

                            <label
                                for="address"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Address
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Enter complete farm address"
                            >{{ old('address') }}</textarea>

                            @error('address')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">


                            {{-- Municipality --}}
                            <div>

                                <label
                                    for="municipality"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Municipality
                                </label>

                                <input
                                    type="text"
                                    id="municipality"
                                    name="municipality"
                                    value="{{ old('municipality') }}"
                                    maxlength="100"
                                    placeholder="e.g. Escalante City"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                @error('municipality')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Province --}}
                            <div>

                                <label
                                    for="province"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Province
                                </label>

                                <input
                                    type="text"
                                    id="province"
                                    name="province"
                                    value="{{ old('province') }}"
                                    maxlength="100"
                                    placeholder="Enter province"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                @error('province')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Region --}}
                            <div>

                                <label
                                    for="region"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Region
                                </label>

                                <input
                                    type="text"
                                    id="region"
                                    name="region"
                                    value="{{ old('region') }}"
                                    maxlength="100"
                                    placeholder="Enter region"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                @error('region')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>


                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">


                            {{-- Latitude --}}
                            <div>

                                <label
                                    for="latitude"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Latitude
                                </label>

                                <input
                                    type="number"
                                    id="latitude"
                                    name="latitude"
                                    value="{{ old('latitude') }}"
                                    step="any"
                                    min="-90"
                                    max="90"
                                    placeholder="e.g. 10.8505"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                <p class="mt-1 text-xs text-gray-500">
                                    Must be between -90 and 90.
                                </p>

                                @error('latitude')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Longitude --}}
                            <div>

                                <label
                                    for="longitude"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Longitude
                            </label>

                                <input
                                    type="number"
                                    id="longitude"
                                    name="longitude"
                                    value="{{ old('longitude') }}"
                                    step="any"
                                    min="-180"
                                    max="180"
                                    placeholder="e.g. 123.8854"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                >

                                <p class="mt-1 text-xs text-gray-500">
                                    Must be between -180 and 180.
                                </p>

                                @error('longitude')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Contact Information --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Contact Information
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Enter the farm's contact details.
                        </p>

                    </div>


                    <div class="grid grid-cols-1 gap-5 px-6 py-6 sm:grid-cols-2">


                        {{-- Contact Number --}}
                        <div>

                            <label
                                for="contact_number"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Contact Number
                            </label>

                            <input
                                type="text"
                                id="contact_number"
                                name="contact_number"
                                value="{{ old('contact_number') }}"
                                maxlength="30"
                                placeholder="e.g. 09123456789"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            @error('contact_number')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Email --}}
                        <div>

                            <label
                                for="email"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                maxlength="255"
                                placeholder="e.g. farm@example.com"
                                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500"
                            >

                            @error('email')
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
                        href="{{ route('farms.index') }}"
                        class="rounded-lg border border-gray-300 bg-white
                               px-4 py-2 text-sm font-semibold text-gray-700
                               shadow-sm hover:bg-gray-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="rounded-lg bg-[#3368A0] px-5 py-2
                               text-sm font-semibold text-white shadow-sm
                               hover:bg-[#28557F]"
                    >
                        Register Farm
                    </button>

                </div>


            </form>

        </div>

    </div>

</x-app-layout>