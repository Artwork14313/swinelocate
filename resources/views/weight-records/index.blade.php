<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h2 class="text-2xl font-bold text-gray-900">
                    Weight Records
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    View and manage recorded swine weights.
                </p>

            </div>

            <a href="{{ route('weight-records.create') }}"
                class="inline-flex items-center justify-center rounded-lg
                       bg-[#3368A0] px-4 py-2 text-sm font-semibold
                       text-white shadow-sm hover:bg-[#28557F]">

                Add Weight Record

            </a>

        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if (session('success'))

                <div class="mb-6 rounded-lg border border-green-200
                            bg-green-50 px-4 py-3 text-sm text-green-700">

                    {{ session('success') }}

                </div>

            @endif


            {{-- Weight Records --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Weight History
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Weight records are displayed from newest to oldest.
                    </p>

                </div>


                @if ($weightRecords->isEmpty())

                    <div class="px-6 py-12 text-center">

                        <p class="text-sm font-medium text-gray-900">
                            No weight records found.
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            Start recording weights to monitor swine growth.
                        </p>

                        <a href="{{ route('weight-records.create') }}"
                            class="mt-4 inline-flex rounded-lg bg-[#3368A0]
                                   px-4 py-2 text-sm font-semibold text-white
                                   hover:bg-[#28557F]">

                            Add First Weight Record

                        </a>

                    </div>

                @else

                    <div class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">

                                <tr>

                                    <th class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500">

                                        Swine

                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500">

                                        Record Date

                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500">

                                        Weight

                                    </th>

                                    <th class="px-6 py-3 text-left text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500">

                                        Recorded By

                                    </th>

                                    <th class="px-6 py-3 text-right text-xs
                                               font-semibold uppercase tracking-wide
                                               text-gray-500">

                                        Action

                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-100 bg-white">

                                @foreach ($weightRecords as $record)

                                    <tr class="hover:bg-gray-50">

                                        {{-- Swine --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <div class="text-sm font-semibold text-gray-900">

                                                {{ $record->swine?->tag_number ?? 'Unknown' }}

                                            </div>

                                            @if ($record->swine?->name)

                                                <div class="text-xs text-gray-500">

                                                    {{ $record->swine->name }}

                                                </div>

                                            @endif

                                        </td>


                                        {{-- Date --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span class="text-sm text-gray-700">

                                                {{ $record->record_date?->format('M d, Y') ?? '—' }}

                                            </span>

                                        </td>


                                        {{-- Weight --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span class="text-sm font-semibold text-gray-900">

                                                {{ number_format((float) $record->weight, 2) }}

                                                kg

                                            </span>

                                        </td>


                                        {{-- Recorded By --}}
                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span class="text-sm text-gray-700">

                                                {{ $record->recordedBy?->name ?? 'Unknown' }}

                                            </span>

                                        </td>


                                        {{-- Action --}}
                                        <td class="whitespace-nowrap px-6 py-4 text-right">

                                            <a href="{{ route('weight-records.show', $record) }}"
                                                class="inline-flex rounded-lg border
                                                       border-gray-300 bg-white px-3 py-2
                                                       text-sm font-medium text-gray-700
                                                       hover:bg-gray-50">

                                                View

                                            </a>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- Pagination --}}
                    @if ($weightRecords->hasPages())

                        <div class="border-t border-gray-200 px-6 py-4">

                            {{ $weightRecords->links() }}

                        </div>

                    @endif

                @endif

            </div>

        </div>

    </div>

</x-app-layout>