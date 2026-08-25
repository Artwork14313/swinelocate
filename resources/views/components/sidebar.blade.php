<aside class="fixed inset-y-0 left-0 z-40 hidden w-64
           border-r border-gray-200 bg-[#F2F2ED]
           lg:flex lg:flex-col">

    {{-- Brand --}}
    <div class="flex h-16 shrink-0 items-center border-b border-gray-200 px-6">

        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">

            <div class="flex h-9 w-9 items-center justify-center rounded-lg text-white bg-[#B9D175]">
                <img src="/images/swineicon2.svg" alt="SwineLocate" />
            </div>

            <div>
                <div class="font-bold text-gray-900">
                    SwineLocate
                </div>

                <!-- <div class="text-xs text-gray-500">
                    Traceability & Management System
                </div> -->
            </div>

        </a>

    </div>


    {{-- Navigation --}}
    <div class="flex-1 overflow-y-auto px-4 py-5">

        {{-- Dashboard --}}
        <div class="mb-6">

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium
                {{ request()->routeIs('dashboard')
    ? 'bg-indigo-50 text-indigo-700'
    : 'text-gray-700 hover:bg-gray-50' }}">

                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0h6" />
                </svg>

                Dashboard

            </a>

        </div>


        {{-- ========================================================= --}}
        {{-- 1. USER & ACCESS MANAGEMENT --}}
        {{-- ========================================================= --}}

        <div class="mb-6">

            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                User & Access
            </p>

            {{-- We'll activate these when the admin CRUD is implemented --}}
            <div class="space-y-1">

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Users
                </span>

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Roles & Permissions
                </span>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- 2. FARM MANAGEMENT --}}
        {{-- ========================================================= --}}

        <div class="mb-6">

            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Farm Management
            </p>

            <div class="space-y-1">

                <a href="{{ route('farms.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                    {{ request()->routeIs('farms.*')
    ? 'bg-green-50 text-green-700'
    : 'text-gray-700 hover:bg-gray-50' }}">
                    Farms
                </a>


                <a href="{{ route('swine.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                    {{ request()->routeIs('swine.*')
    ? 'bg-green-50 text-green-700'
    : 'text-gray-700 hover:bg-gray-50' }}">
                    Swine
                </a>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- 3. VETERINARY MANAGEMENT --}}
        {{-- ========================================================= --}}

        <div class="mb-6">

            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Veterinary Management
            </p>

            <div class="space-y-1">

                <a href="{{ route('health-records.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
    {{ request()->routeIs('health-records.index') || request()->routeIs('health-records.show') || request()->routeIs('health-records.create') || request()->routeIs('health-records.edit')
    ? 'bg-purple-50 text-purple-700'
    : 'text-gray-700 hover:bg-gray-50' }}">
                    Health Records
                </a>

                <!-- <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Vaccinations
                </span> -->

                    <a href="{{ route('health-records.history.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
    {{ request()->routeIs('health-records.history.index') || request()->routeIs('health-records.history')
    ? 'bg-purple-50 text-purple-700'
    : 'text-gray-700 hover:bg-gray-50' }}">
                        Health History
                    </a>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- 4. FARM OPERATIONS --}}
        {{-- ========================================================= --}}

        <div class="mb-6">

            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Farm Operations
            </p>

            <div class="space-y-1">

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Weight Records
                </span>

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Growth Monitoring
                </span>


                <a href="{{ route('swine.index') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Swine Movements
                </a>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- 5. QR & TRACEABILITY --}}
        {{-- ========================================================= --}}

        <div class="mb-6">

            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                QR & Traceability
            </p>

            <div class="space-y-1">

                <a href="{{ route('swine.index') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    QR & Swine Records
                </a>

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Scan QR
                </span>

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Traceability
                </span>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- 6. OFFLINE SYNCHRONIZATION --}}
        {{-- ========================================================= --}}

        <div class="mb-6">

            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Offline Synchronization
            </p>

            <div class="space-y-1">

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Pending Sync
                </span>

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Sync Status
                </span>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- 7. REPORTS & ANALYTICS --}}
        {{-- ========================================================= --}}

        <div class="mb-6">

            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Reports & Analytics
            </p>

            <div class="space-y-1">

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Reports
                </span>

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Analytics
                </span>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- 8. BACKUP & RESTORE --}}
        {{-- ========================================================= --}}

        <div class="mb-6">

            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Backup & Restore
            </p>

            <div class="space-y-1">

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Database Backup
                </span>

                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400">
                    Backup History
                </span>

            </div>

        </div>

    </div>


    {{-- User --}}
    <div class="border-t border-gray-200 p-4">

        <div class="mb-3">

            <p class="text-sm font-semibold text-gray-900">
                {{ Auth::user()->name }}
            </p>

            <p class="truncate text-xs text-gray-500">
                {{ Auth::user()->email }}
            </p>

        </div>


        <div class="flex gap-2">

            <a href="{{ route('profile.edit') }}"
                class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-center text-xs font-medium text-gray-700 hover:bg-gray-50">
                Profile
            </a>


            <form method="POST" action="{{ route('logout') }}" class="flex-1">

                @csrf

                <button type="submit"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                    Logout
                </button>

            </form>

        </div>

    </div>

</aside>