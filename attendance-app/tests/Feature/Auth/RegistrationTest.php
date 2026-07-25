<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'role_type' => 'kepala_sekolah',
            'tenant_name' => 'Sekolah Test',
            'npsn' => '20261001',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('register.verify-otp'));
    }

    public function test_staff_users_can_register_with_npsn(): void
    {
        $tenant = \App\Models\Tenant::create([
            'name' => 'Sekolah Test',
            'code' => '20261001',
            'slug' => 'sekolah-test',
            'status' => 'active'
        ]);

        $response = $this->post('/register', [
            'role_type' => 'teacher',
            'tenant_code' => '20261001',
            'name' => 'Test Teacher',
            'email' => 'teacher@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('register.verify-otp'));
    }
}
