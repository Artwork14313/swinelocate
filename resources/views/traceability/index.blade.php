<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Swine Traceability</h2>
                <p class="mt-1 text-sm text-gray-500">Find a swine record by tag number or name, or scan its QR code.</p>
            </div>
            <a href="{{ route('qr.scanner') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Scan QR Code</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('traceability.index') }}" class="mb-6 flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:flex-row">
                <label for="search" class="sr-only">Search swine</label>
                <input id="search" name="search" value="{{ request('search') }}" placeholder="Tag number, swine name, or farmer" class="w-full rounded-lg border-gray-300 shadow-sm sm:max-w-md">
                <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-900">Search</button>
                @if (request()->filled('search'))
                    <a href="{{ route('traceability.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700">Clear</a>
                @endif
            </form>

            <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tag Number</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Farmer</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Farm</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Current Location</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Record</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($swine as $pig)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $pig->tag_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $pig->farmer->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $pig->farm->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $pig->currentLocation->name_with_address ?? 'Unassigned' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ ucfirst($pig->status) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('swine.scan', ['qr_token' => $pig->qr_token]) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">View Traceability</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">No swine records match this search.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $swine->links() }}</div>
        </div>
    </div>
</x-app-layout>
