<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $farm->name }}
                </h2>

                <p class="text-sm text-gray-500">
                    {{ $farm->farm_code }}
                </p>
            </div>

            <a href="{{ route('farms.edit', $farm) }}"
                class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-semibold hover:bg-gray-700">
                Edit Farm
            </a>

        </div>
    </x-slot>

    <div class="py-8">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Farm Information --}}
            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        Farm Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <p class="text-sm text-gray-500">
                                Farm Code
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->farm_code }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Farm Name
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Municipality / City
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->municipality ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Province
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->province ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Contact Number
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->contact_number ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Email
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->email ?? '—' }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">
                                Address
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->address ?? '—' }}
                            </p>
                        </div>

                    </div>

                </div>

            </div>


            {{-- Statistics --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">
                        Locations
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $farm->locations->count() }}
                    </p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">
                        Swine
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $farm->swine->count() }}
                    </p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">
                        Users
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $farm->users->count() }}
                    </p>
                </div>

            </div>


            {{-- Locations --}}
            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <div class="flex justify-between items-center mb-6">

                        <div class="flex justify-between items-center mb-6">

                            <div>
                                <h3 class="text-lg font-semibold">
                                    Farm Locations
                                </h3>

                                <p class="text-sm text-gray-500">
                                    Manage pens and areas within this farm.
                                </p>
                            </div>

                            @if(auth()->user()->hasPermission('manage-locations'))

                                <a href="{{ route('farms.locations.index', $farm) }}"
                                    class="px-4 py-2 bg-gray-900 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                                    Manage Locations
                                </a>

                            @endif

                        </div>

                        <span class="text-sm text-gray-500">
                            {{ $farm->locations->count() }} location(s)
                        </span>

                    </div>

                    @forelse($farm->locations as $location)

                        <div class="border-b last:border-b-0 py-4">

                            <div class="flex justify-between">

                                <div>
                                    <p class="font-medium">
                                        {{ $location->name }}
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        {{ $location->location_code }}
                                    </p>
                                </div>

                                <span class="text-sm">
                                    {{ ucfirst($location->status) }}
                                </span>

                            </div>

                        </div>

                    @empty

                        <p class="text-gray-500">
                            No locations have been registered.
                        </p>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</x-app-layout>