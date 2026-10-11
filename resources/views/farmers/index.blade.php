<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Farmer Management</h2>
                <p class="mt-1 text-sm text-gray-500">Manage farmer profiles and their registered swine.</p>
            </div>
            <a href="{{ route('farmers.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                + Register Farmer
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="GET" action="{{ route('farmers.index') }}" class="mb-6 flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:flex-row">
                <label for="search" class="sr-only">Search farmers</label>
                <input id="search" name="search" value="{{ request('search') }}" placeholder="Search name, phone, or email" class="w-full rounded-lg border-gray-300 shadow-sm sm:max-w-md">
                <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-900">Search</button>
                @if (request()->filled('search'))
                    <a href="{{ route('farmers.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700">Clear</a>
                @endif
            </form>

            <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Farmer</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Farm</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Registered Swine</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($farmers as $farmer)
                            <tr>
                                <td class="px-6 py-4">
                                    <a href="{{ route('farmers.show', $farmer) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $farmer->name }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $farmer->farm->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $farmer->contact_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $farmer->swine_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">No farmers registered yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $farmers->links() }}</div>
        </div>
    </div>
</x-app-layout>
