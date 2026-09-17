{{-- ================================================================
MOBILE SIDEBAR OVERLAY
================================================================ --}}

<div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="lg:hidden">

    {{-- Mobile Header --}}
    <div
        class="fixed top-0 left-0 right-0 z-30 h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4">

        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">

            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#B9D175]">

                <img src="/images/swineicon2.svg" alt="SwineLocate" class="h-7 w-7" />

            </div>

            <div class="font-bold text-gray-900">
                SwineLocate
            </div>

        </a>

        <button type="button" @click="sidebarOpen = true" class="inline-flex items-center justify-center rounded-lg p-2
                   text-gray-600 hover:bg-gray-100 hover:text-gray-900
                   focus:outline-none focus:ring-2 focus:ring-indigo-500" aria-label="Open navigation menu">

            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />

            </svg>

        </button>

    </div>


    {{-- Mobile Overlay --}}
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/40"
        style="display: none;"></div>


    {{-- Mobile Sidebar --}}
    <aside x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col
               border-r border-gray-200 bg-[#F2F2ED] shadow-xl" style="display: none;">

        {{-- ========================================================
        MOBILE BRAND HEADER
        ======================================================== --}}

        <div class="flex h-16 shrink-0 items-center justify-between
                    border-b border-gray-200 px-5">

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">

                <div class="flex h-9 w-9 items-center justify-center
                            rounded-lg bg-[#B9D175]">

                    <img src="/images/swineicon2.svg" alt="SwineLocate" class="h-7 w-7" />

                </div>

                <div>

                    <div class="font-bold text-gray-900">
                        SwineLocate
                    </div>

                    <div class="text-[10px] text-gray-500">
                        Swine Traceability System
                    </div>

                </div>

            </a>

            <button type="button" @click="sidebarOpen = false" class="rounded-lg p-2 text-gray-500
                       hover:bg-gray-100 hover:text-gray-700" aria-label="Close navigation menu">

                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />

                </svg>

            </button>

        </div>


        {{-- ========================================================
        MOBILE NAVIGATION
        ======================================================== --}}

        <div class="flex-1 overflow-y-auto px-4 py-5">

            {{-- ====================================================
            DASHBOARD
            ==================================================== --}}

            <div class="mb-6">

                <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="flex items-center gap-3 rounded-lg px-3 py-2.5
                           text-sm font-medium
                           {{ request()->routeIs('dashboard')
    ? 'bg-blue-700 text-white'
    : 'text-gray-700 hover:bg-gray-50' }}">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0h6" />

                    </svg>

                    Dashboard

                </a>

            </div>


            {{-- ====================================================
            1. USER & ACCESS MANAGEMENT
            ==================================================== --}}

            @if(
                    Auth::user()->hasPermission('manage-users') ||
                    Auth::user()->hasPermission('manage-roles')
                )

                <div class="mb-6">

                    <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                          tracking-wider text-gray-400">

                        User & Access

                    </p>

                    <div class="space-y-1">

                        @if(Auth::user()->hasPermission('manage-users'))

                                    <a href="{{ route('users.index') }}" @click="sidebarOpen = false" class="flex items-center gap-3
                                                                                                       rounded-lg px-3 py-2 text-sm text-gray-400 {{
                            request()->routeIs('users.*')
                            ? 'bg-blue-700 text-white'
                            : 'text-gray-700 hover:bg-gray-50'
                                                                                                                           }}">
                                        Users
                                    </a>

                        @endif


                        @if(Auth::user()->hasPermission('manage-roles'))

                                    <a href="{{ route('roles.index') }}" @click="sidebarOpen = false" class="flex items-center gap-3
                                                                                                                       rounded-lg px-3 py-2 text-sm text-gray-400 {{
                            request()->routeIs('roles.*')
                            ? 'bg-blue-700 text-white'
                            : 'text-gray-700 hover:bg-gray-50'}}">
                                        Roles & Permissions
                                    </a>

                        @endif

                    </div>

                </div>

            @endif


            {{-- ====================================================
            2. FARM MANAGEMENT
            ==================================================== --}}

            @if(
                    Auth::user()->hasPermission('manage-farms') ||
                    Auth::user()->hasPermission('manage-swine')
                )

                <div class="mb-6">

                    <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                          tracking-wider text-gray-400">

                        Farm Management

                    </p>

                    <div class="space-y-1">

                        @if(Auth::user()->hasPermission('manage-farms'))

                                    <a href="{{ route('farms.index') }}" @click="sidebarOpen = false" class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                                                       text-sm font-medium
                                                                                                                                                       {{ request()->routeIs('farms.*')
                            ? 'bg-blue-700 text-white'
                            : 'text-gray-700 hover:bg-gray-50' }}">
                                        Farms
                                    </a>

                        @endif


                        @if(Auth::user()->hasPermission('manage-swine'))

                                    <a href="{{ route('swine.index') }}" @click="sidebarOpen = false" class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                                                       text-sm font-medium
                                                                                                                                                       {{ request()->routeIs('swine.*') &&
                            !request()->routeIs('swine.scan')
                            ? 'bg-blue-700 text-white'
                            : 'text-gray-700 hover:bg-gray-50' }}">
                                        Swine
                                    </a>

                        @endif

                    </div>

                </div>

            @endif


            {{-- ====================================================
            3. VETERINARY MANAGEMENT
            ==================================================== --}}

            @if(Auth::user()->hasPermission('manage-health'))

                    <div class="mb-6">

                        <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                                                      tracking-wider text-gray-400">

                            Veterinary Management

                        </p>

                        <div class="space-y-1">

                            <a href="{{ route('health-records.index') }}" @click="sidebarOpen = false" class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                           text-sm font-medium
                                                                                           {{
                request()->routeIs('health-records.*')
                ? 'bg-blue-700 text-white'
                : 'text-gray-700 hover:bg-gray-50'
                                                                                           }}">
                                Health Records
                            </a>


                        </div>

                    </div>

            @endif


            {{-- ====================================================
            4. FARM OPERATIONS
            ==================================================== --}}

            @if(
                    Auth::user()->hasPermission('record-weight') ||
                    Auth::user()->hasPermission('manage-movements')
                )

                <div class="mb-6">

                    <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                          tracking-wider text-gray-400">

                        Farm Operations

                    </p>

                    <div class="space-y-1">

                        @if(Auth::user()->hasPermission('record-weight'))

                                    <a href="{{ route('weight-records.index') }}" @click="sidebarOpen = false"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                                                       text-sm font-medium
                                                                                                                                                       {{
                            request()->routeIs('weight-records.*')
                            ? 'bg-blue-700 text-white'
                            : 'text-gray-700 hover:bg-gray-50'
                                                                                                                                                       }}">
                                        Weight Records
                                    </a>


                                    <a href="{{ route('growth-monitoring.index') }}" @click="sidebarOpen = false"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                                                       text-sm font-medium
                                                                                                                                                       {{
                            request()->routeIs('growth-monitoring.*')
                            ? 'bg-blue-700 text-white'
                            : 'text-gray-700 hover:bg-gray-50'
                                                                                                                                                       }}">
                                        Growth Monitoring
                                    </a>

                        @endif


                        @if(Auth::user()->hasPermission('manage-movements'))

                                    <a href="{{ route('swine-movements.index') }}" @click="sidebarOpen = false"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                                                       text-sm font-medium
                                                                                                                                                       {{
                            request()->routeIs('swine-movements.*')
                            ? 'bg-blue-700 text-white'
                            : 'text-gray-700 hover:bg-gray-50'
                                                                                                                                                       }}">
                                        Swine Movements
                                    </a>

                        @endif

                    </div>

                </div>

            @endif


            {{-- ====================================================
            5. QR & TRACEABILITY
            ==================================================== --}}

            @if(
                    Auth::user()->hasPermission('scan-qr') ||
                    Auth::user()->hasPermission('view-traceability')
                )

                <div class="mb-6">

                    <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                          tracking-wider text-gray-400">

                        QR & Traceability

                    </p>

                    <div class="space-y-1">

                        @if(
                                Auth::user()->hasPermission('scan-qr') ||
                                Auth::user()->hasPermission('view-traceability')
                            )

                                    <a href="{{ route('qr.scanner') }}" @click="sidebarOpen = false"
                                        class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                                                       text-sm font-medium transition
                                                                                                                                                       {{
                            request()->routeIs('qr.scanner') ||
                            request()->routeIs('swine.scan')
                            ? 'bg-blue-700 text-white'
                            : 'text-gray-700 hover:bg-gray-50'
                                                                                                                                                       }}">

                                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                            stroke="currentColor">

                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 4.5A1.5 1.5 0 015.25 3h3A1.5 1.5 0 019.75 4.5v3A1.5 1.5 0 018.25 9h-3A1.5 1.5 0 013.75 7.5v-3z
                                                                                                                                                        M14.25 4.5A1.5 1.5 0 0115.75 3h3a1.5 1.5 0 011.5 1.5v3A1.5 1.5 0 0118.75 9h-3a1.5 1.5 0 01-1.5-1.5v-3z
                                                                                                                                                        M3.75 16.5A1.5 1.5 0 015.25 15h3a1.5 1.5 0 011.5 1.5v3A1.5 1.5 0 018.25 21h-3A1.5 1.5 0 013.75 19.5v-3z
                                                                                                                                                        M14.25 15h1.5v1.5h-1.5V15zm3 0h1.5v1.5h-1.5V15zm-3 3h1.5v1.5h-1.5V18zm3 0h1.5v1.5h-1.5V18z" />

                                        </svg>

                                        <span>Scan QR</span>

                                    </a>

                        @endif

                    </div>

                </div>

            @endif


            {{-- ====================================================
            6. DATA SYNCHRONIZATION
            ==================================================== --}}

            <div class="mb-6">

                <p class="mb-2 px-3 text-xs font-semibold uppercase
                          tracking-wider text-gray-400">

                    Data Synchronization

                </p>

                <div class="space-y-1">

                    <a href="{{ route('sync-status.index') }}" @click="sidebarOpen = false" class="flex items-center gap-3 rounded-lg px-3 py-2
                               text-sm font-medium
                               {{
    request()->routeIs('sync-status.index')
    ? 'bg-blue-700 text-white'
    : 'text-gray-700 hover:bg-gray-50'
                               }}">
                        Sync Status
                    </a>

                </div>

            </div>


            {{-- ====================================================
            7. REPORTS & ANALYTICS
            ==================================================== --}}

            @if(Auth::user()->hasPermission('view-reports'))

                <div class="mb-6">

                    <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                          tracking-wider text-gray-400">

                        Reports & Analytics

                    </p>

                    <div class="space-y-1">

                        <span class="flex cursor-not-allowed items-center gap-3
                                                               rounded-lg px-3 py-2 text-sm text-gray-400">
                            Reports
                        </span>

                        <span class="flex cursor-not-allowed items-center gap-3
                                                               rounded-lg px-3 py-2 text-sm text-gray-400">
                            Analytics
                        </span>

                    </div>

                </div>

            @endif


            {{-- ====================================================
            8. BACKUP & RESTORE
            ==================================================== --}}

            @if(Auth::user()->hasPermission('manage-backups'))

                <div class="mb-6">

                    <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                          tracking-wider text-gray-400">

                        Backup & Restore

                    </p>

                    <div class="space-y-1">

                        <span class="flex cursor-not-allowed items-center gap-3
                                                               rounded-lg px-3 py-2 text-sm text-gray-400">
                            Database Backup
                        </span>

                        <span class="flex cursor-not-allowed items-center gap-3
                                                               rounded-lg px-3 py-2 text-sm text-gray-400">
                            Backup History
                        </span>

                    </div>

                </div>

            @endif

        </div>


        {{-- ========================================================
        MOBILE USER SECTION
        ======================================================== --}}

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

                <a href="{{ route('profile.edit') }}" @click="sidebarOpen = false" class="flex-1 rounded-lg border border-gray-200
                           px-3 py-2 text-center text-xs font-medium
                           text-gray-700 hover:bg-gray-50">
                    Profile
                </a>

                <form method="POST" action="{{ route('logout') }}" class="flex-1">

                    @csrf

                    <button type="submit" class="w-full rounded-lg border border-gray-200
                               px-3 py-2 text-xs font-medium text-gray-700
                               hover:bg-gray-50">
                        Logout
                    </button>

                </form>

            </div>

        </div>

    </aside>

</div>


{{-- ================================================================
DESKTOP SIDEBAR
================================================================ --}}

<aside class="fixed inset-y-0 left-0 z-40 hidden w-64
              border-r border-gray-200 bg-[#F2F2ED]
              lg:flex lg:flex-col">

    {{-- Brand --}}
    <div class="flex h-16 shrink-0 items-center border-b border-gray-200 px-6">

        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">

            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#B9D175]">

                <img src="/images/swineicon2.svg" alt="SwineLocate" class="h-7 w-7" />

            </div>

            <div>

                <div class="font-bold text-gray-900">
                    SwineLocate
                </div>

            </div>

        </a>

    </div>


    {{-- Navigation --}}
    <div class="flex-1 overflow-y-auto px-4 py-5">

        {{-- Dashboard --}}
        <div class="mb-6">

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium
                {{
    request()->routeIs('dashboard')
    ? 'bg-blue-700 text-white'
    : 'text-gray-700 hover:bg-gray-50'
                }}">

                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0h6" />

                </svg>

                Dashboard

            </a>

        </div>


        {{-- ========================================================
        1. USER & ACCESS MANAGEMENT
        ======================================================== --}}

        @if(
                Auth::user()->hasPermission('manage-users') ||
                Auth::user()->hasPermission('manage-roles')
            )

            <div class="mb-6">

                <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                      tracking-wider text-gray-400">

                    User & Access

                </p>

                <div class="space-y-1">

                    @if(Auth::user()->hasPermission('manage-users'))

                            <a href="{{ route('users.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                       text-sm font-medium
                                                                                                                       {{
                        request()->routeIs('users.*')
                        ? 'bg-blue-700 text-white'
                        : 'text-gray-700 hover:bg-gray-50'
                                                                                                                       }}">
                                Users
                            </a>

                    @endif


                    @if(Auth::user()->hasPermission('manage-roles'))

                            <a href="{{ route('roles.index') }}" class="flex items-center gap-3
                                                                                                           rounded-lg px-3 py-2 text-sm {{
                        request()->routeIs('roles.*')
                        ? 'bg-blue-700 text-white'
                        : 'text-gray-700 hover:bg-gray-50'
                                                                                                                       }}">
                                Roles & Permissions
                            </a>

                    @endif

                </div>

            </div>

        @endif


        {{-- ========================================================
        2. FARM MANAGEMENT
        ======================================================== --}}

        @if(
                Auth::user()->hasPermission('manage-farms') ||
                Auth::user()->hasPermission('manage-swine')
            )

            <div class="mb-6">

                <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                      tracking-wider text-gray-400">

                    Farm Management

                </p>

                <div class="space-y-1">

                    @if(Auth::user()->hasPermission('manage-farms'))

                            <a href="{{ route('farms.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                       text-sm font-medium
                                                                                                                       {{
                        request()->routeIs('farms.*')
                        ? 'bg-blue-700 text-white'
                        : 'text-gray-700 hover:bg-gray-50'
                                                                                                                       }}">
                                Farms
                            </a>

                    @endif


                    @if(Auth::user()->hasPermission('manage-swine'))

                            <a href="{{ route('swine.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                       text-sm font-medium
                                                                                                                       {{
                        request()->routeIs('swine.*') &&
                        !request()->routeIs('swine.scan')
                        ? 'bg-blue-700 text-white'
                        : 'text-gray-700 hover:bg-gray-50'
                                                                                                                       }}">
                                Swine
                            </a>

                    @endif

                </div>

            </div>

        @endif


        {{-- ========================================================
        3. VETERINARY MANAGEMENT
        ======================================================== --}}

        @if(Auth::user()->hasPermission('manage-health'))

            <div class="mb-6">

                <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                      tracking-wider text-gray-400">

                    Veterinary Management

                </p>

                <div class="space-y-1">

                    <a href="{{ route('health-records.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2
                                                           text-sm font-medium
                                                           {{
            request()->routeIs('health-records.*')
            ? 'bg-blue-700 text-white'
            : 'text-gray-700 hover:bg-gray-50'
                                                           }}">
                        Health Records
                    </a>

                </div>

            </div>

        @endif


        {{-- ========================================================
        4. FARM OPERATIONS
        ======================================================== --}}

        @if(
                Auth::user()->hasPermission('record-weight') ||
                Auth::user()->hasPermission('manage-movements')
            )

            <div class="mb-6">

                <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                      tracking-wider text-gray-400">

                    Farm Operations

                </p>

                <div class="space-y-1">

                    @if(Auth::user()->hasPermission('record-weight'))

                            <a href="{{ route('weight-records.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                       text-sm font-medium
                                                                                                                       {{
                        request()->routeIs('weight-records.*')
                        ? 'bg-blue-700 text-white'
                        : 'text-gray-700 hover:bg-gray-50'
                                                                                                                       }}">
                                Weight Records
                            </a>


                            <a href="{{ route('growth-monitoring.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                       text-sm font-medium
                                                                                                                       {{
                        request()->routeIs('growth-monitoring.*')
                        ? 'bg-blue-700 text-white'
                        : 'text-gray-700 hover:bg-gray-50'
                                                                                                                       }}">
                                Growth Monitoring
                            </a>

                    @endif


                    @if(Auth::user()->hasPermission('manage-movements'))

                            <a href="{{ route('swine-movements.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                       text-sm font-medium
                                                                                                                       {{
                        request()->routeIs('swine-movements.*')
                        ? 'bg-blue-700 text-white'
                        : 'text-gray-700 hover:bg-gray-50'
                                                                                                                       }}">
                                Swine Movements
                            </a>

                    @endif

                </div>

            </div>

        @endif


        {{-- ========================================================
        5. QR & TRACEABILITY
        ======================================================== --}}

        @if(
                Auth::user()->hasPermission('scan-qr') ||
                Auth::user()->hasPermission('view-traceability')
            )

            <div class="mb-6">

                <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                      tracking-wider text-gray-400">

                    QR & Traceability

                </p>

                <div class="space-y-1">

                    @if(
                            Auth::user()->hasPermission('scan-qr') ||
                            Auth::user()->hasPermission('view-traceability')
                        )

                            <a href="{{ route('qr.scanner') }}" class="flex items-center gap-3 rounded-lg px-3 py-2
                                                                                                                       text-sm font-medium transition
                                                                                                                       {{
                        request()->routeIs('qr.scanner') ||
                        request()->routeIs('swine.scan')
                        ? 'bg-blue-700 text-white'
                        : 'text-gray-700 hover:bg-gray-50'
                                                                                                                       }}">

                                <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                    stroke="currentColor">

                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 4.5A1.5 1.5 0 015.25 3h3A1.5 1.5 0 019.75 4.5v3A1.5 1.5 0 018.25 9h-3A1.5 1.5 0 013.75 7.5v-3z
                                                                                                                        M14.25 4.5A1.5 1.5 0 0115.75 3h3a1.5 1.5 0 011.5 1.5v3A1.5 1.5 0 0118.75 9h-3a1.5 1.5 0 01-1.5-1.5v-3z
                                                                                                                        M3.75 16.5A1.5 1.5 0 015.25 15h3a1.5 1.5 0 011.5 1.5v3A1.5 1.5 0 018.25 21h-3A1.5 1.5 0 013.75 19.5v-3z
                                                                                                                        M14.25 15h1.5v1.5h-1.5V15zm3 0h1.5v1.5h-1.5V15zm-3 3h1.5v1.5h-1.5V18zm3 0h1.5v1.5h-1.5V18z" />

                                </svg>

                                <span>Scan QR</span>

                            </a>

                    @endif
                </div>

            </div>

        @endif


        {{-- ========================================================
        6. DATA SYNCHRONIZATION
        ======================================================== --}}

        <div class="mb-6">

            <p class="mb-2 px-3 text-xs font-semibold uppercase
                      tracking-wider text-gray-400">

                Data Synchronization

            </p>

            <div class="space-y-1">

                <a href="{{ route('sync-status.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2
                           text-sm font-medium
                           {{
    request()->routeIs('sync-status.index')
    ? 'bg-blue-700 text-white'
    : 'text-gray-700 hover:bg-gray-50'
                           }}">
                    Sync Status
                </a>

            </div>

        </div>


        {{-- ========================================================
        7. REPORTS & ANALYTICS
        ======================================================== --}}

        @if(Auth::user()->hasPermission('view-reports'))

            <div class="mb-6">

                <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                      tracking-wider text-gray-400">

                    Reports & Analytics

                </p>

                <div class="space-y-1">

                    <span class="flex cursor-not-allowed items-center gap-3
                                                           rounded-lg px-3 py-2 text-sm text-gray-400">
                        Reports
                    </span>

                    <span class="flex cursor-not-allowed items-center gap-3
                                                           rounded-lg px-3 py-2 text-sm text-gray-400">
                        Analytics
                    </span>

                </div>

            </div>

        @endif


        {{-- ========================================================
        8. BACKUP & RESTORE
        ======================================================== --}}

        @if(Auth::user()->hasPermission('manage-backups'))

            <div class="mb-6">

                <p class="mb-2 px-3 text-xs font-semibold uppercase
                                                      tracking-wider text-gray-400">

                    Backup & Restore

                </p>

                <div class="space-y-1">

                    <span class="flex cursor-not-allowed items-center gap-3
                                                           rounded-lg px-3 py-2 text-sm text-gray-400">
                        Database Backup
                    </span>

                    <span class="flex cursor-not-allowed items-center gap-3
                                                           rounded-lg px-3 py-2 text-sm text-gray-400">
                        Backup History
                    </span>

                </div>

            </div>

        @endif

    </div>


    {{-- Desktop User --}}
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

            <a href="{{ route('profile.edit') }}" class="flex-1 rounded-lg border border-gray-200
                       px-3 py-2 text-center text-xs font-medium
                       text-gray-700 hover:bg-gray-50">
                Profile
            </a>

            <form method="POST" action="{{ route('logout') }}" class="flex-1">

                @csrf

                <button type="submit" class="w-full rounded-lg border border-gray-200
                           px-3 py-2 text-xs font-medium text-gray-700
                           hover:bg-gray-50">
                    Logout
                </button>

            </form>

        </div>

    </div>

</aside>