<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Sale History</h2>
            <p class="mt-1 text-sm text-gray-500">Recorded swine sales and buyer details.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h3 class="font-semibold text-gray-900">Completed sales</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date sold</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Swine</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Buyer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Contact</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Sale price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Farm</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($sales as $sale)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $sale->sold_at->format('M d, Y') }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if ($sale->swine)
                                            <a href="{{ route('swine.show', $sale->swine) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">
                                                {{ $sale->swine->tag_number }}
                                            </a>
                                        @else
                                            <span class="text-sm text-gray-500">Swine record deleted</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $sale->buyer_name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $sale->buyer_contact }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900">₱{{ number_format((float) $sale->sale_price, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $sale->swine?->farm?->name ?? '—' }}</td>
                                </tr>
                                @if ($sale->notes)
                                    <tr class="bg-gray-50/70">
                                        <td colspan="6" class="px-6 py-3 text-sm text-gray-600">
                                            <span class="font-medium text-gray-700">Sale notes:</span> {{ $sale->notes }}
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                        No sales have been recorded yet. Mark an active swine as sold to add the first sale.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($sales->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4">
                        {{ $sales->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
