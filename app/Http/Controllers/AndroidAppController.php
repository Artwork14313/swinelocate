<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AndroidAppController extends Controller
{
    public function show(): View
    {
        $downloadPath = public_path('downloads/swinelocate.apk');
        $downloadAvailable = is_file($downloadPath);
        $qrTarget = $downloadAvailable
            ? route('android-app.download')
            : route('android-app');

        return view('android-app', [
            'qrCode' => QrCode::size(240)->generate($qrTarget),
            'downloadAvailable' => $downloadAvailable,
        ]);
    }

    public function download(): BinaryFileResponse
    {
        $path = public_path('downloads/swinelocate.apk');

        abort_unless(is_file($path), 404, 'The Android app download is not available yet.');

        return response()->download($path, 'SwineLocate.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
