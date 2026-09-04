<x-app-layout>

    <x-slot name="header">

        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Sync Status
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Monitor offline records waiting to synchronize with the server.
            </p>
        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">


            {{-- Connection Status --}}
            <div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Connection Status
                        </p>

                        <div class="mt-2 flex items-center gap-3">

                            <span
                                id="connection-indicator"
                                class="h-3 w-3 rounded-full bg-gray-400">
                            </span>

                            <span
                                id="connection-status"
                                class="text-lg font-semibold text-gray-900">
                                Checking...
                            </span>

                        </div>

                    </div>


                    {{-- Sync Button --}}
                    <button
                        type="button"
                        id="sync-now-button"
                        class="inline-flex items-center justify-center rounded-lg
                               bg-[#3368A0] px-5 py-2.5 text-sm font-semibold
                               text-white shadow-sm hover:bg-[#28557F]">

                        Sync Now

                    </button>

                </div>

            </div>


            {{-- Summary --}}
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">

                {{-- Pending --}}
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">

                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Pending Synchronization
                    </p>

                    <p
                        id="pending-count"
                        class="mt-2 text-3xl font-bold text-gray-900">
                        0
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        Records waiting to be uploaded.
                    </p>

                </div>


                {{-- Conflicts --}}
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">

                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Synchronization Conflicts
                    </p>

                    <p
                        id="conflict-count"
                        class="mt-2 text-3xl font-bold text-red-600">
                        0
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        Records requiring review.
                    </p>

                </div>


                {{-- Last Sync --}}
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">

                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Last Synchronization
                    </p>

                    <p
                        id="last-sync"
                        class="mt-2 text-lg font-semibold text-gray-900">
                        Not yet synchronized
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        Latest successful synchronization activity.
                    </p>

                </div>

            </div>


            {{-- Synchronization Records --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Synchronization Records
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Offline records waiting for synchronization or requiring review.
                    </p>

                </div>


                {{-- IMPORTANT:
                     Both pending records and conflicts
                     will be rendered here.
                --}}
                <div id="pending-records">

                    <div class="px-6 py-10 text-center">

                        <p class="text-sm text-gray-500">
                            Loading synchronization records...
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>


    @vite('resources/js/sync-status.js')

</x-app-layout>