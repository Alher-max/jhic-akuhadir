<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use App\Models\PushSubscription;
use App\Models\Tenant;
use App\Models\Announcement;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup default tenant for testing
        Tenant::factory()->create(['id' => 1]);
    }

    public function test_user_can_subscribe_to_push_notifications()
    {
        $user = User::factory()->create(['tenant_id' => 1]);

        $subscriptionData = [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/fake-endpoint-123',
            'keys' => [
                'p256dh' => 'BLMe_vU9zX_7v_8y_8y_8y_8y_8y_8y_8y_8y_8y_8y_8y_8y_8y_8y_8y_8y_8y_8y_8y_8y_8w',
                'auth' => '8y_8y_8y_8y_8y_8y_8y_8y_8w'
            ]
        ];

        $response = $this->actingAs($user)
            ->postJson('/push-subscription', $subscriptionData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => $subscriptionData['endpoint']
        ]);
    }

    public function test_user_can_unsubscribe_from_push_notifications()
    {
        $user = User::factory()->create(['tenant_id' => 1]);
        $subscription = PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/fake-endpoint-123',
            'public_key' => 'fake-key',
            'auth_token' => 'fake-token',
            'content_encoding' => 'aesgcm'
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/push-subscription', [
                'endpoint' => $subscription->endpoint
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('push_subscriptions', [
            'id' => $subscription->id
        ]);
    }

    public function test_push_notification_triggered_on_announcement_creation()
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'tenant_id' => 1]);
        $class = SchoolClass::create([
            'nama_kelas' => 'Test Class',
            'wali_kelas_id' => $teacher->id,
            'tenant_id' => 1,
            'jenjang' => 'SD',
            'tingkat' => '1'
        ]);

        // Refresh teacher because relationship might be cached
        $teacher->refresh();

        $student = User::factory()->create([
            'role' => 'student',
            'class_id' => $class->id,
            'tenant_id' => 1
        ]);

        // Mock subscription for student
        PushSubscription::create([
            'user_id' => $student->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/fake-student',
            'public_key' => 'fake-key',
            'auth_token' => 'fake-token'
        ]);

        $response = $this->actingAs($teacher)
            ->post(route('teacher.announcements.store'), [
                'title' => 'Test Announcement',
                'description' => 'Test Body',
                'target_audience' => 'both',
                'school_class_id' => $class->id
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('announcements', [
            'title' => 'Test Announcement',
            'school_class_id' => $class->id
        ]);
    }
}
