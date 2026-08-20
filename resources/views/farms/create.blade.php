<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Register Farm
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Register a new farm and its basic information.
            </p>
        </div>
    </x-slot>


    <div class="py-8">

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Validation Errors --}}
            @if ($errors->any())

                <div class="mb-6 rounded-lg border border-red-200
                            bg-red-50 p-4">

                    <div class="flex">

                        <div class="ml-3">

                            <h3 class="text-sm font-semibold text-red-800">
                                Please correct the following errors:
                            </h3>

                            <ul class="mt-2 list-disc list-inside
                                       text-sm text-red-700">

                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            {{-- Form Card --}}
            <div class="overflow-hidden rounded-xl bg-white
                        shadow-sm ring-1 ring-gray-200">

                {{-- Card Header --}}
                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Farm Information
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Enter the basic information of the swine farm.
                    </p>

                </div>


                {{-- Form --}}
                <form
                    method="POST"
                    action="{{ route('farms.store') }}"
                >

                    @csrf

                    <div class="px-6 py-6">

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                            {{-- Farm Code --}}
                            <div>

                                <label
                                    for="farm_code"
                                    class="block text-sm font-medium
                                           text-gray-700"
                                >
                                    Farm Code
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="farm_code"
                                    name="farm_code"
                                    type="text"
                                    value="{{ old('farm_code') }}"
                                    placeholder="Example: FARM-001"
                                    required
                                    class="mt-1 block w-full rounded-lg
                                           border-gray-300 shadow-sm
                                           focus:border-indigo-500
                                           focus:ring-indigo-500"
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
                                    class="block text-sm font-medium
                                           text-gray-700"
                                >
                                    Farm Name
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name') }}"
                                    placeholder="Example: Demo Swine Farm"
                                    required
                                    class="mt-1 block w-full rounded-lg
                                           border-gray-300 shadow-sm
                                           focus:border-indigo-500
                                           focus:ring-indigo-500"
                                >

                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Complete Address --}}
                            <div class="md:col-span-2">

                                <label
                                    for="address"
                                    class="block text-sm font-medium
                                           text-gray-700"
                                >
                                    Complete Address
                                    <span class="text-red-500">*</span>
                                </label>

                                <textarea
                                    id="address"
                                    name="address"
                                    rows="3"
                                    placeholder="House/Street, Barangay, and other address details"
                                    required
                                    class="mt-1 block w-full rounded-lg
                                           border-gray-300 shadow-sm
                                           focus:border-indigo-500
                                           focus:ring-indigo-500"
                                >{{ old('address') }}</textarea>

                                @error('address')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Municipality / City --}}
                            <div>

                                <label
                                    for="city"
                                    class="block text-sm font-medium
                                           text-gray-700"
                                >
                                    Municipality / City
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="city"
                                    name="city"
                                    type="text"
                                    value="{{ old('city') }}"
                                    placeholder="Example: Davao City"
                                    required
                                    class="mt-1 block w-full rounded-lg
                                           border-gray-300 shadow-sm
                                           focus:border-indigo-500
                                           focus:ring-indigo-500"
                                >

                                @error('city')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Province --}}
                            <div>

                                <label
                                    for="province"
                                    class="block text-sm font-medium
                                           text-gray-700"
                                >
                                    Province
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="province"
                                    name="province"
                                    type="text"
                                    value="{{ old('province') }}"
                                    placeholder="Example: Davao del Sur"
                                    required
                                    class="mt-1 block w-full rounded-lg
                                           border-gray-300 shadow-sm
                                           focus:border-indigo-500
                                           focus:ring-indigo-500"
                                >

                                @error('province')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Contact Number --}}
                            <div>

                                <label
                                    for="contact_number"
                                    class="block text-sm font-medium
                                           text-gray-700"
                                >
                                    Contact Number
                                </label>

                                <input
                                    id="contact_number"
                                    name="contact_number"
                                    type="text"
                                    value="{{ old('contact_number') }}"
                                    placeholder="Example: 09123456789"
                                    class="mt-1 block w-full rounded-lg
                                           border-gray-300 shadow-sm
                                           focus:border-indigo-500
                                           focus:ring-indigo-500"
                                >

                                @error('contact_number')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- Status --}}
                            <div>

                                <label
                                    for="status"
                                    class="block text-sm font-medium
                                           text-gray-700"
                                >
                                    Status
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    class="mt-1 block w-full rounded-lg
                                           border-gray-300 shadow-sm
                                           focus:border-indigo-500
                                           focus:ring-indigo-500"
                                >

                                    <option
                                        value="active"
                                        @selected(old('status', 'active') === 'active')
                                    >
                                        Active
                                    </option>

                                    <option
                                        value="inactive"
                                        @selected(old('status') === 'inactive')
                                    >
                                        Inactive
                                    </option>

                                </select>

                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </div>


                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-3
                                border-t border-gray-200 bg-gray-50
                                px-6 py-4">

                        <a
                            href="{{ route('farms.index') }}"
                            class="rounded-lg border border-gray-300
                                   bg-white px-4 py-2.5 text-sm
                                   font-semibold text-gray-700
                                   hover:bg-gray-50 transition"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="rounded-lg bg-indigo-600 px-5 py-2.5
                                   text-sm font-semibold text-white
                                   shadow-sm hover:bg-indigo-700
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-indigo-500
                                   focus:ring-offset-2 transition"
                        >
                            Register Farm
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>