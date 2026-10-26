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

            <section id="farmer-qr-print" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-gray-900">Public Farmer QR Code</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Buyers can scan this code to view animals the farmer has listed for sale.
                    </p>
                    <div class="mx-auto mt-5 w-fit rounded-xl border border-gray-200 bg-white p-4">
                        {!! $qrCode !!}
                    </div>
                    <div class="no-print mt-5 flex flex-wrap justify-center gap-3">
                        <button type="button" onclick="window.print()" class="rounded-lg bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-900">
                            Print Farmer QR
                        </button>
                        <a href="{{ route('farmers.public-profile', ['qr_token' => $farmer->qr_token]) }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Preview Public Page
                        </a>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Sold Swine</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $soldSales->count() }} swine sold</p>
                    </div>
                    <div class="rounded-lg bg-green-50 px-4 py-2 text-right ring-1 ring-green-200">
                        <p class="text-xs font-semibold uppercase text-green-700">Total Income</p>
                        <p class="text-xl font-bold text-green-800">₱{{ number_format($totalIncome, 2) }}</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tag Number</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Date Sold</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Buyer</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Sale Price</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($soldSales as $sale)
                                <tr>
                                    <td class="px-6 py-4">
                                        @if ($sale->swine && ! $sale->swine->trashed())
                                            <a href="{{ route('swine.show', $sale->swine) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $sale->swine->tag_number }}</a>
                                        @else
                                            <span class="text-sm font-semibold text-gray-700">{{ $sale->swine?->tag_number ?? '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $sale->sold_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $sale->buyer_name }}<span class="block text-xs text-gray-500">{{ $sale->buyer_contact }}</span></td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900">₱{{ number_format((float) $sale->sale_price, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">No sold swine recorded for this farmer yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $swine->currentLocation->name_with_address ?? 'Unassigned' }}</td>
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
    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            #farmer-qr-print,
            #farmer-qr-print * {
                visibility: visible !important;
            }

            #farmer-qr-print {
                position: absolute;
                inset: 0;
                width: 100%;
                box-shadow: none;
            }

            #farmer-qr-print .no-print {
                display: none !important;
            }
        }
    </style>
</x-app-layout>
