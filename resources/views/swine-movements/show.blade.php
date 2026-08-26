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

                <a
                    href="{{ route('swine.show', $movement->swine) }}"
                    class="inline-flex items-center rounded-lg
                           bg-[#3368A0] px-4 py-2
                           text-sm font-semibold text-white
                           hover:bg-[#28557F]"
                >
                    View Swine
                </a>

            </div>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">


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
                        Location before and after the movement.
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