<x-app-layout>

<x-slot name="header">

    <div>
        <h2 class="text-2xl font-bold text-gray-900">
            Scan QR Code
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Scan a swine QR code or a farmer QR code to open its listing.
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
                    Position a SwineLocate swine or farmer QR code inside the scanner.
                </p>

            </div>


            {{-- Scanner Area --}}
            <div class="px-6 py-8">

                <div id="qr-reader"
                    class="mx-auto w-full max-w-md overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                </div>

            </div>


            {{-- Status --}}
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">

                <p id="scanner-status"
                    class="text-center text-sm font-medium text-gray-500"
                    aria-live="polite">
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

        const status = document.getElementById('scanner-status');
        const reader = document.getElementById('qr-reader');

        if (!status || !reader) {
            return;
        }

        const scanner = new Html5Qrcode('qr-reader');

        let scanHandled = false;


        function getSwineLocateQrType(decodedText) {

            try {
                const url = new URL(decodedText);

                if (url.origin !== window.location.origin) {
                    return null;
                }

                const match = url.pathname.match(
                    /^\/(qr\/scan|swine\/scan|public\/farmers)\/([A-Za-z0-9_-]+)\/?$/
                );

                if (!match) {
                    return null;
                }

                return match[1] === 'public/farmers' ? 'farmer' : 'swine';
            } catch {
                return null;
            }
        }


        /*
         * Display scanner status.
         */
        function setStatus(message, type) {

            status.textContent = message;

            status.classList.remove(
                'text-gray-500',
                'text-green-600',
                'text-red-600',
                'text-yellow-600'
            );

            if (type === 'success') {

                status.classList.add('text-green-600');

            } else if (type === 'error') {

                status.classList.add('text-red-600');

            } else if (type === 'warning') {

                status.classList.add('text-yellow-600');

            } else {

                status.classList.add('text-gray-500');
            }
        }


        /*
         * Handle successful QR scan.
         */
        function onScanSuccess(decodedText) {

            // Prevent multiple redirects from repeated camera detections.
            if (scanHandled) {
                return;
            }

            const qrType = getSwineLocateQrType(decodedText);

            if (!qrType) {
                setStatus(
                    'Invalid QR code. Please scan a SwineLocate swine or farmer QR code.',
                    'error'
                );

                return;
            }

            scanHandled = true;

            setStatus(
                qrType === 'farmer'
                    ? 'Farmer QR code detected. Opening public farmer profile...'
                    : 'Swine QR code detected. Opening traceability record...',
                'success'
            );


            /*
             * Stop the camera before navigating away.
             */
            scanner.stop()
                .then(function () {

                    window.location.assign(decodedText);

                })
                .catch(function (error) {

                    console.error('Unable to stop QR scanner:', error);

                    // Still navigate if the camera cannot be stopped.
                    window.location.assign(decodedText);

                });
        }


        /*
         * Normal scanning failures are ignored because
         * html5-qrcode continuously reports unsuccessful frames.
         */
        function onScanFailure(errorMessage) {
            // Intentionally ignored.
        }


        /*
         * Start camera.
         */
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

                setStatus(
                    'Camera ready. Scan a SwineLocate swine or farmer QR code.',
                    'default'
                );

            })
            .catch(function (error) {

                console.error('QR scanner camera error:', error);

                setStatus(
                    'Unable to access the camera. Please allow camera permission.',
                    'error'
                );

            });

    });
</script>

</x-app-layout>
