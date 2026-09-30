<?php

namespace App\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApkDownloadController extends Controller
{
    private const APK_MAP = [
        'siswa' => [
            'relative_path' => 'apk/akuhadir-siswa.apk',
            'fallback_path' => 'akuhadir-siswa.apk',
            'filename' => 'akuhadir-siswa.apk',
        ],
        'orangtua' => [
            'relative_path' => 'apk/akuhadir-ortu.apk',
            'fallback_path' => 'akuhadir-ortu.apk',
            'filename' => 'akuhadir-ortu.apk',
        ],
    ];

    public function __invoke(string $role): BinaryFileResponse|Response
    {
        if (! array_key_exists($role, self::APK_MAP)) {
            abort(404, 'File APK tidak tersedia untuk role yang diminta.');
        }

        $file = self::APK_MAP[$role];
        $filesystem = new Filesystem();

        $candidates = [
            storage_path('app/public/' . $file['relative_path']),
            public_path('storage/' . $file['relative_path']),
            public_path($file['relative_path']),
            public_path($file['fallback_path']),
        ];

        $fullPath = null;
        foreach ($candidates as $candidate) {
            if ($filesystem->exists($candidate)) {
                $fullPath = $candidate;
                break;
            }
        }

        if (! $fullPath) {
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
