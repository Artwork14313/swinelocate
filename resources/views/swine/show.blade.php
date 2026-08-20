<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Swine Details
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    View the complete registration information for this swine.
                </p>
            </div>

            <div class="flex gap-3">

                <a href="{{ route('swine.index') }}" class="rounded-lg border border-gray-300 bg-white
                           px-4 py-2 text-sm font-semibold text-gray-700
                           hover:bg-gray-50">
                    Back to Swine
                </a>

                <a href="{{ route('swine.edit', $swine) }}" class="rounded-lg bg-indigo-600 px-4 py-2
                           text-sm font-semibold text-white
                           hover:bg-indigo-700">
                    Edit
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


            {{-- Main Information Card --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                {{-- Profile Header --}}
                <div class="border-b border-gray-200 px-6 py-6">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                        <div>

                            <div class="flex items-center gap-3">

                                <h3 class="text-2xl font-bold text-gray-900">
                                    {{ $swine->tag_number }}
                                </h3>

                                @if ($swine->status === 'active')

                                    <span class="inline-flex rounded-full
                                                                                   bg-green-100 px-2.5 py-1
                                                                                   text-xs font-semibold text-green-700">
                                        Active
                                    </span>

                                @elseif ($swine->status === 'inactive')

                                    <span class="inline-flex rounded-full
                                                                                   bg-gray-100 px-2.5 py-1
                                                                                   text-xs font-semibold text-gray-700">
                                        Inactive
                                    </span>

                                @elseif ($swine->status === 'sold')

                                    <span class="inline-flex rounded-full
                                                                                   bg-blue-100 px-2.5 py-1
                                                                                   text-xs font-semibold text-blue-700">
                                        Sold
                                    </span>

                                @elseif ($swine->status === 'deceased')

                                    <span class="inline-flex rounded-full
                                                                                   bg-red-100 px-2.5 py-1
                                                                                   text-xs font-semibold text-red-700">
                                        Deceased
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full
                                                                                   bg-gray-100 px-2.5 py-1
                                                                                   text-xs font-semibold text-gray-700">
                                        {{ ucfirst($swine->status) }}
                                    </span>

                                @endif

                            </div>

                            @if ($swine->name)

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $swine->name }}
                                </p>

                            @endif

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

                        <!-- <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Name
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $swine->name ?: '—' }}
                            </dd>
                        </div> -->

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
                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Current Location
                            </dt>

                            @if ($swine->currentLocation)

                                <dd class="mt-1 text-sm font-medium text-gray-900">
                                    {{ $swine->currentLocation->name }}
                                </dd>

                                @if ($swine->currentLocation->location_code)

                                    <dd class="text-xs text-gray-500">
                                        {{ $swine->currentLocation->location_code }}
                                    </dd>

                                @endif

                            @else

                                <dd class="mt-1 text-sm text-gray-400">
                                    No location assigned
                                </dd>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- Dates & Source --}}
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

                                @if ($swine->birth_date)
                                    {{ $swine->birth_date->format('F d, Y') }}
                                @else
                                    —
                                @endif

                            </dd>

                        </div>


                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Acquisition Date
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">

                                @if ($swine->acquisition_date)
                                    {{ $swine->acquisition_date->format('F d, Y') }}
                                @else
                                    —
                                @endif

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

                                @if ($swine->created_at)
                                    {{ $swine->created_at->format('F d, Y h:i A') }}
                                @else
                                    —
                                @endif

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
                <div class="flex flex-col gap-3 bg-gray-50 px-6 py-5 sm:flex-row sm:justify-between">

                    <form method="POST" action="{{ route('swine.destroy', $swine) }}"
                        onsubmit="return confirm('Are you sure you want to delete this swine record? This action will move the record to the trash.');">

                        @csrf
                        @method('DELETE')

                        <button type="submit" class="w-full rounded-lg border border-red-200
                                   bg-white px-4 py-2.5 text-sm font-semibold
                                   text-red-600 hover:bg-red-50
                                   sm:w-auto">
                            Delete
                        </button>

                    </form>


                    <div class="flex flex-col gap-3 sm:flex-row">

                        <a href="{{ route('swine.index') }}" class="rounded-lg border border-gray-300 bg-white
                                   px-4 py-2.5 text-center text-sm font-semibold
                                   text-gray-700 hover:bg-gray-50">
                            Back to Swine
                        </a>

                        <a href="{{ route('swine.edit', $swine) }}" class="rounded-lg bg-indigo-600 px-5 py-2.5
                                   text-center text-sm font-semibold text-white
                                   hover:bg-indigo-700">
                            Edit Swine
                        </a>

                        <a href="{{ route('swine.movements.create', $swine) }}" class="rounded-lg bg-indigo-600 px-4 py-2
           text-sm font-semibold text-white
           hover:bg-indigo-700">
                            Move Swine
                        </a>

                    </div>

                </div>

            </div>

            {{-- Movement History --}}
            <div class="border-t border-gray-200 px-6 py-6">

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <h4 class="text-lg font-semibold text-gray-900">
                            Movement History
                        </h4>

                        <p class="mt-1 text-sm text-gray-500">
                            Complete location movement history for this swine.
                        </p>
                    </div>

                    <a href="{{ route('swine.movements.create', $swine) }}" class="inline-flex items-center justify-center rounded-lg
                   bg-indigo-600 px-4 py-2 text-sm font-semibold
                   text-white hover:bg-indigo-700">
                        Move Swine
                    </a>

                </div>


                @if ($swine->movements->isNotEmpty())

                    <div class="mt-6 space-y-4">

                        @foreach ($swine->movements as $movement)

                            <div class="rounded-xl border border-gray-200
                                   bg-white p-5 shadow-sm">

                                {{-- Movement Header --}}
                                <div class="flex flex-col gap-3 sm:flex-row
                                        sm:items-start sm:justify-between">

                                    <div>

                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $movement->movement_date->format('F d, Y') }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $movement->movement_date->format('h:i A') }}
                                        </p>

                                    </div>


                                    @if ($movement->reason)

                                        <span class="inline-flex w-fit rounded-full
                                                   bg-indigo-50 px-3 py-1 text-xs
                                                   font-medium text-indigo-700">
                                            {{ $movement->reason }}
                                        </span>

                                    @endif

                                </div>


                                {{-- Location Movement --}}
                                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">

                                    {{-- From --}}
                                    <div>

                                        <p class="text-xs font-medium uppercase
                                              tracking-wide text-gray-500">
                                            From
                                        </p>

                                        <div class="mt-2">

                                            <p class="font-semibold text-gray-900">
                                                {{ $movement->fromLocation?->name ?? 'Initial Location' }}
                                            </p>

                                            @if ($movement->fromLocation?->location_code)

                                                <p class="mt-1 font-mono text-xs text-gray-500">
                                                    {{ $movement->fromLocation->location_code }}
                                                </p>

                                            @endif

                                        </div>

                                    </div>


                                    {{-- Arrow --}}
                                    <div class="hidden items-center justify-center sm:flex">

                                        <span class="text-2xl text-gray-400">
                                            →
                                        </span>

                                    </div>


                                    {{-- To --}}
                                    <div>

                                        <p class="text-xs font-medium uppercase
                                              tracking-wide text-gray-500">
                                            To
                                        </p>

                                        <div class="mt-2">

                                            <p class="font-semibold text-gray-900">
                                                {{ $movement->toLocation?->name ?? '—' }}
                                            </p>

                                            @if ($movement->toLocation?->location_code)

                                                <p class="mt-1 font-mono text-xs text-gray-500">
                                                    {{ $movement->toLocation->location_code }}
                                                </p>

                                            @endif

                                        </div>

                                    </div>

                                </div>


                                {{-- Notes --}}
                                @if ($movement->notes)

                                    <div class="mt-5 rounded-lg bg-gray-50 p-4">

                                        <p class="text-xs font-medium uppercase
                                                  tracking-wide text-gray-500">
                                            Notes
                                        </p>

                                        <p class="mt-1 text-sm text-gray-700">
                                            {{ $movement->notes }}
                                        </p>

                                    </div>

                                @endif


                                {{-- Recorded By --}}
                                <div class="mt-5 border-t border-gray-100 pt-4">

                                    <p class="text-xs text-gray-500">

                                        Recorded by

                                        <span class="font-medium text-gray-700">
                                            {{ $movement->recordedBy?->name ?? 'System' }}
                                        </span>

                                    </p>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="mt-6 rounded-xl border border-dashed
                        border-gray-300 bg-gray-50 p-8 text-center">

                        <p class="font-medium text-gray-700">
                            No movement history
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            No location movements have been recorded for this swine.
                        </p>

                        <a href="{{ route('swine.movements.create', $swine) }}" class="mt-4 inline-flex rounded-lg bg-indigo-600
                           px-4 py-2 text-sm font-semibold text-white
                           hover:bg-indigo-700">
                            Record First Movement
                        </a>

                    </div>

                @endif

            </div>

            <div class="border-t border-gray-200 px-6 py-6">

                <h4 class="text-base font-semibold text-gray-900">
                    QR Code
                </h4>

                <div class="mt-5 flex flex-col items-center">

                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                        {!! $qrCode !!}
                    </div>

                    <p class="mt-3 text-sm text-gray-500">
                        Scan this QR code to view the swine information.
                    </p>

                    <p class="mt-1 font-mono text-xs text-gray-400">
                        {{ $swine->qr_token }}
                    </p>

                </div>


            </div>
        </div>

    </div>
    </div>

</x-app-layout>