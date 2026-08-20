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

            <a
                href="{{ route('farms.locations.create', $farm) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700"
            >
                Add Location
            </a>

        </div>

    </x-slot>

    <div class="py-8">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))

                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-green-700">
                    {{ session('success') }}
                </div>

            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <div class="flex items-center justify-between mb-6">

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Registered Locations
                            </h3>

                            <p class="text-sm text-gray-500">
                                Areas, pens, and housing locations within this farm.
                            </p>
                        </div>

                        <a
                            href="{{ route('farms.show', $farm) }}"
                            class="text-sm text-gray-600 hover:text-gray-900"
                        >
                            ← Back to Farm
                        </a>

                    </div>

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

                                        <td class="px-6 py-4">
                                            <span class="font-medium text-gray-900">
                                                {{ $location->location_code }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4">
                                            {{ $location->name }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $location->type ?? '—' }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $location->capacity ?? '—' }}
                                        </td>

                                        <td class="px-6 py-4">

                                            @if($location->status === 'active')

                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                                    Active
                                                </span>

                                            @else

                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                                    Inactive
                                                </span>

                                            @endif

                                        </td>

                                        <td class="px-6 py-4 text-right text-sm">

                                            <a
                                                href="{{ route('farms.locations.show', [$farm, $location]) }}"
                                                class="text-gray-700 hover:text-gray-900 mr-3"
                                            >
                                                View
                                            </a>

                                            <a
                                                href="{{ route('farms.locations.edit', [$farm, $location]) }}"
                                                class="text-blue-600 hover:text-blue-800 mr-3"
                                            >
                                                Edit
                                            </a>

                                            @if($location->status === 'active')

                                                <form
                                                    action="{{ route('farms.locations.destroy', [$farm, $location]) }}"
                                                    method="POST"
                                                    class="inline"
                                                    onsubmit="return confirm('Deactivate this location?')"
                                                >

                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="text-red-600 hover:text-red-800"
                                                    >
                                                        Deactivate
                                                    </button>

                                                </form>

                                            @endif

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td
                                            colspan="6"
                                            class="px-6 py-12 text-center text-gray-500"
                                        >
                                            No locations have been registered for this farm.
                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

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