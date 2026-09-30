<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApkDownloadTest extends TestCase
{
    public function test_student_apk_download_returns_android_package_archive_response(): void
    {
        $response = $this->get('/download/apk/siswa');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.android.package-archive')
            ->assertDownload('HadirYuk-Siswa-v1.2.apk');
    }

    public function test_parent_apk_download_returns_android_package_archive_response(): void
    {
        $response = $this->get('/download/apk/orangtua');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.android.package-archive')
            ->assertDownload('HadirYuk-OrangTua-v1.1.apk');
    }

    public function test_invalid_apk_role_returns_not_found(): void
    {
        $this->get('/download/apk/guru')
            ->assertNotFound();
    }
}
