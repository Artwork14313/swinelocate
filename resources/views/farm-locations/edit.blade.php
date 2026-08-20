<x-app-layout>

    <x-slot name="header">

        <div>

            <h2 class="font-semibold text-xl text-gray-800">
                Edit Farm Location
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                {{ $farm->name }}
                ·
                {{ $location->location_code }}
            </p>

        </div>

    </x-slot>

    <div class="py-8">

        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <form
                        method="POST"
                        action="{{ route('farms.locations.update', [$farm, $location]) }}"
                    >

                        @csrf
                        @method('PUT')

                        @include('farm-locations._form')

                        <div class="mt-8">

                            <x-input-label
                                for="status"
                                value="Status"
                            />

                            <select
                                id="status"
                                name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >

                                <option
                                    value="active"
                                    @selected(old('status', $location->status) === 'active')
                                >
                                    Active
                                </option>

                                <option
                                    value="inactive"
                                    @selected(old('status', $location->status) === 'inactive')
                                >
                                    Inactive
                                </option>

                            </select>

                            <x-input-error
                                :messages="$errors->get('status')"
                                class="mt-2"
                            />

                        </div>


                        <div class="mt-8 flex items-center justify-end gap-3">

                            <a
                                href="{{ route('farms.locations.show', [$farm, $location]) }}"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-semibold hover:bg-gray-700"
                            >
                                Save Changes
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>