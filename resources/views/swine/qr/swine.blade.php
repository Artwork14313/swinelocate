<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Swine Traceability - {{ $swine->tag_number }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body class="min-h-screen bg-gray-50">

    <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6">

        {{-- Header --}}
        <div class="text-center">

            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">
                SwineLocate
            </p>

            <h1 class="mt-1 text-2xl font-bold text-gray-900">
                Swine Traceability
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                QR-based livestock identification and tracking
            </p>

        </div>


        {{-- Swine Card --}}
        <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">

            <div class="border-b border-gray-200 px-6 py-5">

                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Tag Number
                </p>

                <h2 class="mt-1 text-2xl font-bold text-gray-900">
                    {{ $swine->tag_number }}
                </h2>

                @if ($swine->name)

                    <p class="mt-1 text-sm text-gray-500">
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
            <div class="grid grid-cols-2 gap-4 px-6 py-6">

                <div>

                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Sex
                    </p>

                    <p class="mt-1 font-medium text-gray-900">
                        {{ $swine->sex }}
                    </p>

                </div>


                <div>

                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Breed
                    </p>

                    <p class="mt-1 font-medium text-gray-900">
                        {{ $swine->breed ?? 'Not specified' }}
                    </p>

                </div>


                <div>

                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Birth Date
                    </p>

                    <p class="mt-1 font-medium text-gray-900">
                        {{ $swine->birth_date?->format('M d, Y') ?? 'Not specified' }}
                    </p>

                </div>


                <div>

                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Status
                    </p>

                    <p class="mt-1 font-medium text-gray-900">
                        {{ ucfirst($swine->status) }}
                    </p>

                </div>

            </div>


            {{-- Movement History --}}
            <div class="border-t border-gray-200 px-6 py-6">

                <h3 class="text-lg font-semibold text-gray-900">
                    Traceability History
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Recorded location movements.
                </p>


                @if ($swine->movements->isNotEmpty())

                    <div class="mt-5 space-y-4">

                        @foreach ($swine->movements as $movement)

                            <div class="rounded-xl border border-gray-200 p-4">

                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $movement->movement_date->format('M d, Y h:i A') }}
                                </p>


                                <div class="mt-3">

                                    <p class="text-sm text-gray-600">

                                        {{ $movement->fromLocation?->name ?? 'Initial Location' }}

                                        <span class="mx-2 font-semibold text-indigo-600">
                                            →
                                        </span>

                                        <span class="font-semibold text-gray-900">
                                            {{ $movement->toLocation?->name ?? 'Unknown' }}
                                        </span>

                                    </p>

                                </div>


                                @if ($movement->reason)

                                    <p class="mt-2 text-xs text-gray-500">
                                        Reason:
                                        <span class="font-medium text-gray-700">
                                            {{ $movement->reason }}
                                        </span>
                                    </p>

                                @endif

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="mt-5 rounded-xl bg-gray-50 p-6 text-center">

                        <p class="text-sm text-gray-500">
                            No movement history available.
                        </p>

                    </div>

                @endif

            </div>


            {{-- Footer --}}
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 text-center">

                <p class="text-xs text-gray-500">
                    Powered by SwineLocate
                </p>

            </div>

        </div>

    </div>

</body>

</html>