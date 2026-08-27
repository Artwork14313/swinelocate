<x-app-layout>

    <x-slot name="header">

        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                Scan QR Code
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Scan a swine QR code to view its identification and traceability records.
            </p>
        </div>

    </x-slot>


    <div class="py-8">

        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">

                {{-- Header --}}
                <div class="border-b border-gray-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-gray-900">
                        QR Code Scanner
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Position the swine QR code inside the scanner.
                    </p>

                </div>


                {{-- Scanner Area --}}
                <div class="px-6 py-8">

                    <div id="qr-reader"
                        class="mx-auto w-full max-w-md overflow-hidden rounded-xl border border-gray-200">
                    </div>

                </div>


                {{-- Status --}}
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">

                    <p id="scanner-status" class="text-center text-sm text-gray-500">
                        Waiting for camera...
                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- QR Scanner Library --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>


    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const status =
                document.getElementById('scanner-status');


            const scanner =
                new Html5Qrcode('qr-reader');


            function onScanSuccess(decodedText) {

                status.textContent =
                    'QR code detected. Opening traceability record...';


                scanner.stop()
                    .then(function () {

                        window.location.href = decodedText;

                    })
                    .catch(function (error) {

                        console.error(error);

                        window.location.href = decodedText;

                    });

            }


            function onScanFailure(errorMessage) {

                // Ignore continuous scan failures.
                // The scanner checks the camera repeatedly.

            }


            scanner.start(
                {
                    facingMode: 'environment'
                },
                {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                },
                onScanSuccess,
                onScanFailure
            )
                .then(function () {

                    status.textContent =
                        'Camera ready. Scan the swine QR code.';

                })
                .catch(function (error) {

                    console.error(error);

                    status.textContent =
                        'Unable to access the camera. Please allow camera permission.';

                });

        });

    </script>

</x-app-layout>