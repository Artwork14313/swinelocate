<x-app-layout>

```
<x-slot name="header">

    <div>

        <h2 class="font-semibold text-xl text-gray-800">
            Edit Farm
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
                        Farm Information
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Update the details of this farm.
                        Farm status is managed separately through the
                        Activate and Deactivate actions.
                    </p>

                </div>


                <form
                    method="POST"
                    action="{{ route('farms.update', $farm) }}"
                >

                    @csrf
                    @method('PUT')


                    {{-- Farm Fields --}}
                    @include('farms._form')


                    {{-- Current Status --}}
                    <div class="mt-8">

                        <x-input-label
                            value="Current Status"
                        />

                        <div class="mt-2">

                            @if($farm->status === 'active')

                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-700">
                                    Active
                                </span>

                            @else

                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-700">
                                    Inactive
                                </span>

                            @endif

                        </div>

                        <p class="mt-2 text-xs text-gray-500">
                            To change the farm status, use the Activate or
                            Deactivate action from the farm details or
                            farm list.
                        </p>

                    </div>


                    {{-- Actions --}}
                    <div class="mt-8 flex items-center justify-end gap-3">

                        <a
                            href="{{ route('farms.show', $farm) }}"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-semibold hover:bg-gray-700"
                        >
                            Save Changes
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>
```

</x-app-layout>
