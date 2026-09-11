<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">
                Role Management
            </h2>

            <p class="text-sm text-gray-500">
                View system roles and manage their assigned permissions.
            </p>
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

            {{-- Error Message --}}
            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Desktop Table --}}
            <div class="hidden overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 md:block">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        System Roles
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Each role determines the functions a user can access in SwineLocate.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Role
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Description
                                </th>

                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Users
                                </th>

                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Permissions
                                </th>

                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($roles as $role)
                                <tr class="hover:bg-gray-50">

                                    {{-- Role --}}
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                {{ $role->name }}
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $role->slug }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Description --}}
                                    <td class="px-6 py-4">
                                        <div class="max-w-md text-sm text-gray-600">
                                            {{ $role->description ?: 'No description provided.' }}
                                        </div>
                                    </td>

                                    {{-- Users --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                            {{ $role->users_count }}
                                        </span>
                                    </td>

                                    {{-- Permissions --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <span class="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                            {{ $role->permissions_count }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">

                                            <a href="{{ route('roles.show', $role) }}"
                                               class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                View
                                            </a>

                                            <a href="{{ route('roles.edit', $role) }}"
                                               class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                                                Edit Permissions
                                            </a>

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                        No roles found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile Cards --}}
            <div class="space-y-4 md:hidden">

                <div class="rounded-xl bg-white px-5 py-4 shadow-sm ring-1 ring-gray-200">
                    <h3 class="font-semibold text-gray-900">
                        System Roles
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Manage role permissions for SwineLocate users.
                    </p>
                </div>

                @forelse ($roles as $role)
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">

                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">
                                    {{ $role->name }}
                                </h3>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $role->slug }}
                                </p>
                            </div>

                            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                {{ $role->permissions_count }}
                                {{ $role->permissions_count === 1 ? 'permission' : 'permissions' }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <p class="text-sm text-gray-600">
                                {{ $role->description ?: 'No description provided.' }}
                            </p>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">

                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Users
                                </p>

                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ $role->users_count }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Permissions
                                </p>

                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ $role->permissions_count }}
                                </p>
                            </div>

                        </div>

                        <div class="mt-5 flex gap-2">

                            <a href="{{ route('roles.show', $role) }}"
                               class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                                View
                            </a>

                            <a href="{{ route('roles.edit', $role) }}"
                               class="flex-1 rounded-lg bg-indigo-600 px-3 py-2 text-center text-sm font-medium text-white hover:bg-indigo-700">
                                Edit
                            </a>

                        </div>

                    </div>
                @empty
                    <div class="rounded-xl bg-white px-5 py-10 text-center text-sm text-gray-500 shadow-sm ring-1 ring-gray-200">
                        No roles found.
                    </div>
                @endforelse

            </div>

        </div>
    </div>
</x-app-layout>