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


            {{-- Error Message --}}
            @if (session('error'))

                <div class="mb-6 rounded-lg border border-red-200
                                        bg-red-50 px-4 py-3 text-sm text-red-700">

                    {{ session('error') }}

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

                        {{-- Tag Number --}}
                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Tag Number
                            </dt>

                            <dd class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $swine->tag_number }}
                            </dd>

                        </div>


                        {{-- Sex --}}
                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Sex
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ ucfirst($swine->sex) }}
                            </dd>

                        </div>


                        {{-- Breed --}}
                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Breed
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $swine->breed ?: '—' }}
                            </dd>

                        </div>


                        {{-- QR Token --}}
                        <!-- <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                QR Token
                            </dt>

                            <dd class="mt-1 break-all text-sm font-medium text-gray-900">
                                {{ $swine->qr_token ?: '—' }}
                            </dd>

                        </div> -->

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
                                    {{ $swine->currentLocation->name_with_address }}
                                </dd>

                                @if ($swine->currentLocation->location_code)

                                    <dd class="text-xs text-gray-500">
                                        {{ $swine->currentLocation->location_code }}
                                    </dd>

                                @endif


                                {{-- Verify location belongs to current farm --}}
                                @if ($swine->farm && $swine->currentLocation->farm_id === $swine->farm->id)

                                    <dd class="mt-1 text-xs text-green-600">
                                        Location belongs to the assigned farm.
                                    </dd>

                                @else

                                    <dd class="mt-1 text-xs text-red-600">
                                        Location does not belong to the assigned farm.
                                    </dd>

                                @endif

                            @else

                                <dd class="mt-1 text-sm text-gray-400">
                                    No location assigned
                                </dd>

                                <dd class="mt-1 text-xs text-gray-500">
                                    This swine does not currently have a location.
                                </dd>

                            @endif

                        </div>

                        {{-- Farmer --}}
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Farmer
                            </dt>
                            @if ($swine->farmer)
                                <dd class="mt-1 text-sm font-medium text-gray-900">
                                    <a href="{{ route('farmers.show', $swine->farmer) }}" class="text-indigo-600 hover:text-indigo-800">
                                        {{ $swine->farmer->name }}
                                    </a>
                                </dd>
                                <dd class="mt-1 text-xs text-gray-500">
                                    {{ $swine->farmer->contact_number }}
                                </dd>
                            @else
                                <dd class="mt-1 text-sm text-gray-400">No farmer assigned</dd>
                            @endif
                        </div>

                    </div>


                    {{-- Location Action --}}
                    @if (
                            $swine->status === 'active' &&
                            auth()->user()->hasPermission('manage-movements')
                        )

                        <div class="mt-6">

                            @if ($swine->currentLocation)

                                <a href="{{ route('swine.movements.create', $swine) }}" class="inline-flex items-center justify-center rounded-lg
                                                                   bg-[#3368A0] px-4 py-2 text-sm font-semibold
                                                                   text-white hover:bg-[#28557F]">
                                    Move Swine
                                </a>

                            @else

                                <a href="{{ route('swine.movements.create', $swine) }}" class="inline-flex items-center justify-center rounded-lg
                                                                   bg-[#3368A0] px-4 py-2 text-sm font-semibold
                                                                   text-white hover:bg-[#28557F]">
                                    Assign Location
                                </a>

                            @endif

                        </div>

                    @endif

                </div>


                {{-- Registration Information --}}
                <div class="border-b border-gray-200 px-6 py-6">

                    <h4 class="text-base font-semibold text-gray-900">
                        Registration Information
                    </h4>

                    <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">

                        {{-- Birth Date --}}
                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Birth Date
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $swine->birth_date?->format('F d, Y') ?? '—' }}
                            </dd>

                        </div>


                        {{-- Acquisition Date --}}
                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Acquisition Date
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $swine->acquisition_date?->format('F d, Y') ?? '—' }}
                            </dd>

                        </div>


                        {{-- Source --}}
                        <div>

                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Source
                            </dt>

                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $swine->source ?: '—' }}
                            </dd>

                        </div>


                        {{-- Record Created --}}
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

                    {{-- Lifecycle Action --}}
                    <div>

                        @if (
                                $swine->status === 'active' &&
                                auth()->user()->hasPermission('manage-swine')
                            )

                            <form method="POST" action="{{ route('swine.destroy', $swine) }}"
                                onsubmit="return confirm('Are you sure you want to deactivate this swine?');">

                                @csrf
                                @method('DELETE')

                                <button type="submit" class="w-full rounded-lg border border-red-200
                                                       bg-white px-4 py-2.5 text-sm font-semibold
                                                       text-red-600 hover:bg-red-50 sm:w-auto">
                                    Deactivate Swine
                                </button>

                            </form>

                        @elseif (
                                $swine->status === 'inactive' &&
                                auth()->user()->hasPermission('manage-swine')
                            )

                            <form method="POST" action="{{ route('swine.activate', $swine) }}"
                                onsubmit="return confirm('Are you sure you want to activate this swine?');">

                                @csrf
                                @method('PATCH')

                                <button type="submit" class="w-full rounded-lg border border-green-200
                                                       bg-white px-4 py-2.5 text-sm font-semibold
                                                       text-green-600 hover:bg-green-50 sm:w-auto">
                                    Activate Swine
                                </button>

                            </form>

                        @endif

                    </div>


                    {{-- Edit --}}
                    @if (auth()->user()->hasPermission('manage-swine'))

                        <div class="flex flex-col gap-3 sm:flex-row">

                            <a href="{{ route('swine.edit', $swine) }}" class="rounded-lg bg-indigo-600 px-5 py-2.5
                                                   text-center text-sm font-semibold text-white
                                                   hover:bg-indigo-700">
                                Edit Swine
                            </a>

                        </div>

                    @endif

                </div>


            </div>


            {{-- QR Code --}}
            <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-6 text-center">
                    <h4 class="text-lg font-semibold text-gray-900">
                        QR Code Identification
                    </h4>

                    <p class="mt-1 text-sm text-gray-500">
                        Scan this QR code to access the swine traceability record.
                    </p>
                </div>

                {{-- Printable QR Area --}}
                <div id="printable-qr" class="px-6 py-8">

                    <div class="flex flex-col items-center">

                        {{-- Title shown when printed --}}
                        <div class="print-only mb-4 text-center">
                            <h2 class="text-xl font-bold text-gray-900">
                                SwineLocate
                            </h2>

                            <p class="text-sm text-gray-600">
                                Swine QR Code Identification
                            </p>

                            <p class="mt-2 text-sm font-medium text-gray-800">
                                Tag Number: {{ $swine->tag_number }}
                            </p>
                        </div>

                        {{-- QR --}}
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            {!! $qrCode !!}
                        </div>

                        <div class="mt-5 flex items-center justify-center gap-3">

                            {{-- Traceability Button --}}
                            @if (auth()->user()->hasPermission('scan-qr'))

                                            <a href="{{ route('swine.scan', ['qr_token' => $swine->qr_token]) }}" target="_blank"
                                                rel="noopener noreferrer" class="inline-flex items-center justify-center
                                   rounded-lg bg-indigo-600 px-5 py-2.5
                                   text-sm font-semibold text-white
                                   hover:bg-indigo-700">
                                                View Traceability
                                            </a>

                            @endif

                            {{-- Print Button --}}
                            <button type="button" onclick="printQRCode()" class="inline-flex items-center justify-center
               rounded-lg bg-gray-800 px-5 py-2.5
               text-sm font-semibold text-white
               hover:bg-gray-900">
                                Print QR Code
                            </button>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>
    <style>
        .print-only {
            display: none;
        }

        @media print {

            body * {
                visibility: hidden;
            }

            #printable-qr,
            #printable-qr * {
                visibility: visible;
            }

            #printable-qr {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
            }

            .no-print {
                display: none !important;
            }

            .print-only {
                display: block;
            }

            @page {
                size: A4;
                margin: 20mm;
            }
        }
    </style>

    <script>
        function printQRCode() {
            const qr = document.querySelector('#printable-qr');

            if (!qr) {
                alert('QR code area not found.');
                return;
            }

            const printWindow = window.open('', '_blank', 'width=600,height=700');

            printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>SwineLocate QR Code</title>

                <style>
                    @page {
                        size: A4;
                        margin: 15mm;
                    }

                    * {
                        box-sizing: border-box;
                    }

                    body {
                        margin: 0;
                        padding: 0;
                        font-family: Arial, sans-serif;
                        text-align: center;
                    }

                    .print-container {
                        width: 100%;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: flex-start;
                        padding-top: 20px;
                    }

                    h2 {
                        margin: 0 0 5px;
                        font-size: 22px;
                    }

                    p {
                        margin: 4px 0;
                        font-size: 14px;
                    }

                    .qr {
                        margin-top: 20px;
                        padding: 15px;
                        border: 1px solid #ddd;
                        border-radius: 10px;
                        background: white;
                    }

                    .qr svg {
                        display: block;
                        width: 250px;
                        height: 250px;
                    }
                </style>
            </head>

            <body>

                <div class="print-container">

                    <h2>SwineLocate</h2>

                    <p>Swine QR Code Identification</p>

                    <p>
                        <strong>Tag Number:</strong>
                        {{ $swine->tag_number }}
                    </p>

                    <div class="qr">
                        {!! $qrCode !!}
                    </div>

                </div>

            </body>
            </html>
        `);

            printWindow.document.close();

            printWindow.onload = function () {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        }
    </script>
</x-app-layout>