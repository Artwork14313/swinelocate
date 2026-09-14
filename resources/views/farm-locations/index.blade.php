<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>
                <h2 class="font-semibold text-xl text-gray-800">
                    Farm Locations
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $farm->name }}
                    ·
                    {{ $farm->farm_code }}
                </p>
            </div>

            @if(
                    $farm->status === 'active' &&
                    auth()->user()->hasPermission('manage-locations')
                )

                <a href="{{ route('farms.locations.create', $farm) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                    Add Location
                </a>

            @endif

        </div>

    </x-slot>

    <div class="py-8">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Success Message --}}
            @if(session('success'))

                <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-700">
                    {{ session('success') }}
                </div>

            @endif


            {{-- Error Message --}}
            @if(session('error'))

                <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-700">
                    {{ session('error') }}
                </div>

            @endif


            {{-- Inactive Farm Notice --}}
            @if($farm->status !== 'active')

                <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">

                    <div class="flex items-start">

                        <div>
                            <p class="font-semibold text-yellow-800">
                                This farm is inactive.
                            </p>

                            <p class="text-sm text-yellow-700 mt-1">
                                Existing locations are shown for historical and
                                traceability purposes. New locations cannot be
                                added or modified while the farm is inactive.
                            </p>
                        </div>

                    </div>

                </div>

            @endif


            {{-- Locations Card --}}
            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    {{-- Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">

                        <div>

                            <div class="flex items-center gap-3">

                                <h3 class="text-lg font-semibold text-gray-900">
                                    Registered Locations
                                </h3>

                                @if($farm->status === 'active')

                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                        Farm Active
                                    </span>

                                @else

                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                        Farm Inactive
                                    </span>

                                @endif

                            </div>

                            <p class="text-sm text-gray-500 mt-1">
                                Areas, pens, and housing locations within this farm.
                            </p>

                        </div>

                        <a href="{{ route('farms.show', $farm) }}" class="text-sm text-gray-600 hover:text-gray-900">
                            ← Back to Farm
                        </a>

                    </div>


                    {{-- Location Table --}}
                    <div class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-gray-200">

                            <thead>

                                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">

                                    <th class="px-6 py-3">
                                        Code
                                    </th>

                                    <th class="px-6 py-3">
                                        Location
                                    </th>

                                    <th class="px-6 py-3">
                                        Type
                                    </th>

                                    <th class="px-6 py-3">
                                        Capacity
                                    </th>

                                    <th class="px-6 py-3">
                                        Status
                                    </th>

                                    <th class="px-6 py-3 text-right">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-200">

                                @forelse($locations as $location)

                                    <tr class="hover:bg-gray-50">

                                        {{-- Code --}}
                                        <td class="px-6 py-4">

                                            <span class="font-medium text-gray-900">
                                                {{ $location->location_code }}
                                            </span>

                                        </td>


                                        {{-- Location --}}
                                        <td class="px-6 py-4">

                                            <span class="text-gray-900">
                                                {{ $location->name }}
                                            </span>

                                        </td>


                                        {{-- Type --}}
                                        <td class="px-6 py-4 text-sm text-gray-600">

                                            {{ $location->type ?? '—' }}

                                        </td>


                                        {{-- Capacity --}}
                                        <td class="px-6 py-4 text-sm text-gray-600">

                                            {{ $location->capacity ?? '—' }}

                                        </td>


                                        {{-- Status --}}
                                        <td class="px-6 py-4">

                                            @if($location->status === 'active')

                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                                    Active
                                                </span>

                                            @else

                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                                    Inactive
                                                </span>

                                            @endif

                                        </td>


                                        {{-- Actions --}}
                                        <td class="px-6 py-4 text-right text-sm">

                                            {{-- View --}}
                                            <a href="{{ route('farms.locations.show', [$farm, $location]) }}"
                                                class="text-gray-700 hover:text-gray-900 mr-3">
                                                View
                                            </a>


                                            {{-- Management Actions --}}
                                            @if(
                                                    $farm->status === 'active' &&
                                                    auth()->user()->hasPermission('manage-locations')
                                                )

                                                <a href="{{ route('farms.locations.edit', [$farm, $location]) }}"
                                                    class="text-blue-600 hover:text-blue-800 mr-3">
                                                    Edit
                                                </a>


                                                @if(
                                                        $farm->status === 'active' &&
                                                        auth()->user()->hasPermission('manage-locations')
                                                    )

                                                    @if($location->status === 'active')

                                                        <form action="{{ route('farms.locations.destroy', [$farm, $location]) }}"
                                                            method="POST" class="inline"
                                                            onsubmit="return confirm('Deactivate this location?')">

                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                                Deactivate
                                                            </button>

                                                        </form>

                                                    @else

                                                        <form action="{{ route('farms.locations.activate', [$farm, $location]) }}"
                                                            method="POST" class="inline"
                                                            onsubmit="return confirm('Activate this location?')">

                                                            @csrf
                                                            @method('PATCH')

                                                            <button type="submit" class="text-green-600 hover:text-green-800">
                                                                Activate
                                                            </button>

                                                        </form>

                                                    @endif

                                                @endif

                                            @endif

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            No locations have been registered for this farm.
                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>


                    {{-- Pagination --}}
                    @if($locations->hasPages())

                        <div class="mt-6">
                            {{ $locations->links() }}
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</x-app-layout>