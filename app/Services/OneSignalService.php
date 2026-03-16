<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    protected $appId;
    protected $apiKey;

    public function __construct()
    {
        $this->appId = config('services.onesignal.app_id');
        $this->apiKey = config('services.onesignal.rest_api_key');
    }

    /**
     * Send notification via OneSignal API
     */
    public function sendNotification($target, $title, $body, $data = [], $isTopic = false)
    {
        $url = "https://api.onesignal.com/notifications";

        $appId = trim($this->appId);
        $apiKey = trim($this->apiKey);

        Log::info('OneSignal Debug', [
            'app_id_length' => strlen($appId),
            'api_key_length' => strlen($apiKey),
            'target' => $target,
            'is_topic' => $isTopic
        ]);

        $payload = [
            'app_id' => $appId,
            'headings' => [
                'en' => $title
            ],
            'contents' => [
                'en' => $body
            ],
            'data' => $data,
        ];

        if ($isTopic) {
            $payload['included_segments'] = [$target];
        } else {
            $payload['include_subscription_ids'] = [$target];
        }

        try {

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('OneSignal Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'OneSignal request failed',
                'error_details' => $response->json()
            ];

        } catch (\Exception $e) {

            Log::error('OneSignal Exception', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}