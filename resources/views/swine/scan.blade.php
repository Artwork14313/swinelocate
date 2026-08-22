<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Swine Traceability
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                QR-based swine identification and movement history
            </p>
        </div>
    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            {{-- Swine Information --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                {{-- Header --}}
                <div class="border-b border-gray-200 px-6 py-6 text-center">

                    <p class="text-sm font-medium text-indigo-600">
                        SwineLocate Traceability Record
                    </p>

                    <h1 class="mt-2 text-3xl font-bold text-gray-900">
                        {{ $swine->tag_number }}
                    </h1>

                    @if ($swine->name)

                        <p class="mt-1 text-gray-500">
                            {{ $swine->name }}
                        </p>

                    @endif

                </div>


                {{-- Current Location --}}
                <div class="px-6 pt-6">

                    <div class="rounded-xl bg-indigo-50 p-5">

                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                            Current Location
                        </p>

                        <p class="mt-2 text-xl font-bold text-gray-900">
                            {{ $swine->currentLocation?->name ?? 'No location assigned' }}
                        </p>

                        @if ($swine->currentLocation?->location_code)

                            <p class="mt-1 font-mono text-sm text-indigo-700">
                                {{ $swine->currentLocation->location_code }}
                            </p>

                        @endif

                    </div>

                </div>


                {{-- Basic Information --}}
                <div class="px-6 py-6">

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        {{-- Tag --}}
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Tag Number
                            </p>

                            <p class="mt-1 font-semibold text-gray-900">
                                {{ $swine->tag_number }}
                            </p>
                        </div>


                        {{-- Status --}}
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Status
                            </p>

                            <p class="mt-1 font-semibold
                                {{ $swine->status === 'active'
                                    ? 'text-green-600'
                                    : 'text-gray-700' }}">
                                {{ ucfirst($swine->status) }}
                            </p>
                        </div>


                        {{-- Sex --}}
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Sex
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ ucfirst($swine->sex) }}
                            </p>
                        </div>


                        {{-- Breed --}}
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Breed
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $swine->breed ?: '—' }}
                            </p>
                        </div>


                        {{-- Farm --}}
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Farm
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $swine->farm?->name ?? '—' }}
                            </p>
                        </div>


                        {{-- Birth Date --}}
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Birth Date
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $swine->birth_date?->format('F d, Y') ?? '—' }}
                            </p>
                        </div>


                        {{-- Acquisition Date --}}
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Acquisition Date
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $swine->acquisition_date?->format('F d, Y') ?? '—' }}
                            </p>
                        </div>

                    </div>

                </div>


                {{-- Traceability Summary --}}
                <div class="border-t border-gray-200 px-6 py-6">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Traceability Summary
                    </h3>

                    <div class="mt-4 grid grid-cols-2 gap-4">

                        <div class="rounded-xl bg-gray-50 p-4">

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Total Movements
                            </p>

                            <p class="mt-2 text-2xl font-bold text-gray-900">
                                {{ $swine->movements->count() }}
                            </p>

                        </div>


                        <div class="rounded-xl bg-gray-50 p-4">

                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Last Movement
                            </p>

                            @if ($swine->movements->isNotEmpty())

                                <p class="mt-2 text-sm font-bold text-gray-900">
                                    {{ $swine->movements->first()->movement_date->format('M d, Y') }}
                                </p>

                            @else

                                <p class="mt-2 text-sm font-medium text-gray-500">
                                    No movement
                                </p>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- Movement History --}}
                <div class="border-t border-gray-200 px-6 py-6">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Movement History
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Recorded location movements of this swine.
                    </p>


                    @if ($swine->movements->isNotEmpty())

                        <div class="mt-5 space-y-4">

                            @foreach ($swine->movements as $movement)

                                <div class="rounded-xl border border-gray-200 p-4">

                                    {{-- Date --}}
                                    <div class="flex items-center justify-between">

                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $movement->movement_date->format('M d, Y') }}
                                        </p>

                                        <p class="text-xs text-gray-500">
                                            {{ $movement->movement_date->format('h:i A') }}
                                        </p>

                                    </div>


                                    {{-- From / To --}}
                                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">

                                        {{-- From --}}
                                        <div>

                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                From
                                            </p>

                                            <p class="mt-1 font-semibold text-gray-900">
                                                {{ $movement->fromLocation?->name ?? 'Initial Location' }}
                                            </p>

                                            @if ($movement->fromLocation?->location_code)

                                                <p class="mt-1 font-mono text-xs text-gray-500">
                                                    {{ $movement->fromLocation->location_code }}
                                                </p>

                                            @endif

                                        </div>


                                        {{-- Arrow --}}
                                        <div class="flex items-center justify-center text-xl font-bold text-indigo-600">
                                            →
                                        </div>


                                        {{-- To --}}
                                        <div>

                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                To
                                            </p>

                                            <p class="mt-1 font-semibold text-gray-900">
                                                {{ $movement->toLocation?->name ?? 'Unknown' }}
                                            </p>

                                            @if ($movement->toLocation?->location_code)

                                                <p class="mt-1 font-mono text-xs text-gray-500">
                                                    {{ $movement->toLocation->location_code }}
                                                </p>

                                            @endif

                                        </div>

                                    </div>


                                    {{-- Reason --}}
                                    @if ($movement->reason)

                                        <div class="mt-4 border-t border-gray-100 pt-3">

                                            <p class="text-xs text-gray-500">
                                                Reason:

                                                <span class="font-medium text-gray-700">
                                                    {{ $movement->reason }}
                                                </span>
                                            </p>

                                        </div>

                                    @endif

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="mt-5 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center">

                            <p class="text-sm font-medium text-gray-700">
                                No movement history available.
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                This swine has not been transferred between locations.
                            </p>

                        </div>

                    @endif

                </div>


                {{-- QR Token --}}
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-5 text-center">

                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        QR Identification Token
                    </p>

                    <p class="mt-1 break-all font-mono text-xs text-gray-600">
                        {{ $swine->qr_token }}
                    </p>

                </div>


                {{-- Footer --}}
                <div class="border-t border-gray-200 px-6 py-4 text-center">

                    <p class="text-xs text-gray-400">
                        Powered by SwineLocate
                    </p>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>