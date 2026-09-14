<x-app-layout>

```
<x-slot name="header">

    <div>

        <h2 class="font-semibold text-xl text-gray-800">
            Register Farm Location
        </h2>

        <p class="text-sm text-gray-500 mt-1">
            {{ $farm->name }}
            ·
            {{ $farm->farm_code }}
        </p>

    </div>

</x-slot>

<div class="py-8">

    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white shadow-sm sm:rounded-lg">

            <div class="p-6">

                <div class="mb-6">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Location Information
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Register a new area, pen, or housing location
                        within this farm.
                    </p>

                </div>

                <form
                    method="POST"
                    action="{{ route('farms.locations.store', $farm) }}"
                >

                    @csrf

                    @include('farm-locations._form')

                    {{-- Status Information --}}
                    <div class="mt-8 rounded-lg bg-blue-50 border border-blue-200 p-4">

                        <p class="font-semibold text-blue-800">
                            Status Information
                        </p>

                        <p class="text-sm text-blue-700 mt-1">
                            Newly registered farm locations are
                            automatically set to
                            <strong>Active</strong>.
                            You can deactivate or activate the location
                            later from the Farm Locations page.
                        </p>

                    </div>

                    <div class="mt-8 flex items-center justify-end gap-3">

                        <a
                            href="{{ route('farms.locations.index', $farm) }}"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-semibold hover:bg-gray-700"
                        >
                            Register Location
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>
```

</x-app-layout>
