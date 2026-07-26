<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_headmaster_school_a_cannot_access_data_from_school_b()
    {
        // 1. Create School A & Headmaster A
        $schoolA = Tenant::create([
            'name' => 'SMA Negeri A',
            'npsn' => '10000001',
            'code' => '10000001',
            'slug' => 'sma-negeri-a',
            'subdomain' => 'smanegeria',
        ]);

        $headmasterA = User::create([
            'name' => 'Kepala Sekolah A',
            'email' => 'kepala@sekolaha.sch.id',
            'password' => bcrypt('password'),
            'role' => 'headmaster',
            'tenant_id' => $schoolA->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 2. Create School B, Headmaster B, Operator B, Student B, Invitation B
        $schoolB = Tenant::create([
            'name' => 'SMA Negeri B',
            'npsn' => '20000002',
            'code' => '20000002',
            'slug' => 'sma-negeri-b',
            'subdomain' => 'smanegerib',
        ]);

        $headmasterB = User::create([
            'name' => 'Kepala Sekolah B',
            'email' => 'kepala@sekolahb.sch.id',
            'password' => bcrypt('password'),
            'role' => 'headmaster',
            'tenant_id' => $schoolB->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $operatorB = User::create([
            'name' => 'Operator Sekolah B',
            'email' => 'operator@sekolahb.sch.id',
            'password' => bcrypt('password'),
            'role' => 'operator',
            'tenant_id' => $schoolB->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $studentB = Student::create([
            'name' => 'Siswa Sekolah B',
            'email' => 'siswa@sekolahb.sch.id',
            'password' => bcrypt('password'),
            'role' => 'student',
            'tenant_id' => $schoolB->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $invitationB = Invitation::create([
            'tenant_id' => $schoolB->id,
            'email' => 'calon.operator@sekolahb.sch.id',
            'role' => 'operator',
            'token' => 'token-sekolah-b-12345',
            'status' => 'pending',
        ]);

        // 3. Act as Headmaster A
        $this->actingAs($headmasterA);

        // Assert Eloquent queries automatically filter out School B data
        $users = User::all();
        $this->assertFalse($users->contains('id', $headmasterB->id));
        $this->assertFalse($users->contains('id', $operatorB->id));
        $this->assertFalse($users->contains('id', $studentB->id));
        $this->assertTrue($users->contains('id', $headmasterA->id));

        $students = Student::all();
        $this->assertFalse($students->contains('id', $studentB->id));

        $invitations = Invitation::all();
        $this->assertFalse($invitations->contains('id', $invitationB->id));

        // Attempting to query School B models directly fails to retrieve them
        $foundOperatorB = User::find($operatorB->id);
        $this->assertNull($foundOperatorB);

        $foundStudentB = Student::find($studentB->id);
        $this->assertNull($foundStudentB);

        $foundInvitationB = Invitation::find($invitationB->id);
        $this->assertNull($foundInvitationB);
    }
}
