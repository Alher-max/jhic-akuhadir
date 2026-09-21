<?php

namespace App\Services;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected $webPush;

    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject' => config('webpush.subject'),
                'publicKey' => config('webpush.public_key'),
                'privateKey' => config('webpush.private_key'),
            ],
        ];

        $this->webPush = new WebPush($auth);
    }

    /**
     * Send notification to a specific user.
     */
    public function sendToUser($user, $title, $body, $url = '/dashboard')
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'publicKey' => $sub->public_key,
                'authToken' => $sub->auth_token,
                'contentEncoding' => $sub->content_encoding,
            ]);

            $this->webPush->queueNotification(
                $subscription,
                json_encode([
                    'title' => $title,
                    'body' => $body,
                    'url' => $url,
                ])
            );
        }

        return $this->flush();
    }

    /**
     * Send to multiple users.
     */
    public function sendToMany($users, $title, $body, $url = '/dashboard')
    {
        if ($users->isEmpty()) {
            Log::warning("PushNotificationService: Attempted to send to empty user list.");
            return;
        }

        foreach ($users as $user) {
            $this->sendToUser($user, $title, $body, $url);
        }
    }

    protected function flush()
    {
        $results = [];
        try {
            foreach ($this->webPush->flush() as $report) {
                $endpoint = $report->getEndpoint();
                if (!$report->isSuccess()) {
                    Log::error("Push Notification failed for endpoint: {$endpoint}. Error: {$report->getReason()}");
                    
                    // Optional: Delete expired subscriptions
                    if ($report->isSubscriptionExpired()) {
                        PushSubscription::where('endpoint', $endpoint)->delete();
                    }
                }
                $results[] = [
                    'endpoint' => $endpoint,
                    'success' => $report->isSuccess()
                ];
            }
        } catch (\Throwable $e) {
            Log::error("PushNotificationService flush failed: " . $e->getMessage());
        }
        return $results;
    }
}
