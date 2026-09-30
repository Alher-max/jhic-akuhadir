<?php

namespace App\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApkDownloadController extends Controller
{
    private const APK_MAP = [
        'siswa' => [
            'path' => 'hadiryuk-siswa-1-2.apk',
            'filename' => 'HadirYuk-Siswa-v1.2.apk',
        ],
        'orangtua' => [
            'path' => 'hadiryuk-ortu-1-1.apk',
            'filename' => 'HadirYuk-OrangTua-v1.1.apk',
        ],
    ];

    public function __invoke(string $role): BinaryFileResponse|Response
    {
        if (! array_key_exists($role, self::APK_MAP)) {
            abort(404, 'File APK tidak tersedia untuk role yang diminta.');
        }

        $file = self::APK_MAP[$role];
        $fullPath = public_path($file['path']);

        if (! (new Filesystem())->exists($fullPath)) {
            abort(404, 'File APK yang Anda cari belum tersedia saat ini.');
        }

        return response()->download($fullPath, $file['filename'], [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
