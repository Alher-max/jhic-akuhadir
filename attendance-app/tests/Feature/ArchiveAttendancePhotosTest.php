<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveAttendancePhotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_photos_command_creates_zip_and_deletes_old_photos(): void
    {
        Storage::fake('public');

        $tenant = Tenant::create([
            'name' => 'SMA Negeri 2 Yogyakarta',
            'slug' => 'sman2yogyakarta',
            'code' => 'SMAN2YOGYA',
            'subdomain' => 'sman2yogyakarta',
            'onboarding_completed' => true,
        ]);

        $student = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siswa Old Photo',
            'email' => 'oldphoto@sman2yogyakarta.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        // Create old photo file in public storage
        $photoPath = 'attendances/test_old_photo.jpg';
        Storage::disk('public')->put($photoPath, 'fake_image_content');

        // Create attendance record 100 days ago
        $attendance = Attendance::create([
            'tenant_id' => $tenant->id,
            'user_id' => $student->id,
            'date' => Carbon::now()->subDays(100)->format('Y-m-d'),
            'clock_in' => Carbon::now()->subDays(100)->setTime(7, 0),
            'status' => 'present',
            'photo_path' => $photoPath,
        ]);

        // Assert file exists before command
        Storage::disk('public')->assertExists($photoPath);

        // Run artisan command with --days=90 --delete
        $this->artisan('attendance:archive-photos --days=90 --delete')
            ->assertExitCode(0);

        // Assert photo path in DB is now null
        $this->assertNull($attendance->fresh()->photo_path);

        // Assert physical file in storage is deleted
        Storage::disk('public')->assertMissing($photoPath);
    }
}
