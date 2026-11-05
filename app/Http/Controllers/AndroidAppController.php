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
        $downloadUrl = $downloadAvailable
            ? route('android-app.download', ['v' => filemtime($downloadPath)])
            : null;
        $qrTarget = $downloadAvailable
            ? $downloadUrl
            : route('android-app');

        return view('android-app', [
            'qrCode' => QrCode::size(240)->generate($qrTarget),
            'downloadAvailable' => $downloadAvailable,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    public function download(): BinaryFileResponse
    {
        $path = public_path('downloads/swinelocate.apk');

        abort_unless(is_file($path), 404, 'The Android app download is not available yet.');

        return response()->download($path, 'SwineLocate.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
