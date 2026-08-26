<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Swine Details
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Complete registration, traceability, and QR information.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">

                <a href="{{ route('swine.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2
                           text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Back to Swine
                </a>



            </div>

        </div>
    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if (session('success'))

                <div class="mb-6 rounded-lg border border-green-200
                                bg-green-50 px-4 py-3 text-sm text-green-700">

                    {{ session('success') }}

                </div>

            @endif


            {{-- Main Swine Information --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">


                {{-- Profile Header --}}
                <div class="border-b border-gray-200 px-6 py-6">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                        <div>

                            <div class="flex flex-wrap items-center gap-3">

                                <h3 class="text-2xl font-bold text-gray-900">
                                    {{ $swine->tag_number }}
                                </h3>


                                {{-- Status --}}
                                @if ($swine->status === 'active')

                                    <span class="inline-flex rounded-full bg-green-100
                                                     px-2.5 py-1 text-xs font-semibold
                                                     text-green-700">
                                        Active
                                    </span>

                                @elseif ($swine->status === 'inactive')

                                    <span class="inline-flex rounded-full bg-gray-100
                                                     px-2.5 py-1 text-xs font-semibold
                                                     text-gray-700">
                                        Inactive
                                    </span>

                                @elseif ($swine->status === 'sold')

                                    <span class="inline-flex rounded-full bg-blue-100
                                                     px-2.5 py-1 text-xs font-semibold
                                                     text-blue-700">
                                        Sold
                                    </span>

                                @elseif ($swine->status === 'deceased')

                                    <span class="inline-flex rounded-full bg-red-100
                                                     px-2.5 py-1 text-xs font-semibold
                                                     text-red-700">
                                        Deceased
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-gray-100
                                                     px-2.5 py-1 text-xs font-semibold
                                                     text-gray-700">
                                        {{ ucfirst($swine->status) }}
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Identification --}}
                <div class="border-b border-gray-200 px-6 py-6">

                    <h4 class="text-base font-semibold text-gray-900">
                        Identification
                    </h4>

                    <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">

                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Tag Number
                            </dt>

                            <dd class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $swine->tag_number }}
                            </dd>

                        </div>

                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Sex
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ ucfirst($swine->sex) }}
                            </dd>

                        </div>


                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Breed
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $swine->breed ?: '—' }}
                            </dd>

                        </div>

                    </div>

                </div>


                {{-- Farm & Location --}}
                <div class="border-b border-gray-200 px-6 py-6">

                    <h4 class="text-base font-semibold text-gray-900">
                        Farm & Location
                    </h4>

                    <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">


                        {{-- Farm --}}
                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Farm
                            </dt>

                            @if ($swine->farm)

                                <dd class="mt-1 text-sm font-medium text-gray-900">
                                    {{ $swine->farm->name }}
                                </dd>

                                @if ($swine->farm->farm_code)

                                    <dd class="text-xs text-gray-500">
                                        {{ $swine->farm->farm_code }}
                                    </dd>

                                @endif

                            @else

                                <dd class="mt-1 text-sm text-gray-400">
                                    No farm assigned
                                </dd>

                            @endif

                        </div>


                        {{-- Current Location --}}

                        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                            <div class="border-b border-gray-200 px-6 py-5">

                                <h3 class="text-lg font-semibold text-gray-900">
                                    Current Location
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Current farm location assigned to this swine.
                                </p>

                            </div>


                            <div class="px-6 py-6">

                                @if ($swine->currentLocation)

                                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                                <div>

                                                    <p class="text-lg font-semibold text-gray-900">
                                                        {{ $swine->currentLocation->name }}
                                                    </p>

                                                    @if ($swine->currentLocation->location_code)

                                                        <p class="mt-1 text-sm text-gray-500">
                                                            Location Code:
                                                            <span class="font-medium text-gray-700">
                                                                {{ $swine->currentLocation->location_code }}
                                                            </span>
                                                        </p>

                                                    @endif

                                                </div>


                                                <a href="{{ route('swine.movements.create', $swine) }}" class="inline-flex items-center justify-center rounded-lg
                                       bg-[#3368A0] px-4 py-2 text-sm font-semibold
                                       text-white hover:bg-[#28557F]">
                                                    Move Swine
                                                </a>

                                            </div>

                                @else

                                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                                <div>

                                                    <p class="text-sm font-medium text-gray-900">
                                                        No location assigned
                                                    </p>

                                                    <p class="mt-1 text-sm text-gray-500">
                                                        This swine does not currently have a location.
                                                    </p>

                                                </div>


                                                <a href="{{ route('swine.movements.create', $swine) }}" class="inline-flex items-center justify-center rounded-lg
                                       bg-[#3368A0] px-4 py-2 text-sm font-semibold
                                       text-white hover:bg-[#28557F]">
                                                    Assign Location
                                                </a>

                                            </div>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Registration Information --}}
                <div class="border-b border-gray-200 px-6 py-6">

                    <h4 class="text-base font-semibold text-gray-900">
                        Registration Information
                    </h4>

                    <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">

                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Birth Date
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $swine->birth_date?->format('F d, Y') ?? '—' }}
                            </dd>

                        </div>


                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Acquisition Date
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $swine->acquisition_date?->format('F d, Y') ?? '—' }}
                            </dd>

                        </div>


                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Source
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $swine->source ?: '—' }}
                            </dd>

                        </div>


                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Record Created
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $swine->created_at?->format('F d, Y h:i A') ?? '—' }}
                            </dd>

                        </div>

                    </div>

                </div>


                {{-- Notes --}}
                <div class="border-b border-gray-200 px-6 py-6">

                    <h4 class="text-base font-semibold text-gray-900">
                        Notes
                    </h4>

                    <div class="mt-4 rounded-lg bg-gray-50 p-4">

                        @if ($swine->notes)

                            <p class="whitespace-pre-line text-sm leading-6 text-gray-700">
                                {{ $swine->notes }}
                            </p>

                        @else

                            <p class="text-sm text-gray-400">
                                No notes recorded.
                            </p>

                        @endif

                    </div>

                </div>


                {{-- Actions --}}
                <div class="flex flex-col gap-3 bg-gray-50 px-6 py-5
                            sm:flex-row sm:items-center sm:justify-between">


                    {{-- Delete --}}
                    <form method="POST" action="{{ route('swine.destroy', $swine) }}"
                        onsubmit="return confirm('Are you sure you want to delete this swine record? This action will move the record to the trash.');">

                        @csrf
                        @method('DELETE')

                        <button type="submit" class="w-full rounded-lg border border-red-200
                                   bg-white px-4 py-2.5 text-sm font-semibold
                                   text-red-600 hover:bg-red-50 sm:w-auto">
                            Delete
                        </button>

                    </form>


                    {{-- Action Buttons --}}
                    <div class="flex flex-col gap-3 sm:flex-row">



                        <a href="{{ route('swine.edit', $swine) }}" class="rounded-lg bg-indigo-600 px-5 py-2.5
                                   text-center text-sm font-semibold text-white
                                   hover:bg-indigo-700">
                            Edit Swine
                        </a>

                    </div>

                </div>

            </div>
           
            {{-- QR Code --}}
            <div class="mt-6 overflow-hidden rounded-xl bg-white
                        shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-6 text-center">

                    <h4 class="text-lg font-semibold text-gray-900">
                        QR Code Identification
                    </h4>

                    <p class="mt-1 text-sm text-gray-500">
                        Scan this QR code to access the swine traceability record.
                    </p>

                </div>


                <div class="px-6 py-8">

                    <div class="flex flex-col items-center">


                        {{-- QR --}}
                        <div class="rounded-xl border border-gray-200
                                    bg-white p-5 shadow-sm">

                            {!! $qrCode !!}

                        </div>


                        {{-- Scan Button --}}
                        <a href="{{ route('swine.scan', ['qr_token' => $swine->qr_token]) }}" target="_blank" class="mt-5 inline-flex items-center justify-center
                                   rounded-lg bg-indigo-600 px-5 py-2.5
                                   text-sm font-semibold text-white
                                   hover:bg-indigo-700">
                            Open QR Traceability Page
                        </a>


                        {{-- Token --}}
                        <div class="mt-5 max-w-full text-center">

                            <p class="text-xs font-medium uppercase
                                      tracking-wide text-gray-500">
                                QR Identification Token
                            </p>

                            <p class="mt-2 break-all font-mono text-xs text-gray-500">
                                {{ $swine->qr_token }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>