<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Farm Management
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Manage registered farms and farm information.
                </p>
            </div>

            <a href="{{ route('farms.create') }}"
               class="inline-flex items-center justify-center rounded-lg
                      border border-transparent bg-indigo-600 px-4 py-2.5
                      text-sm font-semibold text-white
                      transition hover:bg-indigo-700
                      focus:outline-none focus:ring-2
                      focus:ring-indigo-500 focus:ring-offset-2">
                + Register Farm
            </a>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

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


            {{-- Validation Errors --}}
            @if($errors->any())

                <div class="mb-6 rounded-lg border border-red-200
                            bg-red-50 px-4 py-3 text-sm text-red-700">

                    <ul class="list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>

            @endif


            {{-- Farm Table Card --}}
            <div class="overflow-hidden rounded-xl bg-white
                        shadow-sm ring-1 ring-gray-200">


                {{-- Card Header --}}
                <div class="border-b border-gray-200 px-6 py-5">

                    <div class="flex flex-col gap-4 lg:flex-row
                                lg:items-center lg:justify-between">

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


                    {{-- Status Filter --}}
                    <form method="GET"
                          action="{{ route('farms.index') }}"
                          class="mt-5 flex flex-col gap-3 sm:flex-row
                                 sm:items-end">

                        <div class="w-full sm:w-56">

                            <label for="status"
                                   class="block text-sm font-medium text-gray-700">
                                Farm Status
                            </label>

                            <select name="status"
                                    id="status"
                                    class="mt-1 block w-full rounded-lg
                                           border-gray-300 text-sm shadow-sm
                                           focus:border-indigo-500
                                           focus:ring-indigo-500">

                                <option value=""
                                    {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>
                                    All Farms
                                </option>

                                <option value="active"
                                    {{ request('status') === 'active' ? 'selected' : '' }}>
                                    Active
                                </option>

                                <option value="inactive"
                                    {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>

                            </select>

                        </div>


                        <button type="submit"
                                class="inline-flex items-center justify-center
                                       rounded-lg bg-gray-800 px-4 py-2.5
                                       text-sm font-medium text-white
                                       transition hover:bg-gray-700
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-500 focus:ring-offset-2">
                            Filter
                        </button>


                        @if(request()->filled('status'))

                            <a href="{{ route('farms.index') }}"
                               class="inline-flex items-center justify-center
                                      rounded-lg border border-gray-300
                                      bg-white px-4 py-2.5 text-sm
                                      font-medium text-gray-700
                                      transition hover:bg-gray-50
                                      focus:outline-none focus:ring-2
                                      focus:ring-indigo-500 focus:ring-offset-2">
                                Clear
                            </a>

                        @endif

                    </form>

                </div>


                {{-- Active Filter Indicator --}}
                @if(request()->filled('status'))

                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">

                        <p class="text-sm text-gray-600">

                            Showing

                            <span class="font-semibold text-gray-900">
                                {{ ucfirst(request('status')) }}
                            </span>

                            farms only.

                        </p>

                    </div>

                @endif


                {{-- Table --}}
                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs
                                           font-semibold uppercase
                                           tracking-wider text-gray-500">
                                    Farm Code
                                </th>

                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs
                                           font-semibold uppercase
                                           tracking-wider text-gray-500">
                                    Farm Name
                                </th>

                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs
                                           font-semibold uppercase
                                           tracking-wider text-gray-500">
                                    Location
                                </th>

                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs
                                           font-semibold uppercase
                                           tracking-wider text-gray-500">
                                    Contact
                                </th>

                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs
                                           font-semibold uppercase
                                           tracking-wider text-gray-500">
                                    Status
                                </th>

                                <th scope="col"
                                    class="px-6 py-4 text-right text-xs
                                           font-semibold uppercase
                                           tracking-wider text-gray-500">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100 bg-white">

                            @forelse($farms as $farm)

                                <tr class="transition hover:bg-gray-50">

                                    {{-- Farm Code --}}
                                    <td class="whitespace-nowrap px-6 py-5">

                                        <span class="font-semibold text-gray-900">
                                            {{ $farm->farm_code }}
                                        </span>

                                    </td>


                                    {{-- Farm Name --}}
                                    <td class="whitespace-nowrap px-6 py-5">

                                        <div class="font-medium text-gray-900">
                                            {{ $farm->name }}
                                        </div>

                                    </td>


                                    {{-- Location --}}
                                    <td class="px-6 py-5">

                                        <div class="text-sm text-gray-700">

                                            @if($farm->municipality || $farm->province)

                                                {{ $farm->municipality }}

                                                @if($farm->municipality && $farm->province)
                                                    ,
                                                @endif

                                                {{ $farm->province }}

                                            @else

                                                <span class="text-gray-400">
                                                    —
                                                </span>

                                            @endif

                                        </div>

                                    </td>


                                    {{-- Contact --}}
                                    <td class="whitespace-nowrap px-6 py-5">

                                        <span class="text-sm text-gray-700">
                                            {{ $farm->contact_number ?? '—' }}
                                        </span>

                                    </td>


                                    {{-- Status --}}
                                    <td class="whitespace-nowrap px-6 py-5">

                                        @if($farm->status === 'active')

                                            <span class="inline-flex items-center
                                                         rounded-full
                                                         bg-green-100
                                                         px-2.5 py-1
                                                         text-xs font-semibold
                                                         text-green-700">

                                                <span class="mr-1.5 h-1.5 w-1.5
                                                             rounded-full
                                                             bg-green-500"></span>

                                                Active

                                            </span>

                                        @else

                                            <span class="inline-flex items-center
                                                         rounded-full
                                                         bg-gray-100
                                                         px-2.5 py-1
                                                         text-xs font-semibold
                                                         text-gray-600">

                                                <span class="mr-1.5 h-1.5 w-1.5
                                                             rounded-full
                                                             bg-gray-400"></span>

                                                Inactive

                                            </span>

                                        @endif

                                    </td>


                                    {{-- Actions --}}
                                    <td class="whitespace-nowrap px-6 py-5">

                                        <div class="flex items-center
                                                    justify-end gap-3">

                                            {{-- View --}}
                                            <a href="{{ route('farms.show', $farm) }}"
                                               class="text-sm font-medium
                                                      text-blue-600
                                                      hover:text-blue-800">
                                                View
                                            </a>


                                            {{-- Edit --}}
                                            <a href="{{ route('farms.edit', $farm) }}"
                                               class="text-sm font-medium
                                                      text-indigo-600
                                                      hover:text-indigo-800">
                                                Edit
                                            </a>


                                            {{-- Activate / Deactivate --}}
                                            @if($farm->status === 'active')

                                                <form method="POST"
                                                      action="{{ route('farms.destroy', $farm) }}"
                                                      class="inline"
                                                      onsubmit="return confirm('Are you sure you want to deactivate this farm?');">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="text-sm font-medium
                                                                   text-red-600
                                                                   hover:text-red-800">
                                                        Deactivate
                                                    </button>

                                                </form>

                                            @else

                                                <form method="POST"
                                                      action="{{ route('farms.activate', $farm) }}"
                                                      class="inline"
                                                      onsubmit="return confirm('Are you sure you want to activate this farm?');">

                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                            class="text-sm font-medium
                                                                   text-green-600
                                                                   hover:text-green-800">
                                                        Activate
                                                    </button>

                                                </form>

                                            @endif

                                        </div>

                                    </td>

                                </tr>


                            @empty

                                <tr>

                                    <td colspan="6"
                                        class="px-6 py-12 text-center">

                                        <div class="flex flex-col items-center">

                                            <div class="flex h-12 w-12
                                                        items-center justify-center
                                                        rounded-full bg-gray-100
                                                        text-gray-400">

                                                <svg class="h-6 w-6"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">

                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7M9 11h6" />

                                                </svg>

                                            </div>


                                            @if(request()->filled('status'))

                                                <h3 class="mt-3 text-sm
                                                           font-semibold
                                                           text-gray-900">
                                                    No {{ request('status') }}
                                                    farms found
                                                </h3>

                                                <p class="mt-1 text-sm
                                                          text-gray-500">
                                                    There are currently no
                                                    {{ request('status') }}
                                                    farms registered.
                                                </p>


                                                <a href="{{ route('farms.index') }}"
                                                   class="mt-4 text-sm
                                                          font-semibold
                                                          text-indigo-600
                                                          hover:text-indigo-800">
                                                    View all farms →
                                                </a>

                                            @else

                                                <h3 class="mt-3 text-sm
                                                           font-semibold
                                                           text-gray-900">
                                                    No farms registered
                                                </h3>

                                                <p class="mt-1 text-sm
                                                          text-gray-500">
                                                    Get started by registering
                                                    your first farm.
                                                </p>

                                                <a href="{{ route('farms.create') }}"
                                                   class="mt-4 text-sm
                                                          font-semibold
                                                          text-indigo-600
                                                          hover:text-indigo-800">
                                                    Register a farm →
                                                </a>

                                            @endif

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