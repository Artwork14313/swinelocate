<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="font-semibold text-xl text-gray-800">
                    {{ $location->name }}
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $location->location_code }}
                    ·
                    {{ $farm->name }}
                </p>

            </div>

            <a
                href="{{ route('farms.locations.edit', [$farm, $location]) }}"
                class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-semibold hover:bg-gray-700"
            >
                Edit Location
            </a>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">


            @if(session('success'))

                <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-700">
                    {{ session('success') }}
                </div>

            @endif


            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        Location Information
                    </h3>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>

                            <p class="text-sm text-gray-500">
                                Location Code
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $location->location_code }}
                            </p>

                        </div>


                        <div>

                            <p class="text-sm text-gray-500">
                                Location Name
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $location->name }}
                            </p>

                        </div>


                        <div>

                            <p class="text-sm text-gray-500">
                                Type
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $location->type ?? '—' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-sm text-gray-500">
                                Capacity
                            </p>

                            <p class="mt-1 font-medium text-gray-900">
                                {{ $location->capacity ?? '—' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-sm text-gray-500">
                                Status
                            </p>

                            <div class="mt-1">

                                @if($location->status === 'active')

                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                        Active
                                    </span>

                                @else

                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                        Inactive
                                    </span>

                                @endif

                            </div>

                        </div>


                        <div>

                            <p class="text-sm text-gray-500">
                                Farm
                            </p>

                            <a
                                href="{{ route('farms.show', $farm) }}"
                                class="mt-1 inline-block font-medium text-blue-600 hover:text-blue-800"
                            >
                                {{ $farm->name }}
                            </a>

                        </div>


                        <div class="md:col-span-2">

                            <p class="text-sm text-gray-500">
                                Description
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $location->description ?? 'No description provided.' }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <div>

                <a
                    href="{{ route('farms.locations.index', $farm) }}"
                    class="text-sm text-gray-600 hover:text-gray-900"
                >
                    ← Back to Locations
                </a>

            </div>

        </div>

    </div>

</x-app-layout>