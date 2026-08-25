<x-app-layout>

    <x-slot name="header">

        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Health History
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Select a swine to view its complete health history.
            </p>
        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Swine Health History
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Choose a swine to view all recorded health events.
                    </p>

                </div>


                @if ($swine->isEmpty())

                    <div class="px-6 py-12 text-center">

                        <p class="text-sm text-gray-500">
                            No swine records available.
                        </p>

                    </div>

                @else

                    <div class="divide-y divide-gray-200">

                        @foreach ($swine as $animal)

                            <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                                <div>

                                    <div class="flex items-center gap-3">

                                        <h4 class="font-semibold text-gray-900">
                                            {{ $animal->tag_number }}
                                        </h4>

                                        @if ($animal->name)

                                            <span class="text-sm text-gray-500">
                                                {{ $animal->name }}
                                            </span>

                                        @endif

                                    </div>


                                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-500">

                                        <span>
                                            Farm:
                                            {{ $animal->farm?->name ?? '—' }}
                                        </span>

                                        <span>
                                            Status:
                                            {{ ucfirst($animal->status) }}
                                        </span>

                                    </div>

                                </div>


                                <a
                                    href="{{ route('health-records.history', $animal) }}"
                                    class="inline-flex items-center justify-center rounded-lg
                                           bg-indigo-600 px-4 py-2 text-sm font-semibold
                                           text-white hover:bg-indigo-700"
                                >
                                    View Health History
                                </a>

                            </div>

                        @endforeach

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>