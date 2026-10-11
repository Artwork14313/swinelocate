<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $farmer->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">Farmer profile and linked swine records.</p>
            </div>
            <a href="{{ route('farmers.edit', $farmer) }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Edit Farmer</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
            @endif
            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Farmer Information</h3>
                <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div><dt class="text-xs font-semibold uppercase text-gray-500">Farm</dt><dd class="mt-1 text-sm text-gray-900">{{ $farmer->farm->name }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-gray-500">Contact Number</dt><dd class="mt-1 text-sm text-gray-900">{{ $farmer->contact_number }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-gray-500">Email</dt><dd class="mt-1 text-sm text-gray-900">{{ $farmer->email ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-gray-500">Address</dt><dd class="mt-1 text-sm text-gray-900">{{ $farmer->address }}</dd></div>
                </dl>
            </section>

            <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900">Registered Swine</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tag Number</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Current Location</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($farmer->swine as $swine)
                                <tr>
                                    <td class="px-6 py-4"><a href="{{ route('swine.show', $swine) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $swine->tag_number }}</a></td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $swine->currentLocation->name ?? 'Unassigned' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ ucfirst($swine->status) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500">No swine are linked to this farmer yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 px-6 py-4">
                    <a href="{{ route('farmers.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Back to Farmers</a>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
