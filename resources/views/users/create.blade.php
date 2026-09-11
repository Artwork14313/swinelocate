<x-app-layout>

    <x-slot name="header">

        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Add User
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Create a new SwineLocate system user and assign their role and farm.
            </p>
        </div>

    </x-slot>


    <div class="py-6">

        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

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


            <form
                method="POST"
                action="{{ route('users.store') }}"
                class="space-y-6"
            >

                @csrf


                {{-- Account Information --}}
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-5 py-4">

                        <h3 class="text-lg font-semibold text-gray-800">
                            Account Information
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Enter the user's basic account details.
                        </p>

                    </div>


                    <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                        {{-- Name --}}
                        <div class="md:col-span-2">

                            <label
                                for="name"
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Full Name
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                placeholder="Enter full name"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#3368A0] focus:ring-[#3368A0]"
                            >

                            @error('name')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Email --}}
                        <div>

                            <label
                                for="email"
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Email Address
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                placeholder="example@email.com"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#3368A0] focus:ring-[#3368A0]"
                            >

                            @error('email')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Phone --}}
                        <div>

                            <label
                                for="phone"
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Phone Number
                            </label>

                            <input
                                id="phone"
                                name="phone"
                                type="text"
                                value="{{ old('phone') }}"
                                placeholder="09XXXXXXXXX"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#3368A0] focus:ring-[#3368A0]"
                            >

                            @error('phone')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Role and Farm Assignment --}}
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-5 py-4">

                        <h3 class="text-lg font-semibold text-gray-800">
                            Role & Farm Assignment
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Define what the user can access and, when applicable, which farm they belong to.
                        </p>

                    </div>


                    <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                        {{-- Role --}}
                        <div>

                            <label
                                for="role_id"
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Role
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="role_id"
                                name="role_id"
                                required
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#3368A0] focus:ring-[#3368A0]"
                            >

                                <option value="">
                                    Select Role
                                </option>

                                @foreach($roles as $role)

                                    <option
                                        value="{{ $role->id }}"
                                        @selected(old('role_id') == $role->id)
                                    >
                                        {{ $role->name }}
                                    </option>

                                @endforeach

                            </select>

                            @error('role_id')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Farm --}}
                        <div>

                            <label
                                for="farm_id"
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Assigned Farm
                            </label>

                            <select
                                id="farm_id"
                                name="farm_id"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#3368A0] focus:ring-[#3368A0]"
                            >

                                <option value="">
                                    No Farm Assigned
                                </option>

                                @foreach($farms as $farm)

                                    <option
                                        value="{{ $farm->id }}"
                                        @selected(old('farm_id') == $farm->id)
                                    >
                                        {{ $farm->name }}
                                    </option>

                                @endforeach

                            </select>

                            @error('farm_id')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Password & Status --}}
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-5 py-4">

                        <h3 class="text-lg font-semibold text-gray-800">
                            Password & Account Status
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Set the user's initial password and account status.
                        </p>

                    </div>


                    <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                        {{-- Password --}}
                        <div>

                            <label
                                for="password"
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Password
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="Minimum 8 characters"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#3368A0] focus:ring-[#3368A0]"
                            >

                            @error('password')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Confirm Password --}}
                        <div>

                            <label
                                for="password_confirmation"
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Confirm Password
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="Re-enter password"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#3368A0] focus:ring-[#3368A0]"
                            >

                        </div>


                        {{-- Status --}}
                        <div>

                            <label
                                for="status"
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Account Status
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="status"
                                name="status"
                                required
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#3368A0] focus:ring-[#3368A0]"
                            >

                                <option
                                    value="active"
                                    @selected(old('status', 'active') === 'active')
                                >
                                    Active
                                </option>

                                <option
                                    value="inactive"
                                    @selected(old('status') === 'inactive')
                                >
                                    Inactive
                                </option>

                            </select>

                            @error('status')

                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Actions --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a
                        href="{{ route('users.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-[#3368A0] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#28547f] focus:outline-none focus:ring-2 focus:ring-[#3368A0] focus:ring-offset-2"
                    >
                        Create User
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>