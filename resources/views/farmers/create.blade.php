<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">Register Farmer</h2>
        <p class="mt-1 text-sm text-gray-500">Add the farmer's contact details and associated farm.</p>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form method="POST" action="{{ route('farmers.store') }}">
                    @csrf
                    @include('farmers._form')
                    <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-5">
                        <a href="{{ route('farmers.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Cancel</a>
                        <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Register Farmer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
