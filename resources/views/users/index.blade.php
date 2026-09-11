<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    User Management
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Manage system users, roles, farm assignments, and account status.
                </p>
            </div>

            <a
                href="{{ route('users.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-[#3368A0] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#28547f] focus:outline-none focus:ring-2 focus:ring-[#3368A0] focus:ring-offset-2"
            >
                + Add User
            </a>

        </div>

    </x-slot>


    <div class="py-6">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))

                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>

            @endif


            {{-- Error Message --}}
            @if(session('error'))

                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>

            @endif


            {{-- Validation Errors --}}
            @if($errors->any())

                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">

                    <p class="text-sm font-semibold text-red-700">
                        Please correct the following errors:
                    </p>

                    <ul class="mt-2 list-disc pl-5 text-sm text-red-600">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- Filters --}}
            <div class="mb-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                <form
                    method="GET"
                    action="{{ route('users.index') }}"
                    class="grid grid-cols-1 gap-4 md:grid-cols-4"
                >

                    {{-- Search --}}
                    <div class="md:col-span-2">

                        <label
                            for="search"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Search
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ request('search') }}"
                            placeholder="Search name, email, or phone..."
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#3368A0] focus:ring-[#3368A0]"
                        >

                    </div>


                    {{-- Role --}}
                    <div>

                        <label
                            for="role"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Role
                        </label>

                        <select
                            id="role"
                            name="role"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#3368A0] focus:ring-[#3368A0]"
                        >

                            <option value="">
                                All Roles
                            </option>

                            @foreach($roles as $role)

                                <option
                                    value="{{ $role->id }}"
                                    @selected(request('role') == $role->id)
                                >
                                    {{ $role->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Status --}}
                    <div>

                        <label
                            for="status"
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#3368A0] focus:ring-[#3368A0]"
                        >

                            <option value="">
                                All Status
                            </option>

                            <option
                                value="active"
                                @selected(request('status') === 'active')
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                @selected(request('status') === 'inactive')
                            >
                                Inactive
                            </option>

                        </select>

                    </div>


                    {{-- Buttons --}}
                    <div class="flex items-end gap-2 md:col-span-4">

                        <button
                            type="submit"
                            class="rounded-lg bg-[#3368A0] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#28547f]"
                        >
                            Filter
                        </button>

                        <a
                            href="{{ route('users.index') }}"
                            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Reset
                        </a>

                    </div>

                </form>

            </div>


            {{-- User Table --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-5 py-4">

                    <div class="flex items-center justify-between">

                        <div>

                            <h3 class="text-lg font-semibold text-gray-800">
                                System Users
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $users->total() }}
                                {{ Str::plural('user', $users->total()) }}
                                found
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Desktop Table --}}
                <div class="hidden overflow-x-auto md:block">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    User
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Role
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Farm
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Status
                                </th>

                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100 bg-white">

                            @forelse($users as $user)

                                <tr class="transition hover:bg-gray-50">

                                    {{-- User --}}
                                    <td class="px-5 py-4">

                                        <div class="font-semibold text-gray-800">
                                            {{ $user->name }}
                                        </div>

                                        <div class="text-sm text-gray-500">
                                            {{ $user->email }}
                                        </div>

                                        @if($user->phone)

                                            <div class="mt-0.5 text-xs text-gray-400">
                                                {{ $user->phone }}
                                            </div>

                                        @endif

                                    </td>


                                    {{-- Role --}}
                                    <td class="px-5 py-4">

                                        @if($user->role)

                                            <span class="text-sm font-medium text-gray-700">
                                                {{ $user->role->name }}
                                            </span>

                                        @else

                                            <span class="text-sm text-gray-400">
                                                No role assigned
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Farm --}}
                                    <td class="px-5 py-4">

                                        @if($user->farm)

                                            <span class="text-sm text-gray-700">
                                                {{ $user->farm->name }}
                                            </span>

                                        @else

                                            <span class="text-sm text-gray-400">
                                                No farm assigned
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Status --}}
                                    <td class="px-5 py-4">

                                        @if($user->status === 'active')

                                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                                Active
                                            </span>

                                        @else

                                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                                Inactive
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Actions --}}
                                    <td class="px-5 py-4">

                                        <div class="flex justify-end gap-2">

                                            <a
                                                href="{{ route('users.show', $user) }}"
                                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50"
                                            >
                                                View
                                            </a>

                                            <a
                                                href="{{ route('users.edit', $user) }}"
                                                class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50"
                                            >
                                                Edit
                                            </a>

                                            @if(auth()->id() !== $user->id)

                                                <form
                                                    method="POST"
                                                    action="{{ route('users.toggle-status', $user) }}"
                                                    class="inline"
                                                >

                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="rounded-lg border border-yellow-200 px-3 py-1.5 text-xs font-semibold text-yellow-700 transition hover:bg-yellow-50"
                                                    >
                                                        {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                    </button>

                                                </form>

                                                <form
                                                    method="POST"
                                                    action="{{ route('users.destroy', $user) }}"
                                                    class="inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this user?');"
                                                >

                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50"
                                                    >
                                                        Delete
                                                    </button>

                                                </form>

                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="5"
                                        class="px-5 py-12 text-center"
                                    >

                                        <div class="text-sm font-semibold text-gray-700">
                                            No users found.
                                        </div>

                                        <p class="mt-1 text-sm text-gray-500">
                                            Try adjusting your search or filters.
                                        </p>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- Mobile Cards --}}
                <div class="divide-y divide-gray-100 md:hidden">

                    @forelse($users as $user)

                        <div class="p-5">

                            <div class="flex items-start justify-between gap-4">

                                <div class="min-w-0">

                                    <div class="font-semibold text-gray-800">
                                        {{ $user->name }}
                                    </div>

                                    <div class="mt-1 break-all text-sm text-gray-500">
                                        {{ $user->email }}
                                    </div>

                                    @if($user->phone)

                                        <div class="mt-1 text-xs text-gray-400">
                                            {{ $user->phone }}
                                        </div>

                                    @endif

                                </div>


                                @if($user->status === 'active')

                                    <span class="shrink-0 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                        Active
                                    </span>

                                @else

                                    <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                        Inactive
                                    </span>

                                @endif

                            </div>


                            <div class="mt-4 grid grid-cols-2 gap-4">

                                <div>

                                    <div class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                        Role
                                    </div>

                                    <div class="mt-1 text-sm text-gray-700">
                                        {{ $user->role?->name ?? 'No role assigned' }}
                                    </div>

                                </div>


                                <div>

                                    <div class="text-xs font-medium uppercase tracking-wide text-gray-400">
                                        Farm
                                    </div>

                                    <div class="mt-1 text-sm text-gray-700">
                                        {{ $user->farm?->name ?? 'No farm assigned' }}
                                    </div>

                                </div>

                            </div>


                            <div class="mt-4 flex flex-wrap gap-2">

                                <a
                                    href="{{ route('users.show', $user) }}"
                                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700"
                                >
                                    View
                                </a>

                                <a
                                    href="{{ route('users.edit', $user) }}"
                                    class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700"
                                >
                                    Edit
                                </a>

                                @if(auth()->id() !== $user->id)

                                    <form
                                        method="POST"
                                        action="{{ route('users.toggle-status', $user) }}"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="rounded-lg border border-yellow-200 px-3 py-1.5 text-xs font-semibold text-yellow-700"
                                        >
                                            {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>

                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('users.destroy', $user) }}"
                                        onsubmit="return confirm('Are you sure you want to delete this user?');"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                @endif

                            </div>

                        </div>

                    @empty

                        <div class="px-5 py-12 text-center">

                            <div class="text-sm font-semibold text-gray-700">
                                No users found.
                            </div>

                            <p class="mt-1 text-sm text-gray-500">
                                Try adjusting your search or filters.
                            </p>

                        </div>

                    @endforelse

                </div>


                {{-- Pagination --}}
                @if($users->hasPages())

                    <div class="border-t border-gray-200 px-5 py-4">
                        {{ $users->links() }}
                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>