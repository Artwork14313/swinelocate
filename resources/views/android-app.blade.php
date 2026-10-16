<x-guest-layout :show-navigation="false">
    <div class="min-h-screen bg-gray-100 px-4 py-10 sm:px-6 lg:px-8">
        <main class="mx-auto max-w-xl space-y-6">
            <header class="rounded-xl bg-white p-6 text-center shadow-sm ring-1 ring-gray-200">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">SwineLocate for Android</p>
                <h1 class="mt-2 text-3xl font-bold text-gray-900">Get the mobile app</h1>
                <p class="mt-2 text-gray-600">Scan this QR code on your Android phone to open the app download page.</p>
                <div class="mx-auto mt-6 w-fit rounded-xl border border-gray-200 bg-white p-4">
                    {!! $qrCode !!}
                </div>
                @if ($downloadAvailable)
                    <a href="{{ route('android-app.download') }}"
                        class="mt-6 inline-flex w-full items-center justify-center rounded-lg bg-indigo-700 px-5 py-3 font-semibold text-white hover:bg-indigo-800">
                        Download SwineLocate APK
                    </a>
                    <p class="mt-3 text-left text-sm text-gray-500">
                        After downloading, open the APK and allow installation from this source if Android asks. Only install APKs from SwineLocate.
                    </p>
                @else
                    <p class="mt-5 rounded-lg bg-amber-50 p-4 text-left text-sm text-amber-900">
                        The Android APK has not been published to this server yet. Build a signed release APK and place it at
                        <code class="font-semibold">public/downloads/swinelocate.apk</code>, then deploy the application.
                    </p>
                @endif
                <a href="{{ route('farmers.nearby') }}" class="mt-5 inline-flex text-sm font-semibold text-indigo-700 hover:text-indigo-900">
                    Back to farmers near Sagay City
                </a>
            </header>
        </main>
    </div>
</x-guest-layout>
