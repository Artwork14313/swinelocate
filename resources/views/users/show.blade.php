<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    User Details
                </h2>
                <p class="text-sm text-gray-500">
                    View account information, role, farm assignment, and account status.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('users.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Back
                </a>

                <a href="{{ route('users.edit', $user) }}"
                   class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Edit User
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

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

            {{-- Profile Header --}}
            <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-8">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white text-2xl font-bold text-indigo-600 shadow">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>

                        <div class="text-white">
                            <h3 class="text-2xl font-bold">
                                {{ $user->name }}
                            </h3>

                            <p class="mt-1 text-sm text-indigo-100">
                                {{ $user->email }}
                            </p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($user->role)
                                    <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-medium text-white">
                                        {{ $user->role->name }}
                                    </span>
                                @endif

                                @if ($user->status === 'active')
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Account Information --}}
            <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Account Information
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-2">

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Full Name
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-900">
                            {{ $user->name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Email Address
                        </p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $user->email }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Phone Number
                        </p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $user->phone ?: 'Not provided' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Account Status
                        </p>

                        <div class="mt-1">
                            @if ($user->status === 'active')
                                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- Role & Farm Assignment --}}
            <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Role & Farm Assignment
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-2">

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Assigned Role
                        </p>

                        @if ($user->role)
                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ $user->role->name }}
                            </p>

                            @if ($user->role->description)
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $user->role->description }}
                                </p>
                            @endif
                        @else
                            <p class="mt-1 text-sm text-gray-500">
                                No role assigned
                            </p>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Assigned Farm
                        </p>

                        @if ($user->farm)
                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ $user->farm->name }}
                            </p>
                        @else
                            <p class="mt-1 text-sm text-gray-500">
                                No farm assigned
                            </p>
                        @endif
                    </div>

                </div>
            </div>

            {{-- Account Dates --}}
            <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Account Information
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-2">

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Account Created
                        </p>

                        <p class="mt-1 text-sm text-gray-900">
                            {{ $user->created_at?->format('F d, Y h:i A') ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Last Updated
                        </p>

                        <p class="mt-1 text-sm text-gray-900">
                            {{ $user->updated_at?->format('F d, Y h:i A') ?? 'N/A' }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap gap-2">

                <a href="{{ route('users.edit', $user) }}"
                   class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Edit User
                </a>

                @if (auth()->id() !== $user->id)
                    <form method="POST"
                          action="{{ route('users.toggle-status', $user) }}"
                          class="inline">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            {{ $user->status === 'active' ? 'Deactivate User' : 'Activate User' }}
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('users.destroy', $user) }}"
                          class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this user?');">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            Delete User
                        </button>
                    </form>
                @endif

            </div>

        </div>
    </div>
</x-app-layout>