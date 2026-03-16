<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\OneSignalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $oneSignalService;

    public function __construct(OneSignalService $oneSignalService)
    {
        $this->oneSignalService = $oneSignalService;
    }

    /**
     * Send a test notification
     */
    public function sendTestNotification(Request $request)
    {
        try {
            // Support both old FCM keys and new OneSignal keys for backwards compatibility
            $request->validate([
                'fcm_token' => 'nullable|string',
                'topic' => 'nullable|string',
                'player_id' => 'nullable|string',
                'segment' => 'nullable|string',
                'title' => 'required|string',
                'body' => 'required|string',
                'data' => 'nullable|array',
            ]);

            $target = $request->player_id ?? $request->segment ?? $request->fcm_token ?? $request->topic ?? 'All';
            $isTopic = $request->has('segment') || $request->has('topic') || !($request->player_id || $request->fcm_token);

            $result = $this->oneSignalService->sendNotification(
                $target,
                $request->title,
                $request->body,
                $request->data ?? [],
                $isTopic
            );

            $isSuccess = isset($result['id']) || (isset($result['success']) && $result['success'] === true);

            if ($isSuccess) {
                // Save to database for Admin Panel tracking
                // Since this is a test from API, we'll associate it with all Admin users 
                // so it shows up in their topbar
                $adminUsers = User::where('role', 'admin')->get();

                foreach ($adminUsers as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'test_notification',
                        'title' => $request->title,
                        'body' => $request->body,
                        'data' => $request->data ?? [],
                        'created_at' => now(),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Notification sent successfully and logged' . ($adminUsers->count() > 0 ? " for {$adminUsers->count()} admins" : ""),
                    'details' => $result['data']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $result['message'] ?? ($result['errors'][0] ?? 'Failed to send notification'),
                'details' => $result
            ], 500);

        } catch (\Exception $e) {
            Log::error('Notification API Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}
