<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h2 class="text-2xl font-bold text-gray-900">
                    Movement Details
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Complete information about this swine movement.
                </p>

            </div>


            <div class="flex gap-2">

                <a
                    href="{{ route('swine-movements.index') }}"
                    class="inline-flex items-center rounded-lg
                           border border-gray-300 bg-white px-4 py-2
                           text-sm font-medium text-gray-700
                           hover:bg-gray-50"
                >
                    Back to Movements
                </a>


                @if ($movement->swine)

                    <a
                        href="{{ route('swine.show', $movement->swine) }}"
                        class="inline-flex items-center rounded-lg
                               bg-[#3368A0] px-4 py-2
                               text-sm font-semibold text-white
                               hover:bg-[#28557F]"
                    >
                        View Swine
                    </a>

                @endif

            </div>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">


            {{-- ==========================================================
                STATUS
            =========================================================== --}}

            @php

                $status = strtolower(
                    trim($movement->status ?? 'completed')
                );

                $resolution = strtolower(
                    trim($movement->conflict_resolution ?? '')
                );

            @endphp


            <div class="overflow-hidden rounded-xl bg-white
                        shadow-sm ring-1 ring-gray-200">


                {{-- Header --}}

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Movement Status
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Current synchronization and conflict status of this movement.
                    </p>

                </div>


                {{-- Status Information --}}

                <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-2">


                    {{-- ==================================================
                        STATUS
                    =================================================== --}}

                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Status
                        </p>


                        @if ($status === 'completed')

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-green-100 px-3 py-1.5
                                       text-sm font-semibold text-green-700"
                            >
                                Completed
                            </span>


                        @elseif ($status === 'superseded')

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-gray-200 px-3 py-1.5
                                       text-sm font-semibold text-gray-700"
                            >
                                Superseded
                            </span>


                        @elseif ($status === 'conflict')

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-yellow-100 px-3 py-1.5
                                       text-sm font-semibold text-yellow-700"
                            >
                                Conflict
                            </span>


                        @elseif ($status === 'rejected')

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-red-100 px-3 py-1.5
                                       text-sm font-semibold text-red-700"
                            >
                                Rejected
                            </span>


                        @elseif ($status === 'pending')

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-gray-100 px-3 py-1.5
                                       text-sm font-semibold text-gray-700"
                            >
                                Pending
                            </span>


                        @else

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-gray-100 px-3 py-1.5
                                       text-sm font-semibold text-gray-700"
                            >
                                {{ ucfirst($status) }}
                            </span>

                        @endif

                    </div>


                    {{-- ==================================================
                        CONFLICT RESOLUTION
                    =================================================== --}}

                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Conflict Resolution
                        </p>


                        {{-- Offline Version Kept --}}

                        @if ($resolution === 'offline')

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-blue-100 px-3 py-1.5
                                       text-sm font-semibold text-blue-700"
                            >
                                Offline Version Kept
                            </span>


                        {{-- Online Version Kept --}}

                        @elseif ($resolution === 'online')

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-purple-100 px-3 py-1.5
                                       text-sm font-semibold text-purple-700"
                            >
                                Online Version Kept
                            </span>


                        {{-- No Conflict --}}

                        @elseif (
                            $status === 'completed' &&
                            empty($resolution)
                        )

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-green-100 px-3 py-1.5
                                       text-sm font-semibold text-green-700"
                            >
                                No Conflict
                            </span>


                        {{-- Conflict still unresolved --}}

                        @elseif ($status === 'conflict')

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-yellow-100 px-3 py-1.5
                                       text-sm font-semibold text-yellow-700"
                            >
                                Awaiting Resolution
                            </span>


                        {{-- Superseded without resolution --}}

                        @elseif ($status === 'superseded')

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-gray-100 px-3 py-1.5
                                       text-sm font-semibold text-gray-600"
                            >
                                Superseded
                            </span>


                        {{-- Unknown --}}

                        @else

                            <span
                                class="mt-2 inline-flex items-center rounded-full
                                       bg-gray-100 px-3 py-1.5
                                       text-sm font-semibold text-gray-600"
                            >
                                Not Applicable
                            </span>

                        @endif

                    </div>

                </div>


                {{-- ======================================================
                    SYNCHRONIZATION INFORMATION
                ======================================================= --}}

                @if ($resolution === 'offline')

                    <div class="border-t border-gray-200 px-6 py-5">

                        <div class="rounded-lg bg-blue-50 p-4">

                            <p class="text-sm font-semibold text-blue-800">
                                Synchronization Information
                            </p>

                            <p class="mt-1 text-sm text-blue-700">
                                A synchronization conflict occurred and the
                                offline movement was selected. The offline
                                destination was applied as the final swine
                                location.
                            </p>

                        </div>

                    </div>


                @elseif ($resolution === 'online')

                    <div class="border-t border-gray-200 px-6 py-5">

                        <div class="rounded-lg bg-purple-50 p-4">

                            <p class="text-sm font-semibold text-purple-800">
                                Synchronization Information
                            </p>

                            <p class="mt-1 text-sm text-purple-700">
                                A synchronization conflict occurred and the
                                online/server version was kept. The offline
                                movement was not applied as the final location
                                change.
                            </p>

                        </div>

                    </div>


                @elseif ($status === 'conflict')

                    <div class="border-t border-gray-200 px-6 py-5">

                        <div class="rounded-lg bg-yellow-50 p-4">

                            <p class="text-sm font-semibold text-yellow-800">
                                Synchronization Information
                            </p>

                            <p class="mt-1 text-sm text-yellow-700">
                                This movement has a synchronization conflict
                                that has not yet been resolved.
                            </p>

                        </div>

                    </div>


                @elseif ($status === 'superseded')

                    <div class="border-t border-gray-200 px-6 py-5">

                        <div class="rounded-lg bg-gray-50 p-4">

                            <p class="text-sm font-semibold text-gray-800">
                                Synchronization Information
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                This movement was superseded by another movement
                                during synchronization. It is retained in the
                                history for audit and traceability purposes but
                                is not the final movement applied to the swine.
                            </p>

                        </div>

                    </div>

                @endif

            </div>


            {{-- ==========================================================
                SWINE
            =========================================================== --}}

            <div class="overflow-hidden rounded-xl bg-white
                        shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Swine
                    </p>


                    <div class="mt-1 flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">

                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ $movement->swine?->tag_number ?? '—' }}
                        </h1>


                        @if ($movement->swine?->name)

                            <span class="text-sm text-gray-500">
                                {{ $movement->swine->name }}
                            </span>

                        @endif

                    </div>


                    @if ($movement->swine?->farm)

                        <p class="mt-2 text-sm text-gray-500">

                            Farm:

                            <span class="font-medium text-gray-700">
                                {{ $movement->swine->farm->name }}
                            </span>

                        </p>

                    @endif

                </div>


                <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-2">


                    {{-- Movement Date --}}

                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Movement Date
                        </p>


                        <p class="mt-1 text-sm font-semibold text-gray-900">

                            {{ $movement->movement_date?->format('F d, Y') ?? '—' }}

                        </p>


                        @if ($movement->movement_date)

                            <p class="mt-1 text-xs text-gray-500">
                                {{ $movement->movement_date->format('h:i A') }}
                            </p>

                        @endif

                    </div>


                    {{-- Recorded By --}}

                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Recorded By
                        </p>


                        <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $movement->recordedBy?->name ?? 'Unknown' }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- ==========================================================
                LOCATION MOVEMENT
            =========================================================== --}}

            <div class="overflow-hidden rounded-xl bg-white
                        shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Location Movement
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Location before and after this movement record.
                    </p>

                </div>


                <div class="grid grid-cols-1 gap-6 px-6 py-6 md:grid-cols-3">


                    {{-- From --}}

                    <div class="rounded-lg bg-gray-50 p-5">

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            From Location
                        </p>


                        <p class="mt-2 text-lg font-semibold text-gray-900">
                            {{ $movement->fromLocation?->name ?? 'No previous location' }}
                        </p>


                        @if ($movement->fromLocation?->location_code)

                            <p class="mt-1 text-xs text-gray-500">
                                {{ $movement->fromLocation->location_code }}
                            </p>

                        @endif

                    </div>


                    {{-- Arrow --}}

                    <div class="flex items-center justify-center">

                        <div class="text-center">

                            <div class="mx-auto flex h-10 w-10
                                        items-center justify-center
                                        rounded-full bg-[#C8DFDB]">

                                <span class="text-lg font-bold text-[#3368A0]">
                                    →
                                </span>

                            </div>


                            <p class="mt-2 text-xs text-gray-500">
                                Moved to
                            </p>

                        </div>

                    </div>


                    {{-- To --}}

                    <div class="rounded-lg bg-blue-50 p-5">

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Destination
                        </p>


                        <p class="mt-2 text-lg font-semibold text-gray-900">
                            {{ $movement->toLocation?->name ?? '—' }}
                        </p>


                        @if ($movement->toLocation?->location_code)

                            <p class="mt-1 text-xs text-gray-500">
                                {{ $movement->toLocation->location_code }}
                            </p>

                        @endif

                    </div>

                </div>

            </div>


            {{-- ==========================================================
                MOVEMENT INFORMATION
            =========================================================== --}}

            <div class="overflow-hidden rounded-xl bg-white
                        shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Movement Information
                    </h3>

                </div>


                <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-2">


                    {{-- Reason --}}

                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Reason
                        </p>


                        <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $movement->reason ?? 'No reason specified' }}
                        </p>

                    </div>


                    {{-- Recorded Date --}}

                    <div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Record Created
                        </p>


                        <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $movement->created_at?->format('F d, Y h:i A') ?? '—' }}
                        </p>

                    </div>


                    {{-- Notes --}}

                    <div class="sm:col-span-2">

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Notes
                        </p>


                        <div class="mt-2 rounded-lg bg-gray-50 p-4">

                            @if ($movement->notes)

                                <p class="whitespace-pre-line text-sm text-gray-700">
                                    {{ $movement->notes }}
                                </p>

                            @else

                                <p class="text-sm text-gray-400">
                                    No notes were recorded.
                                </p>

                            @endif

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>

</x-app-layout>