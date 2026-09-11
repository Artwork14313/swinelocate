<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    Edit User
                </h2>
                <p class="text-sm text-gray-500">
                    Update account information, role, farm assignment, and status.
                </p>
            </div>

            <a href="{{ route('users.show', $user) }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to User
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

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

            <form method="POST"
                  action="{{ route('users.update', $user) }}"
                  class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Account Information --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">
                            Account Information
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Update the user's basic account details.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-2">

                        {{-- Name --}}
                        <div>
                            <label for="name"
                                   class="block text-sm font-medium text-gray-700">
                                Full Name <span class="text-red-500">*</span>
                            </label>

                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required
                                   maxlength="255"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email"
                                   class="block text-sm font-medium text-gray-700">
                                Email Address <span class="text-red-500">*</span>
                            </label>

                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   required
                                   maxlength="255"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="sm:col-span-2">
                            <label for="phone"
                                   class="block text-sm font-medium text-gray-700">
                                Phone Number
                            </label>

                            <input type="text"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   maxlength="30"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- Role & Farm Assignment --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">
                            Role & Farm Assignment
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Control what the user can access and which farm they belong to.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-2">

                        {{-- Role --}}
                        <div>
                            <label for="role_id"
                                   class="block text-sm font-medium text-gray-700">
                                Role <span class="text-red-500">*</span>
                            </label>

                            <select id="role_id"
                                    name="role_id"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                                <option value="">
                                    Select Role
                                </option>

                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('role_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Farm --}}
                        <div>
                            <label for="farm_id"
                                   class="block text-sm font-medium text-gray-700">
                                Assigned Farm
                            </label>

                            <select id="farm_id"
                                    name="farm_id"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                                <option value="">
                                    No Farm Assigned
                                </option>

                                @foreach ($farms as $farm)
                                    <option value="{{ $farm->id }}"
                                        {{ old('farm_id', $user->farm_id) == $farm->id ? 'selected' : '' }}>
                                        {{ $farm->name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('farm_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- Password --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">
                            Password
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Leave the password fields blank to keep the current password.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-2">

                        {{-- New Password --}}
                        <div>
                            <label for="password"
                                   class="block text-sm font-medium text-gray-700">
                                New Password
                            </label>

                            <input type="password"
                                   id="password"
                                   name="password"
                                   minlength="8"
                                   autocomplete="new-password"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                            <p class="mt-1 text-xs text-gray-500">
                                Minimum of 8 characters.
                            </p>

                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label for="password_confirmation"
                                   class="block text-sm font-medium text-gray-700">
                                Confirm New Password
                            </label>

                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   minlength="8"
                                   autocomplete="new-password"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                    </div>
                </div>

                {{-- Account Status --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">
                            Account Status
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Inactive users cannot log in to SwineLocate.
                        </p>
                    </div>

                    <div class="px-6 py-6">
                        <label for="status"
                               class="block text-sm font-medium text-gray-700">
                            Status <span class="text-red-500">*</span>
                        </label>

                        <select id="status"
                                name="status"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-md">

                            <option value="active"
                                {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="inactive"
                                {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>

                        </select>

                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a href="{{ route('users.show', $user) }}"
                       class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
                        Update User
                    </button>

                </div>

            </form>

        </div>
    </div>
</x-app-layout>