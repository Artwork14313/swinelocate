<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Swine Information
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                QR Code Identification
            </p>
        </div>
    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                {{-- Header --}}
                <div class="border-b border-gray-200 px-6 py-6 text-center">

                    <p class="text-sm font-medium text-gray-500">
                        SwineLocate Identification
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


                {{-- Status --}}
                <div class="px-6 py-6">

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Tag Number
                            </p>

                            <p class="mt-1 font-semibold text-gray-900">
                                {{ $swine->tag_number }}
                            </p>
                        </div>


                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Status
                            </p>

                            <p class="mt-1 font-semibold text-gray-900">
                                {{ ucfirst($swine->status) }}
                            </p>
                        </div>


                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Sex
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ ucfirst($swine->sex) }}
                            </p>
                        </div>


                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Breed
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $swine->breed ?: '—' }}
                            </p>
                        </div>


                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Farm
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $swine->farm?->name ?? '—' }}
                            </p>
                        </div>


                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Current Location
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $swine->currentLocation?->name ?? '—' }}
                            </p>
                        </div>


                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Birth Date
                            </p>

                            <p class="mt-1 text-gray-900">
                                {{ $swine->birth_date?->format('F d, Y') ?? '—' }}
                            </p>
                        </div>


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


                {{-- QR Token --}}
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-5 text-center">

                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        QR Identification Token
                    </p>

                    <p class="mt-1 font-mono text-sm text-gray-700">
                        {{ $swine->qr_token }}
                    </p>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>