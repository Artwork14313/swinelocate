<x-app-layout>

    <x-slot name="header">

        <div>

            <h2 class="font-semibold text-xl text-gray-800">
                Register Farm Location
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                {{ $farm->name }}
                ·
                {{ $farm->farm_code }}
            </p>

        </div>

    </x-slot>

    <div class="py-8">

        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <form
                        method="POST"
                        action="{{ route('farms.locations.store', $farm) }}"
                    >

                        @csrf

                        @include('farm-locations._form')

                        <div class="mt-8 flex items-center justify-end gap-3">

                            <a
                                href="{{ route('farms.locations.index', $farm) }}"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-semibold hover:bg-gray-700"
                            >
                                Register Location
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>