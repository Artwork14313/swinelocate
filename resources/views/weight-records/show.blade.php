<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h2 class="text-2xl font-bold text-gray-900">
                    Weight Record Details
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Details of the recorded swine weight.
                </p>

            </div>

            <div class="flex gap-2">

                <a href="{{ route('weight-records.edit', $weightRecord) }}"
                    class="inline-flex items-center rounded-lg
                           bg-[#3368A0] px-4 py-2 text-sm font-semibold
                           text-white shadow-sm hover:bg-[#28557F]">

                    Edit

                </a>

                <a href="{{ route('weight-records.index') }}"
                    class="inline-flex items-center rounded-lg
                           border border-gray-300 bg-white px-4 py-2
                           text-sm font-semibold text-gray-700
                           shadow-sm hover:bg-gray-50">

                    Back

                </a>

            </div>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">


            {{-- Success Message --}}
            @if (session('success'))

                <div class="rounded-lg border border-green-200
                            bg-green-50 px-4 py-3 text-sm text-green-700">

                    {{ session('success') }}

                </div>

            @endif


            {{-- Swine Information --}}
            <div class="overflow-hidden rounded-xl bg-white
                        shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Swine Information
                    </h3>

                </div>

                <div class="grid grid-cols-1 gap-5 px-6 py-6 sm:grid-cols-2">

                    {{-- Tag Number --}}
                    <div>

                        <p class="text-xs font-medium uppercase
                                  tracking-wide text-gray-500">

                            Tag Number

                        </p>

                        <p class="mt-1 text-lg font-bold text-gray-900">

                            {{ $weightRecord->swine?->tag_number ?? 'Unknown' }}

                        </p>

                    </div>


                    {{-- Breed --}}
                    <div>

                        <p class="text-xs font-medium uppercase
                                  tracking-wide text-gray-500">

                            Breed

                        </p>

                        <p class="mt-1 text-sm text-gray-900">

                            {{ $weightRecord->swine?->breed ?? '—' }}

                        </p>

                    </div>


                    {{-- Sex --}}
                    <div>

                        <p class="text-xs font-medium uppercase
                                  tracking-wide text-gray-500">

                            Sex

                        </p>

                        <p class="mt-1 text-sm text-gray-900">

                            {{ $weightRecord->swine?->sex ?? '—' }}

                        </p>

                    </div>

                </div>

            </div>


            {{-- Weight Information --}}
            <div class="overflow-hidden rounded-xl bg-white
                        shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Weight Information
                    </h3>

                </div>

                <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-3">

                    {{-- Weight --}}
                    <div class="rounded-lg bg-[#C8DFDB] p-5">

                        <p class="text-xs font-medium uppercase
                                  tracking-wide text-gray-600">

                            Recorded Weight

                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900">

                            {{ number_format((float) $weightRecord->weight, 2) }}

                            <span class="text-base font-medium">
                                kg
                            </span>

                        </p>

                    </div>


                    {{-- Date --}}
                    <div class="rounded-lg bg-gray-50 p-5">

                        <p class="text-xs font-medium uppercase
                                  tracking-wide text-gray-500">

                            Record Date

                        </p>

                        <p class="mt-2 text-lg font-bold text-gray-900">

                            {{ $weightRecord->record_date?->format('F d, Y') ?? '—' }}

                        </p>

                        @if ($weightRecord->record_date)

                            <p class="mt-1 text-xs text-gray-500">

                                {{ $weightRecord->record_date->format('l') }}

                            </p>

                        @endif

                    </div>


                    {{-- Recorded By --}}
                    <div class="rounded-lg bg-gray-50 p-5">

                        <p class="text-xs font-medium uppercase
                                  tracking-wide text-gray-500">

                            Recorded By

                        </p>

                        <p class="mt-2 text-sm font-semibold text-gray-900">

                            {{ $weightRecord->recordedBy?->name ?? 'Unknown' }}

                        </p>

                    </div>

                </div>

            </div>


            {{-- Notes --}}
            @if ($weightRecord->notes)

                <div class="overflow-hidden rounded-xl bg-white
                            shadow-sm ring-1 ring-gray-200">

                    <div class="border-b border-gray-200 px-6 py-5">

                        <h3 class="text-lg font-semibold text-gray-900">
                            Notes
                        </h3>

                    </div>

                    <div class="px-6 py-6">

                        <p class="whitespace-pre-line text-sm text-gray-700">

                            {{ $weightRecord->notes }}

                        </p>

                    </div>

                </div>

            @endif


            {{-- Delete --}}
            <div class="flex justify-end">

                <form method="POST"
                    action="{{ route('weight-records.destroy', $weightRecord) }}"
                    onsubmit="return confirm('Are you sure you want to delete this weight record?');">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="rounded-lg border border-red-200
                               bg-white px-4 py-2 text-sm font-semibold
                               text-red-600 hover:bg-red-50">

                        Delete Weight Record

                    </button>

                </form>

            </div>


        </div>

    </div>

</x-app-layout>