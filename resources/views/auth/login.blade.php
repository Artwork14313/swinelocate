<x-guest-layout>
    <div class="min-h-screen flex bg-gray-100">

        <!-- Left Side - Swine Farm Image -->
        <div class="hidden lg:flex lg:w-3/5 relative overflow-hidden">

            <img src="{{ asset('images/loginImage.png') }}" alt="Swine Farm"
                class="absolute inset-0 w-full h-full object-cover">

            <!-- Dark overlay -->
            <div class="absolute inset-0 bg-black/25"></div>

            <!-- Branding -->
            <div class="relative z-10 flex flex-col justify-start p-12 text-white">

                <div class="max-w-xl">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-white/90 rounded-xl p-2 w-14 h-14 flex items-center justify-center">
                            <img src="/images/swineicon.svg" alt="SwineLocate" />
                        </div>

                        <span class="text-2xl font-bold tracking-wide">
                            SwineLocate
                        </span>
                    </div>




                    <h1 class="pb-40 text-4xl xl:text-5xl font-bold leading-tight mb-4">
                        Smarter Farming,
                        <br>
                        Healthier Swine.
                    </h1>

                    <p class="text-lg text-white/90 max-w-lg pt-20">
                        A QR-Based Web and Mobile Application for Swine Traceability and Management with Offline Data
                        Synchronization.
                    </p>

                    <div class="mt-6 flex items-center gap-6 text-sm text-white/90">
                        <span>✓ Swine Traceability</span>
                        <span>✓ Farm Management</span>
                        <span>✓ Health Monitoring</span>
                    </div>
                </div>

            </div>
        </div>


        <!-- Right Side - Login -->
        <div class="w-full lg:w-2/5 flex items-center justify-center bg-white px-6 py-12">

            <div class="w-full max-w-md">

                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex items-center justify-center
                                w-14 h-14 rounded-2xl
                                bg-emerald-100 text-emerald-700 mb-3">

                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M19 7h-1V5h-2v2h-2V5h-2v2H9V5H7v2H5a3 3 0 0 0-3 3v4c0 1.66 1.34 3 3 3h1v2h2v-2h6v2h2v-2h1c1.66 0 3-1.34 3-3v-1h1c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zm-4 8H5c-.55 0-1-.45-1-1v-4c0-.55.45-1 1-1h10c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1zm5-4h-1v-2h1v2z" />
                        </svg>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900">
                        SwineLocate
                    </h1>

                    <p class="text-sm text-gray-500 mt-1">
                        Swine Traceability & Management System
                    </p>
                </div>


                <!-- Login Header -->
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">
                        Welcome back
                    </h2>

                    <p class="mt-2 text-gray-500">
                        Sign in to start managing your swine & farms.
                    </p>
                </div>


                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />


                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-medium" />

                        <x-text-input id="email" class="block mt-2 w-full rounded-xl border-gray-300
                                   focus:border-emerald-500 focus:ring-emerald-500
                                   py-3" type="email" name="email" :value="old('email')" required autofocus
                            autocomplete="username" placeholder="Enter your email" />

                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>


                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between">

                            <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium" />

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-medium text-emerald-700
                                                           hover:text-emerald-800">
                                    {{ __('Forgot password?') }}
                                </a>
                            @endif

                        </div>

                        <x-text-input id="password" class="block mt-2 w-full rounded-xl border-gray-300
                                   focus:border-emerald-500 focus:ring-emerald-500
                                   py-3" type="password" name="password" required autocomplete="current-password"
                            placeholder="Enter your password" />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>


                    <!-- Remember Me -->
                    <div class="flex items-center">

                        <input id="remember_me" type="checkbox" class="rounded border-gray-300
                                   text-emerald-600 shadow-sm
                                   focus:ring-emerald-500" name="remember">

                        <label for="remember_me" class="ms-2 text-sm text-gray-600">
                            {{ __('Remember me') }}
                        </label>

                    </div>


                    <!-- Login Button -->
                    <button type="submit" class="w-full inline-flex justify-center items-center
                               px-6 py-3.5 rounded-xl
                               bg-emerald-700 text-white
                               font-semibold text-sm
                               shadow-sm
                               hover:bg-emerald-800
                               focus:outline-none
                               focus:ring-2
                               focus:ring-emerald-500
                               focus:ring-offset-2
                               transition duration-200">
                        {{ __('Log in') }}

                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>

                </form>


                <!-- Footer -->
                <div class="mt-10 text-center">
                    <p class="text-xs text-gray-400">
                        SwineLocate
                    </p>

                    <p class="text-xs text-gray-400 mt-1">
                        Swine Traceability and Farm Management System
                    </p>
                </div>

            </div>

        </div>

    </div>
</x-guest-layout>