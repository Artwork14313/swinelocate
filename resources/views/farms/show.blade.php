<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <div class="flex items-center gap-3">

                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        {{ $farm->name }}
                    </h2>

                    {{-- Farm Status --}}
                    @if($farm->status === 'active')

                        <span class="inline-flex items-center rounded-full
                                         bg-green-100 px-2.5 py-1
                                         text-xs font-semibold text-green-700">

                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-green-500"></span>

                            Active

                        </span>

                    @else

                        <span class="inline-flex items-center rounded-full
                                         bg-gray-100 px-2.5 py-1
                                         text-xs font-semibold text-gray-600">

                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-gray-400"></span>

                            Inactive

                        </span>

                    @endif

                </div>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $farm->farm_code }}
                </p>
            </div>


            <div class="flex items-center gap-2">

                <a href="{{ route('farms.index') }}" class="rounded-md border border-gray-300
                          bg-white px-4 py-2 text-sm font-semibold
                          text-gray-700 hover:bg-gray-50">
                    Back
                </a>

                <a href="{{ route('farms.edit', $farm) }}" class="rounded-md bg-gray-900 px-4 py-2
                          text-sm font-semibold text-white
                          hover:bg-gray-700">
                    Edit Farm
                </a>

            </div>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">


            {{-- Inactive Farm Notice --}}
            @if($farm->status === 'inactive')

                <div class="rounded-lg border border-gray-200
                                bg-gray-50 px-4 py-4">

                    <div class="flex items-start gap-3">

                        <div class="mt-0.5 flex h-8 w-8 shrink-0
                                        items-center justify-center
                                        rounded-full bg-gray-200">

                            <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />

                            </svg>

                        </div>


                        <div>

                            <p class="text-sm font-semibold text-gray-800">
                                This farm is inactive.
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                New locations and new swine registrations
                                are not allowed until this farm is activated.
                            </p>

                        </div>

                    </div>

                </div>

            @endif


            {{-- Farm Information --}}
            <div class="rounded-lg bg-white shadow-sm">

                <div class="p-6">

                    <h3 class="mb-6 text-lg font-semibold text-gray-900">
                        Farm Information
                    </h3>


                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">


                        {{-- Farm Code --}}
                        <div>

                            <p class="text-sm text-gray-500">
                                Farm Code
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->farm_code }}
                            </p>

                        </div>


                        {{-- Farm Name --}}
                        <div>

                            <p class="text-sm text-gray-500">
                                Farm Name
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->name }}
                            </p>

                        </div>


                        {{-- Municipality --}}
                        <div>

                            <p class="text-sm text-gray-500">
                                Municipality / City
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->municipality ?? '—' }}
                            </p>

                        </div>


                        {{-- Province --}}
                        <div>

                            <p class="text-sm text-gray-500">
                                Province
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->province ?? '—' }}
                            </p>

                        </div>


                        {{-- Region --}}
                        <div>

                            <p class="text-sm text-gray-500">
                                Region
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->region ?? '—' }}
                            </p>

                        </div>


                        {{-- Contact Number --}}
                        <div>

                            <p class="text-sm text-gray-500">
                                Contact Number
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->contact_number ?? '—' }}
                            </p>

                        </div>


                        {{-- Email --}}
                        <div>

                            <p class="text-sm text-gray-500">
                                Email
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->email ?? '—' }}
                            </p>

                        </div>


                        {{-- Address --}}
                        <div class="md:col-span-2">

                            <p class="text-sm text-gray-500">
                                Address
                            </p>

                            <p class="font-medium text-gray-900">
                                {{ $farm->address ?? '—' }}
                            </p>

                        </div>


                        {{-- Coordinates --}}
                        @if($farm->latitude || $farm->longitude)

                            <div>

                                <p class="text-sm text-gray-500">
                                    Latitude
                                </p>

                                <p class="font-medium text-gray-900">
                                    {{ $farm->latitude ?? '—' }}
                                </p>

                            </div>


                            <div>

                                <p class="text-sm text-gray-500">
                                    Longitude
                                </p>

                                <p class="font-medium text-gray-900">
                                    {{ $farm->longitude ?? '—' }}
                                </p>

                            </div>

                        @endif


                        {{-- Status --}}
                        <div>

                            <p class="text-sm text-gray-500">
                                Status
                            </p>

                            <div class="mt-1">

                                @if($farm->status === 'active')

                                    <span class="inline-flex items-center rounded-full
                                                     bg-green-100 px-2.5 py-1
                                                     text-xs font-semibold
                                                     text-green-700">

                                        <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-green-500"></span>

                                        Active

                                    </span>

                                @else

                                    <span class="inline-flex items-center rounded-full
                                                     bg-gray-100 px-2.5 py-1
                                                     text-xs font-semibold
                                                     text-gray-600">

                                        <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-gray-400"></span>

                                        Inactive

                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Statistics --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">


                {{-- Locations --}}
                <div class="rounded-lg bg-white p-6 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Locations
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $farm->locations->count() }}
                    </p>

                </div>


                {{-- Swine --}}
                <div class="rounded-lg bg-white p-6 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Swine
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $farm->swine->count() }}
                    </p>

                </div>


                {{-- Users --}}
                <div class="rounded-lg bg-white p-6 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Users
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $farm->users->count() }}
                    </p>

                </div>

            </div>


            {{-- Farm Locations --}}
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">


                {{-- Locations Header --}}
                <div class="flex flex-col gap-4 border-b border-gray-200
                            p-6 sm:flex-row sm:items-center
                            sm:justify-between">

                    <div>

                        <h3 class="text-lg font-semibold text-gray-900">
                            Farm Locations
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Manage pens and areas within this farm.
                        </p>

                    </div>


                    <div class="flex items-center gap-3">

                        <span class="text-sm text-gray-500">
                            {{ $farm->locations->count() }}
                            {{ Str::plural('location', $farm->locations->count()) }}
                        </span>


                        @if(
                                            $farm->status === 'active' &&
                                            auth()->user()->hasPermission('manage-locations')
                                        )

                                        <a href="{{ route('farms.locations.index', $farm) }}" class="inline-flex items-center rounded-md
                              bg-gray-900 px-4 py-2 text-xs
                              font-semibold uppercase
                              tracking-widest text-white
                              hover:bg-gray-700">

                                            Manage Locations

                                        </a>

                        @endif

                    </div>

                </div>


                {{-- Locations Content --}}
                <div class="p-6">

                    @forelse($farm->locations as $location)

                        <div class="border-b border-gray-200 py-4 last:border-b-0">

                            <div class="flex flex-col gap-3
                                            sm:flex-row sm:items-center
                                            sm:justify-between">


                                <div>

                                    <p class="font-medium text-gray-900">
                                        {{ $location->name }}
                                    </p>

                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $location->location_code }}
                                    </p>

                                </div>


                                <div>

                                    @if($location->status === 'active')

                                        <span class="inline-flex items-center rounded-full
                                                             bg-green-100 px-2.5 py-1
                                                             text-xs font-semibold
                                                             text-green-700">

                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-green-500"></span>

                                            Active

                                        </span>

                                    @else

                                        <span class="inline-flex items-center rounded-full
                                                             bg-gray-100 px-2.5 py-1
                                                             text-xs font-semibold
                                                             text-gray-600">

                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-gray-400"></span>

                                            Inactive

                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="py-8 text-center">

                            <p class="text-sm text-gray-500">
                                No locations have been registered.
                            </p>

                            @if($farm->status === 'active' && auth()->user()->hasPermission('manage-locations'))

                                <a href="{{ route('farms.locations.index', $farm) }}" class="mt-3 inline-block text-sm font-semibold
                                                  text-indigo-600 hover:text-indigo-800">
                                    Add a location →
                                </a>

                            @endif

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</x-app-layout>