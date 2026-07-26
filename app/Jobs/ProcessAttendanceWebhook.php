<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAttendanceWebhook implements ShouldQueue
{
    use Queueable;

    public $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Mocking the outbound webhook
        $webhookUrl = config('services.webhook.url', 'https://webhook.site/mock');
        
        try {
            \Illuminate\Support\Facades\Http::post($webhookUrl, $this->payload);
            \Illuminate\Support\Facades\Log::info('Webhook dispatched successfully: ' . json_encode($this->payload));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to dispatch webhook: ' . $e->getMessage());
            // Optionally: throw $e; to retry the job
        }
    }
}
