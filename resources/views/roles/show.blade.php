<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    Role Details
                </h2>

                <p class="text-sm text-gray-500">
                    View the permissions and users assigned to this role.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('roles.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Back to Roles
                </a>

                <a href="{{ route('roles.edit', $role) }}"
                   class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Edit Permissions
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Role Information --}}
            <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-white">
                                {{ $role->name }}
                            </h3>

                            <p class="mt-1 text-sm text-indigo-100">
                                {{ $role->slug }}
                            </p>
                        </div>

                        <div class="flex gap-3">
                            <div class="rounded-lg bg-white/10 px-4 py-3 text-center">
                                <div class="text-2xl font-bold text-white">
                                    {{ $role->users->count() }}
                                </div>

                                <div class="text-xs text-indigo-100">
                                    Users
                                </div>
                            </div>

                            <div class="rounded-lg bg-white/10 px-4 py-3 text-center">
                                <div class="text-2xl font-bold text-white">
                                    {{ $role->permissions->count() }}
                                </div>

                                <div class="text-xs text-indigo-100">
                                    Permissions
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-gray-600">
                        {{ $role->description ?: 'No description provided for this role.' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- Permissions --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">
                            Assigned Permissions
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Permissions currently available to this role.
                        </p>
                    </div>

                    <div class="p-6">

                        @if ($role->permissions->isNotEmpty())

                            <div class="space-y-3">
                                @foreach ($role->permissions as $permission)
                                    <div class="flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">

                                        <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-green-100 text-green-600">
                                            ✓
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $permission->name }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-gray-500">
                                                {{ $permission->slug }}
                                            </p>

                                            @if ($permission->description)
                                                <p class="mt-1 text-xs text-gray-600">
                                                    {{ $permission->description }}
                                                </p>
                                            @endif
                                        </div>

                                    </div>
                                @endforeach
                            </div>

                        @else

                            <div class="rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center">
                                <p class="text-sm text-gray-500">
                                    No permissions are assigned to this role.
                                </p>
                            </div>

                        @endif

                    </div>
                </div>

                {{-- Users --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">
                            Users with this Role
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Accounts currently assigned to this role.
                        </p>
                    </div>

                    <div class="p-6">

                        @if ($role->users->isNotEmpty())

                            <div class="space-y-3">
                                @foreach ($role->users as $user)
                                    <div class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 px-4 py-3">

                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-600">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>

                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-gray-900">
                                                    {{ $user->name }}
                                                </p>

                                                <p class="truncate text-xs text-gray-500">
                                                    {{ $user->email }}
                                                </p>
                                            </div>
                                        </div>

                                        @if ($user->status === 'active')
                                            <span class="flex-shrink-0 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                                Active
                                            </span>
                                        @else
                                            <span class="flex-shrink-0 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                                Inactive
                                            </span>
                                        @endif

                                    </div>
                                @endforeach
                            </div>

                        @else

                            <div class="rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center">
                                <p class="text-sm text-gray-500">
                                    No users are currently assigned to this role.
                                </p>
                            </div>

                        @endif

                    </div>
                </div>

            </div>

            {{-- Bottom Actions --}}
            <div class="mt-6 flex flex-wrap gap-2">
                <a href="{{ route('roles.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Back to Roles
                </a>

                <a href="{{ route('roles.edit', $role) }}"
                   class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Edit Permissions
                </a>
            </div>

        </div>
    </div>
</x-app-layout>