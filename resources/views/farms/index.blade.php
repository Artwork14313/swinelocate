<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Farm Management
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Manage registered farms and farm information.
                </p>
            </div>

            <a
                href="{{ route('farms.create') }}"
                class="inline-flex items-center justify-center px-4 py-2.5
                       bg-indigo-600 border border-transparent
                       rounded-lg font-semibold text-sm text-white
                       hover:bg-indigo-700
                       focus:outline-none focus:ring-2
                       focus:ring-indigo-500 focus:ring-offset-2
                       transition"
            >
                + Register Farm
            </a>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))

                <div class="mb-6 rounded-lg border border-green-200
                            bg-green-50 px-4 py-3 text-sm text-green-700">

                    {{ session('success') }}

                </div>

            @endif


            {{-- Error Message --}}
            @if(session('error'))

                <div class="mb-6 rounded-lg border border-red-200
                            bg-red-50 px-4 py-3 text-sm text-red-700">

                    {{ session('error') }}

                </div>

            @endif


            {{-- Farm Table Card --}}
            <div class="overflow-hidden bg-white shadow-sm
                        ring-1 ring-gray-200 rounded-xl">

                <div class="px-6 py-5 border-b border-gray-200">

                    <div class="flex items-center justify-between">

                        <div>

                            <h3 class="text-lg font-semibold text-gray-900">
                                Registered Farms
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                View and manage all registered swine farms.
                            </p>

                        </div>

                        <div class="text-sm text-gray-500">
                            {{ $farms->total() }}
                            {{ Str::plural('farm', $farms->total()) }}
                        </div>

                    </div>

                </div>


                {{-- Table --}}
                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th
                                    scope="col"
                                    class="px-6 py-4 text-left text-xs
                                           font-semibold text-gray-500
                                           uppercase tracking-wider"
                                >
                                    Farm Code
                                </th>

                                <th
                                    scope="col"
                                    class="px-6 py-4 text-left text-xs
                                           font-semibold text-gray-500
                                           uppercase tracking-wider"
                                >
                                    Farm Name
                                </th>

                                <th
                                    scope="col"
                                    class="px-6 py-4 text-left text-xs
                                           font-semibold text-gray-500
                                           uppercase tracking-wider"
                                >
                                    Location
                                </th>

                                <th
                                    scope="col"
                                    class="px-6 py-4 text-left text-xs
                                           font-semibold text-gray-500
                                           uppercase tracking-wider"
                                >
                                    Contact
                                </th>

                                <th
                                    scope="col"
                                    class="px-6 py-4 text-left text-xs
                                           font-semibold text-gray-500
                                           uppercase tracking-wider"
                                >
                                    Status
                                </th>

                                <th
                                    scope="col"
                                    class="px-6 py-4 text-right text-xs
                                           font-semibold text-gray-500
                                           uppercase tracking-wider"
                                >
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100 bg-white">

                            @forelse($farms as $farm)

                                <tr class="hover:bg-gray-50 transition">

                                    {{-- Farm Code --}}
                                    <td class="px-6 py-5 whitespace-nowrap">

                                        <span class="font-semibold text-gray-900">
                                            {{ $farm->farm_code }}
                                        </span>

                                    </td>


                                    {{-- Farm Name --}}
                                    <td class="px-6 py-5 whitespace-nowrap">

                                        <div class="font-medium text-gray-900">
                                            {{ $farm->name }}
                                        </div>

                                    </td>


                                    {{-- Location --}}
                                    <td class="px-6 py-5">

                                        <div class="text-sm text-gray-700">
                                            {{ $farm->city }},
                                            {{ $farm->province }}
                                        </div>

                                    </td>


                                    {{-- Contact --}}
                                    <td class="px-6 py-5 whitespace-nowrap">

                                        <span class="text-sm text-gray-700">
                                            {{ $farm->contact_number ?? '—' }}
                                        </span>

                                    </td>


                                    {{-- Status --}}
                                    <td class="px-6 py-5 whitespace-nowrap">

                                        @if($farm->status === 'active')

                                            <span
                                                class="inline-flex items-center
                                                       rounded-full
                                                       bg-green-100
                                                       px-2.5 py-1
                                                       text-xs font-semibold
                                                       text-green-700"
                                            >
                                                <span
                                                    class="mr-1.5 h-1.5 w-1.5
                                                           rounded-full
                                                           bg-green-500"
                                                ></span>

                                                Active
                                            </span>

                                        @else

                                            <span
                                                class="inline-flex items-center
                                                       rounded-full
                                                       bg-gray-100
                                                       px-2.5 py-1
                                                       text-xs font-semibold
                                                       text-gray-600"
                                            >
                                                <span
                                                    class="mr-1.5 h-1.5 w-1.5
                                                           rounded-full
                                                           bg-gray-400"
                                                ></span>

                                                Inactive
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Actions --}}
                                    <td class="px-6 py-5 whitespace-nowrap">

                                        <div class="flex items-center justify-end gap-2">

                                            <a
                                                href="{{ route('farms.show', $farm) }}"
                                                class="inline-flex items-center
                                                       rounded-md px-3 py-1.5
                                                       text-sm font-medium
                                                       text-gray-700
                                                       hover:bg-gray-100
                                                       hover:text-gray-900
                                                       transition"
                                            >
                                                View
                                            </a>


                                            <a
                                                href="{{ route('farms.edit', $farm) }}"
                                                class="inline-flex items-center
                                                       rounded-md px-3 py-1.5
                                                       text-sm font-medium
                                                       text-indigo-600
                                                       hover:bg-indigo-50
                                                       hover:text-indigo-800
                                                       transition"
                                            >
                                                Edit
                                            </a>


                                            @if($farm->status === 'active')

                                                <form
                                                    action="{{ route('farms.destroy', $farm) }}"
                                                    method="POST"
                                                    class="inline"
                                                    onsubmit="return confirm('Are you sure you want to deactivate this farm?')"
                                                >

                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center
                                                               rounded-md px-3 py-1.5
                                                               text-sm font-medium
                                                               text-red-600
                                                               hover:bg-red-50
                                                               hover:text-red-800
                                                               transition"
                                                    >
                                                        Deactivate
                                                    </button>

                                                </form>

                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="6"
                                        class="px-6 py-12 text-center"
                                    >

                                        <div class="flex flex-col items-center">

                                            <div
                                                class="flex h-12 w-12 items-center
                                                       justify-center rounded-full
                                                       bg-gray-100 text-gray-400"
                                            >
                                                <svg
                                                    class="h-6 w-6"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7M9 11h6"
                                                    />
                                                </svg>
                                            </div>

                                            <h3 class="mt-3 text-sm font-semibold text-gray-900">
                                                No farms registered
                                            </h3>

                                            <p class="mt-1 text-sm text-gray-500">
                                                Get started by registering your first farm.
                                            </p>

                                            <a
                                                href="{{ route('farms.create') }}"
                                                class="mt-4 text-sm font-semibold
                                                       text-indigo-600
                                                       hover:text-indigo-800"
                                            >
                                                Register a farm →
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- Pagination --}}
                @if($farms->hasPages())

                    <div class="border-t border-gray-200 px-6 py-4">

                        {{ $farms->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>