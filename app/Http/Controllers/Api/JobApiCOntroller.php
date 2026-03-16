<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OneSignalService;
use App\Models\User;
use App\Models\Job;
use App\Models\JobRequest;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Log;

// use Illuminate\Support\Facades\Validator;

class JobApiController extends Controller
{
    public function createJob(Request $request)
    {
        // $user = $request->user();
        $user = $request->user();

        // only broker allowed
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only brokers can create jobs',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'pickup_address' => 'required|string',
            'delivery_address' => 'required|string',
            'scheduled_at' => 'required|date',
            'payment_rate' => 'required|numeric',
            'load_type' => 'required|string',
            'load_weight' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        $job = Job::create([
            'broker_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'instructions' => $request->instructions,
            'pickup_address' => $request->pickup_address,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'delivery_address' => $request->delivery_address,
            'delivery_lat' => $request->delivery_lat,
            'delivery_lng' => $request->delivery_lng,
            'scheduled_at' => $request->scheduled_at,
            'payment_rate' => $request->payment_rate,
            'load_type' => $request->load_type,
            'load_weight' => $request->load_weight,
            'visibility' => 'public',
            'status' => 'open',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Job created successfully',
            'job' => $job,
        ]);
    }

    public function brokerJobs(Request $request)
    {
        $user = $request->user();

        // only broker allowed
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only brokers can view their jobs',
            ], 403);
        }

        $jobs = Job::where('broker_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Broker jobs fetched successfully',
            'jobs' => $jobs,
        ]);
    }

    public function driverJobs(Request $request)
    {
        $user = $request->user();

        // only drivers allowed
        if ($user->role !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'Only drivers can view available jobs',
            ], 403);
        }

        $jobs = Job::whereNull('driver_id')
            ->where('status', 'open')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Available jobs fetched successfully',
            'jobs' => $jobs,
        ]);
    }

    public function sendJobRequest(Request $request, $jobId)
    {
        $user = $request->user();

        // Only drivers allowed
        if ($user->role !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'Only drivers can send job requests',
            ], 403);
        }

        // Check driver availability
        if ($user->is_available != 1) {
            return response()->json([
                'status' => false,
                'message' => 'Driver is not available',
            ], 400);
        }

        // Check max active requests (3)
        if ($user->active_requests_count >= 3) {
            return response()->json([
                'status' => false,
                'message' => 'Max 3 active requests allowed',
            ], 400);
        }

        $job = Job::find($jobId);

        if (! $job) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found',
            ], 404);
        }

        // Prevent duplicate request
        $exists = JobRequest::where('job_id', $jobId)
            ->where('driver_id', $user->id)
            ->first();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'You already sent request for this job',
            ], 400);
        }

        // Request expires in 2 hours
        $expiresAt = Carbon::now()->addHours(2);

        // Create Job Request
        $jobRequest = JobRequest::create([
            'job_id' => $jobId,
            'driver_id' => $user->id,
            'status' => 'pending',
            'note' => $request->note,
            'expires_at' => $expiresAt,
        ]);

        // Increase driver's active request count
        $user->increment('active_requests_count');

        // Notify broker
        // Notify broker in database
Notification::create([
    'user_id' => $job->broker_id,
    'type' => 'job_request',
    'title' => 'New Job Request',
    'body' => $user->full_name . ' requested your job',
    'data' => json_encode([
        'job_id' => $job->id,
        'driver_id' => $user->id,
        'job_request_id' => $jobRequest->id,
    ]),
    'created_at' => now(),
]);

// Send push notification using OneSignal
$broker = User::find($job->broker_id);

if ($broker && $broker->fcm_token) {

    $oneSignal = new OneSignalService();

    $oneSignal->sendNotification(
        $broker->fcm_token,
        "New Job Request",
        $user->full_name . " requested your job: " . $job->title,
        [
            "job_id" => $job->id,
            "driver_id" => $user->id,
            "job_request_id" => $jobRequest->id
        ]
    );
}

        return response()->json([
            'status' => true,
            'message' => 'Job request sent successfully',
            'request' => [
                'id' => $jobRequest->id,
                'job_id' => $jobRequest->job_id,
                'driver_id' => $jobRequest->driver_id,
                'status' => $jobRequest->status,
                'expires_at' => $expiresAt,
            ],
        ]);
    }

    public function notifications(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'notifications' => $notifications,
        ]);
    }

    public function getBrokerJobRequests(Request $request)
    {
        $user = $request->user();

        // Only broker allowed
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only brokers can view job requests',
            ], 403);
        }

        $requests = JobRequest::with(['job', 'driver'])
            ->whereHas('job', function ($query) use ($user) {
                $query->where('broker_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'requests' => $requests,
        ]);
    }

    // accept job request

public function acceptJobRequest(Request $request, $requestId)
{
    $user = $request->user();

    // Only broker allowed
    if ($user->role !== 'broker') {
        return response()->json([
            'status' => false,
            'message' => 'Only brokers can accept job requests'
        ], 403);
    }

    $jobRequest = JobRequest::find($requestId);

    if (!$jobRequest) {
        return response()->json([
            'status' => false,
            'message' => 'Job request not found'
        ], 404);
    }

    $job = Job::find($jobRequest->job_id);

    DB::beginTransaction();

    try {

        // Update job request
        $jobRequest->status = 'assigned';
        $jobRequest->responded_at = now();
        $jobRequest->save();

        // Update job
        $job->driver_id = $jobRequest->driver_id;
        $job->status = 'assigned';
        $job->save();

        // Delete other requests
        JobRequest::where('job_id', $job->id)
            ->where('id', '!=', $jobRequest->id)
            ->delete();

        DB::commit();

        /*
        |--------------------------------------------------------------------------
        | Send notification to driver
        |--------------------------------------------------------------------------
        */

        $driver = User::find($jobRequest->driver_id);

        if ($driver && $driver->fcm_token) {

            $oneSignal = new OneSignalService();

            $response = $oneSignal->sendNotification(
                $driver->fcm_token,
                "Job Accepted",
                "Your request for job '{$job->title}' has been accepted",
                [
                    "job_id" => $job->id,
                    "status" => "assigned"
                ]
            );

            // Log response
            Log::info('Driver Notification Sent', [
                'driver_id' => $driver->id,
                'player_id' => $driver->fcm_token,
                'response' => $response
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Job assigned successfully',
            'job' => [
                'job_id' => $job->id,
                'full_name'=>$job->full_name,
                'driver_id' => $job->driver_id,
                'status' => $job->status
            ]
        ]);

    } catch (\Exception $e) {

        DB::rollback();

        Log::error('Job Accept Error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}
