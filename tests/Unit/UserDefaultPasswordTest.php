<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Student;
use Tests\TestCase;

class UserDefaultPasswordTest extends TestCase
{
    public function test_get_default_password_for_teacher(): void
    {
        $user = new User([
            'role' => 'teacher',
            'nisn' => '198501012010011001',
        ]);
        
        $this->assertEquals('198501012010011001', $user->getDefaultPassword());
    }

    public function test_get_default_password_for_student(): void
    {
        $student = new Student([
            'role' => 'student',
            'nisn' => '1029384756',
            'nis' => '2024001',
        ]);

        $this->assertEquals('1029384756', $student->getDefaultPassword());
    }

    public function test_get_default_password_for_parent(): void
    {
        $parent = new User([
            'role' => 'parent',
            'parent_phone' => '081234567890',
            'nik' => '3201012345670001',
        ]);

        $this->assertEquals('081234567890', $parent->getDefaultPassword());
    }

    public function test_get_default_password_fallback(): void
    {
        $user = new User([
            'role' => 'operator',
        ]);

        $this->assertEquals('12345678', $user->getDefaultPassword());
    }
}
