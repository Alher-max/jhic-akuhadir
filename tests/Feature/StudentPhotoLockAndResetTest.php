<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentPhotoLockAndResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_with_photo_cannot_upload_second_time(): void
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
            'name' => 'Siswa Test Lock',
            'email' => 'siswalock@sman2yogyakarta.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'student',
            'avatar' => 'avatars/existing_photo.jpg',
            'master_photo' => 'avatars/existing_photo.jpg',
            'is_active' => true,
        ]);

        $newPhoto = UploadedFile::fake()->image('second_photo.jpg');

        $response = $this->from(route('profile.edit'))
            ->actingAs($student)
            ->patch(route('profile.update'), [
                'name' => $student->name,
                'email' => $student->email,
                'avatar' => $newPhoto,
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error', 'Foto profil telah dikunci & terverifikasi. Hubungi Wali Kelas/Operator jika perlu mengubah foto.');
        
        // Assert old photo path is untouched
        $this->assertEquals('avatars/existing_photo.jpg', $student->fresh()->avatar);
    }

    public function test_teacher_can_reset_student_photo(): void
    {
        Storage::fake('public');

        $tenant = Tenant::create([
            'name' => 'SMA Negeri 2 Yogyakarta',
            'slug' => 'sman2yogyakarta',
            'code' => 'SMAN2YOGYA',
            'subdomain' => 'sman2yogyakarta',
            'onboarding_completed' => true,
        ]);

        $teacher = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Guru Wali Kelas',
            'email' => 'guruwali@sman2yogyakarta.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $student = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siswa Reset Test',
            'email' => 'siswareset@sman2yogyakarta.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'student',
            'avatar' => 'avatars/student_photo.jpg',
            'master_photo' => 'avatars/student_photo.jpg',
            'is_active' => true,
        ]);

        $response = $this->from(route('teacher.dashboard'))
            ->actingAs($teacher)
            ->post(route('teacher.students.reset-photo', $student->id));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('success');

        // Assert student photo is reset to null
        $this->assertNull($student->fresh()->avatar);
        $this->assertNull($student->fresh()->master_photo);
    }
}
