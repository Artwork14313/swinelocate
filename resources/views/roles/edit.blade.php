<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    Edit Role Permissions
                </h2>

                <p class="text-sm text-gray-500">
                    Configure the permissions assigned to this role.
                </p>
            </div>

            <a href="{{ route('roles.show', $role) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to Role
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                    <div class="font-medium text-red-800">
                        Please correct the following errors:
                    </div>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Role Information --}}
            <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-6">
                    <h3 class="text-xl font-bold text-white">
                        {{ $role->name }}
                    </h3>

                    <p class="mt-1 text-sm text-indigo-100">
                        {{ $role->description ?: 'Configure permissions for this role.' }}
                    </p>
                </div>

                <div class="grid grid-cols-2 divide-x divide-gray-200">
                    <div class="px-6 py-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Assigned Users
                        </p>

                        <p class="mt-1 text-lg font-semibold text-gray-900">
                            {{ $role->users()->count() }}
                        </p>
                    </div>

                    <div class="px-6 py-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Current Permissions
                        </p>

                        <p class="mt-1 text-lg font-semibold text-gray-900">
                            {{ $role->permissions->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('roles.update', $role) }}"
                  class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Permissions --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">
                                    Permissions
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Select the functions that users with this role are allowed to access.
                                </p>
                            </div>

                            <div class="flex gap-2">
                                <button type="button"
                                        id="select-all"
                                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                    Select All
                                </button>

                                <button type="button"
                                        id="clear-all"
                                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                    Clear All
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-200">

                        @forelse ($permissions as $permission)

                            <label class="flex cursor-pointer items-start gap-4 px-6 py-4 hover:bg-gray-50">

                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->id }}"
                                    class="permission-checkbox mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}
                                >

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $permission->name }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-gray-500">
                                                {{ $permission->slug }}
                                            </p>
                                        </div>
                                    </div>

                                    @if ($permission->description)
                                        <p class="mt-2 text-sm text-gray-600">
                                            {{ $permission->description }}
                                        </p>
                                    @endif
                                </div>

                            </label>

                        @empty

                            <div class="px-6 py-10 text-center">
                                <p class="text-sm text-gray-500">
                                    No permissions are available.
                                </p>
                            </div>

                        @endforelse

                    </div>

                    {{-- Selected Counter --}}
                    <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                        <p class="text-sm text-gray-600">
                            Selected permissions:
                            <span id="permission-count"
                                  class="font-semibold text-gray-900">
                                0
                            </span>
                        </p>
                    </div>

                </div>

                {{-- Warning --}}
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-4">
                    <div class="flex gap-3">
                        <div class="mt-0.5 text-yellow-600">
                            ⚠
                        </div>

                        <div>
                            <p class="text-sm font-medium text-yellow-800">
                                Permission changes affect all users with this role.
                            </p>

                            <p class="mt-1 text-sm text-yellow-700">
                                Make sure the selected permissions match the responsibilities of this role before saving.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a href="{{ route('roles.show', $role) }}"
                       class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
                        Save Permissions
                    </button>

                </div>

            </form>

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const checkboxes = document.querySelectorAll('.permission-checkbox');
                const selectAllButton = document.getElementById('select-all');
                const clearAllButton = document.getElementById('clear-all');
                const permissionCount = document.getElementById('permission-count');

                function updateCount() {
                    const selected = document.querySelectorAll(
                        '.permission-checkbox:checked'
                    ).length;

                    permissionCount.textContent = selected;
                }

                selectAllButton.addEventListener('click', function () {
                    checkboxes.forEach(function (checkbox) {
                        checkbox.checked = true;
                    });

                    updateCount();
                });

                clearAllButton.addEventListener('click', function () {
                    checkboxes.forEach(function (checkbox) {
                        checkbox.checked = false;
                    });

                    updateCount();
                });

                checkboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', updateCount);
                });

                updateCount();
            });
        </script>
    @endpush
</x-app-layout>