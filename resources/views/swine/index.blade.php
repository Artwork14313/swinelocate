<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Swine Management
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Manage registered swine and their identification records.
                </p>
            </div>

            <a href="{{ route('swine.create') }}" class="inline-flex items-center justify-center rounded-lg
                       bg-indigo-600 px-4 py-2.5 text-sm font-semibold
                       text-white shadow-sm hover:bg-indigo-700
                       focus:outline-none focus:ring-2
                       focus:ring-indigo-500 focus:ring-offset-2">
                + Register Swine
            </a>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-green-200
                                bg-green-50 px-4 py-3 text-sm text-green-700">

                    {{ session('success') }}

                </div>
            @endif


            {{-- Main Table --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                           font-semibold uppercase tracking-wider
                                           text-gray-500">
                                    Tag Number
                                </th>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                           font-semibold uppercase tracking-wider
                                           text-gray-500">
                                    Farm
                                </th>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                           font-semibold uppercase tracking-wider
                                           text-gray-500">
                                    Current Location
                                </th>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                           font-semibold uppercase tracking-wider
                                           text-gray-500">
                                    Sex
                                </th>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                           font-semibold uppercase tracking-wider
                                           text-gray-500">
                                    Breed
                                </th>

                                <th scope="col" class="px-6 py-4 text-left text-xs
                                           font-semibold uppercase tracking-wider
                                           text-gray-500">
                                    Status
                                </th>

                                <th scope="col" class="px-6 py-4 text-right text-xs
                                           font-semibold uppercase tracking-wider
                                           text-gray-500">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100 bg-white">

                            @forelse ($swine as $pig)

                                <tr class="transition hover:bg-gray-50">

                                    {{-- Tag Number --}}
                                    <td class="whitespace-nowrap px-6 py-4">

                                        <a href="{{ route('swine.show', $pig) }}"
                                            class="font-semibold text-indigo-600 hover:text-indigo-800">
                                            {{ $pig->tag_number }}
                                        </a>

                                    </td>

                                    {{-- Farm --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">

                                        @if ($pig->farm)
                                            <div class="font-medium text-gray-900">
                                                {{ $pig->farm->name }}
                                            </div>

                                            @if ($pig->farm->farm_code)
                                                <div class="text-xs text-gray-500">
                                                    {{ $pig->farm->farm_code }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">
                                                —
                                            </span>
                                        @endif

                                    </td>


                                    {{-- Current Location --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">

                                        @if ($pig->currentLocation)

                                            <div class="font-medium text-gray-900">
                                                {{ $pig->currentLocation->name }}
                                            </div>

                                            @if ($pig->currentLocation->location_code)
                                                <div class="text-xs text-gray-500">
                                                    {{ $pig->currentLocation->location_code }}
                                                </div>
                                            @endif

                                        @else

                                            <span class="text-gray-400">
                                                Unassigned
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Sex --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">

                                        {{ ucfirst($pig->sex) }}

                                    </td>


                                    {{-- Breed --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">

                                        {{ $pig->breed ?: '—' }}

                                    </td>


                                    {{-- Status --}}
                                    <td class="whitespace-nowrap px-6 py-4">

                                        @if ($pig->status === 'active')

                                            <span class="inline-flex rounded-full
                                                               bg-green-100 px-2.5 py-1
                                                               text-xs font-semibold
                                                               text-green-700">
                                                Active
                                            </span>

                                        @elseif ($pig->status === 'inactive')

                                            <span class="inline-flex rounded-full
                                                               bg-gray-100 px-2.5 py-1
                                                               text-xs font-semibold
                                                               text-gray-700">
                                                Inactive
                                            </span>

                                        @elseif ($pig->status === 'sold')

                                            <span class="inline-flex rounded-full
                                                               bg-blue-100 px-2.5 py-1
                                                               text-xs font-semibold
                                                               text-blue-700">
                                                Sold
                                            </span>

                                        @elseif ($pig->status === 'deceased')

                                            <span class="inline-flex rounded-full
                                                               bg-red-100 px-2.5 py-1
                                                               text-xs font-semibold
                                                               text-red-700">
                                                Deceased
                                            </span>

                                        @else

                                            <span class="inline-flex rounded-full
                                                               bg-gray-100 px-2.5 py-1
                                                               text-xs font-semibold
                                                               text-gray-700">
                                                {{ ucfirst($pig->status) }}
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Actions --}}
                                    <td class="whitespace-nowrap px-6 py-4">

                                        <div class="flex justify-end gap-3">

                                            <a href="{{ route('swine.show', $pig) }}" class="inline-flex items-center
                                                       text-sm font-medium
                                                       text-gray-700
                                                       hover:bg-gray-100
                                                       hover:text-gray-900
                                                       transition">
                                                View
                                            </a>

                                            <a href="{{ route('swine.edit', $pig) }}" class="inline-flex items-center text-sm font-medium text-indigo-600
                                                           hover:text-indigo-800">
                                                Edit
                                            </a>

                                            <form action="{{ route('swine.destroy', $pig) }}" method="POST" class="inline"
                                                onsubmit="return confirm('Are you sure you want to delete this swine?')">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="inline-flex items-center
                                                                   py-1.5
                                                                   text-sm font-medium
                                                                   text-red-600
                                                                   hover:bg-red-50
                                                                   hover:text-red-800
                                                                   transition">
                                                    Delete
                                                </button>

                                            </form>
                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="8" class="px-6 py-16 text-center">

                                        <div class="text-sm font-medium text-gray-900">
                                            No swine registered
                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- Pagination --}}
                @if ($swine->hasPages())

                    <div class="border-t border-gray-200 px-6 py-4">

                        {{ $swine->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>