<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Job;
use App\Models\JobRequest;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Services\OneSignalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Stripe\Checkout\Session;
// use Stripe\PaymentIntent;
use Stripe\Customer;
use Stripe\Stripe;

// use Stripe\Stripe;

// use Illuminate\Support\Facades\Validator;

class JobApiController extends Controller
{
    public function createJob(Request $request)
    {
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

        // 🔔 Save notification in DB
        Notification::create([
            'user_id' => $user->id,
            'type' => 'job_created',
            'title' => 'Job Created',
            'body' => 'Job created successfully. Please make payment before assigning a driver.',
            'data' => json_encode([
                'job_id' => $job->id,
            ]),
            'created_at' => now(),
        ]);

        // 🔔 Send OneSignal Push
        if ($user->onesignal_player_id) {

            $oneSignal = new OneSignalService;

            $oneSignal->sendNotification(
                $user->onesignal_player_id,
                'Job Created',
                'Please make payment before assigning this job to a driver.',
                [
                    'job_id' => $job->id,
                    'type' => 'payment_required',
                ]
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Job created successfully',
            'job' => $job,
        ]);
    }

    public function payJob(Request $request, $jobId)
    {
        $user = $request->user();

        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only broker can pay',
            ], 403);
        }

        $job = Job::findOrFail($jobId);

        if ($job->broker_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $settings = Setting::first();

        if (! $settings || ! $settings->stripe_secret_key) {
            return response()->json([
                'status' => false,
                'message' => 'Stripe not configured',
            ], 500);
        }

        try {

            Stripe::setApiKey($settings->stripe_secret_key);

            // ✅ Create Stripe customer if not exists
            if (! $user->stripe_customer_id) {
                $customer = Customer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                ]);

                $user->update([
                    'stripe_customer_id' => $customer->id,
                ]);
            }

            // ✅ Create Stripe Checkout Session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'customer' => $user->stripe_customer_id,

                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $job->title,
                        ],
                        'unit_amount' => (int) ($job->payment_rate * 100),
                    ],
                    'quantity' => 1,
                ]],

                'mode' => 'payment',

                // 🔥 IMPORTANT FIX
                'success_url' => 'https://mydriver.theurl.co/api/payment-success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => 'myapp://payment-cancel',

                'metadata' => [
                    'job_id' => $job->id,
                    'broker_id' => $user->id,
                ],
            ]);

            return response()->json([
                'status' => true,
                'checkout_url' => $session->url,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Payment failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        try {

            $sessionId = $request->session_id;

            if (! $sessionId) {
                return redirect()->to('myapp://payment-failed?error=missing_session');
            }

            $settings = Setting::first();
            Stripe::setApiKey($settings->stripe_secret_key);

            // ✅ Get Stripe session
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect()->to('myapp://payment-failed?error=not_paid');
            }

            $jobId = $session->metadata->job_id;
            $job = Job::find($jobId);

            if (! $job) {
                return redirect()->to('myapp://payment-failed?error=job_not_found');
            }

            // ✅ Prevent duplicate payment
            if (! Payment::where('job_id', $job->id)->exists()) {

                $platformFeePercent = $settings->platform_commission ?? 10;

                $platformFee = ($job->payment_rate * $platformFeePercent) / 100;
                $driverAmount = $job->payment_rate - $platformFee;

                Payment::create([
                    'invoice_id' => null,
                    'job_id' => $job->id,
                    'broker_id' => $job->broker_id,
                    'driver_id' => null,
                    'amount' => $job->payment_rate,
                    'platform_fee' => $platformFee,
                    'driver_amount' => $driverAmount,
                    'method' => 'card',
                    'status' => 'held',
                    'stripe_payment_intent_id' => $session->payment_intent,
                    'held_at' => now(),
                ]);
            }

            // ✅ Redirect to Flutter app (SUCCESS)
            return redirect()->to("myapp://payment-success?status=success&job_id={$job->id}");

        } catch (\Exception $e) {

            \Log::error('Payment Success Error', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->to('myapp://payment-failed?error='.urlencode($e->getMessage()));
        }
    }

    private function calculateMiles($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 3959; // Miles

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    public function brokerJobs(Request $request)
    {
        $user = $request->user();

        // 🔐 Only broker allowed
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only brokers can view their jobs',
            ], 403);
        }

        // 📦 Get jobs
        $jobs = Job::where('broker_id', $user->id)
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 🚚 Calculate Miles
        |--------------------------------------------------------------------------
        */
        $jobs = $jobs->map(function ($job) {

            $miles = null;

            // ✅ Check coordinates exist
            if (
                $job->pickup_lat &&
                $job->pickup_lng &&
                $job->delivery_lat &&
                $job->delivery_lng
            ) {

                $miles = $this->calculateMiles(
                    $job->pickup_lat,
                    $job->pickup_lng,
                    $job->delivery_lat,
                    $job->delivery_lng
                );
            }

            // ✅ Add new field
            $job->distance_miles = $miles;

            return $job;
        });

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

    public function driverAssignedJobs(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'Only drivers allowed',
            ], 403);
        }

        $jobs = Job::where('driver_id', $user->id)   // ✅ only this driver
            ->whereNotNull('broker_id')              // ✅ broker must exist
            ->whereIn('status', ['assigned', 'in_progress', 'completed', 'pending_approval', 'rejected']) // ✅ assigned jobs only
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
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

    $job = Job::find($jobId);

    if (! $job) {
        return response()->json([
            'status' => false,
            'message' => 'Job not found',
        ], 404);
    }

    // Make sure job is still available
    if ($job->status !== 'open' || $job->driver_id !== null) {
        return response()->json([
            'status' => false,
            'message' => 'This job is no longer available',
        ], 400);
    }

    $now = now();
    $expiresAt = $now->copy()->addMinutes(5);

    try {
        DB::beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | Expire old pending requests of this driver
        |--------------------------------------------------------------------------
        */
        JobRequest::where('driver_id', $user->id)
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->update([
                'status' => 'expired',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Check active request limit
        |--------------------------------------------------------------------------
        */
        $activeRequestsCount = JobRequest::where('driver_id', $user->id)
            ->where('status', 'pending')
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })
            ->count();

        if ($activeRequestsCount >= 15) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Max 15 active requests allowed',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Check existing request for same driver and same job
        |--------------------------------------------------------------------------
        */
        $jobRequest = JobRequest::where('job_id', $jobId)
            ->where('driver_id', $user->id)
            ->lockForUpdate()
            ->first();

        $isReactivated = false;

        if ($jobRequest) {
            $isStillActive = $jobRequest->status === 'pending'
                && (
                    is_null($jobRequest->expires_at)
                    || Carbon::parse($jobRequest->expires_at)->gt($now)
                );

            // If request is still active, do not resend
            if ($isStillActive) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'You already have an active request for this job',
                ], 400);
            }

            // If request already accepted/assigned, do not reactivate
            if ($jobRequest->status === 'assigned') {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Your request for this job is already assigned',
                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | Reactivate existing expired request
            |--------------------------------------------------------------------------
            | No new row will be created here.
            */
            $jobRequest->update([
                'status' => 'pending',
                'note' => $request->note,
                'expires_at' => $expiresAt,
                'responded_at' => null,
            ]);

            $isReactivated = true;

        } else {
            /*
            |--------------------------------------------------------------------------
            | Create request only first time
            |--------------------------------------------------------------------------
            */
            $jobRequest = JobRequest::create([
                'job_id' => $jobId,
                'driver_id' => $user->id,
                'status' => 'pending',
                'note' => $request->note,
                'expires_at' => $expiresAt,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Update driver's active request count correctly
        |--------------------------------------------------------------------------
        */
        $newActiveRequestsCount = JobRequest::where('driver_id', $user->id)
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->count();

        $user->update([
            'active_requests_count' => $newActiveRequestsCount,
        ]);

        DB::commit();

        /*
        |--------------------------------------------------------------------------
        | Notify broker
        |--------------------------------------------------------------------------
        */
        Notification::create([
            'user_id' => $job->broker_id,
            'type' => 'job_request',
            'title' => $isReactivated ? 'Job Request Sent Again' : 'New Job Request',
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

        if ($broker && $broker->onesignal_player_id) {
            $oneSignal = new OneSignalService;

            $oneSignal->sendNotification(
                $broker->onesignal_player_id,
                $isReactivated ? 'Job Request Sent Again' : 'New Job Request',
                $user->full_name . ' requested your job: ' . $job->title,
                [
                    'job_id' => $job->id,
                    'driver_id' => $user->id,
                    'job_request_id' => $jobRequest->id,
                    'type' => 'job_request',
                ]
            );
        }

        return response()->json([
            'status' => true,
            'message' => $isReactivated
                ? 'Job request activated again successfully'
                : 'Job request sent successfully',
            'request' => [
                'id' => $jobRequest->id,
                'job_id' => $jobRequest->job_id,
                'driver_id' => $jobRequest->driver_id,
                'status' => $jobRequest->status,
                'expires_at' => $jobRequest->expires_at,
            ],
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Send Job Request Error', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Failed to send job request',
            'error' => $e->getMessage(),
        ], 500);
    }
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

        // 🔐 Only broker allowed
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only brokers can view job requests',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | ⏰ Auto Expire Old Requests
        |--------------------------------------------------------------------------
        */
        JobRequest::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update([
                'status' => 'expired',
            ]);

        /*
        |--------------------------------------------------------------------------
        | 📦 Fetch Active Requests
        |--------------------------------------------------------------------------
        */
        $requests = JobRequest::with(['job', 'driver'])
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
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

        // 🔐 Only broker can accept job requests
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only brokers can accept job requests',
            ], 403);
        }

        // 📦 Get job request
        $jobRequest = JobRequest::find($requestId);

        if (! $jobRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Job request not found',
            ], 404);
        }

        // 📦 Get related job
        $job = Job::find($jobRequest->job_id);

        if (! $job) {
            Log::error('Job not found while accepting request', [
                'job_id' => $jobRequest->job_id,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Job not found',
            ], 404);
        }

        // 🔒 Check payment exists + lock row (prevent double assignment)
        // 🔒 Check payment exists for this job and is held
        $payment = Payment::where('job_id', $job->id)
            ->where('status', 'held')
            ->lockForUpdate() // prevent double assignment
            ->first();

        if (! $payment) {
            return response()->json([
                'status' => false,
                'message' => 'Valid payment not found. Please complete payment before assigning a driver.',
            ], 400);
        }

        // 🔒 Ensure payment is successful and held
        if ($payment->status !== 'held') {
            return response()->json([
                'status' => false,
                'message' => 'Payment is not completed yet',
            ], 400);
        }

        DB::beginTransaction();

        try {

            // ✅ Mark selected job request as assigned
            $jobRequest->status = 'assigned';
            $jobRequest->responded_at = now();
            $jobRequest->save();

            // ✅ Attach driver to payment (DO NOT change payment status)
            $payment->update([
                'driver_id' => $jobRequest->driver_id,
            ]);

            // ✅ Assign driver to job
            $job->driver_id = $jobRequest->driver_id;
            $job->status = 'assigned';
            $job->save();

            // ❌ Remove all other driver requests for this job
            JobRequest::where('job_id', $job->id)
                ->where('id', '!=', $jobRequest->id)
                ->delete();

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | 🔔 Notify Driver
            |--------------------------------------------------------------------------
            */

            $driver = User::find($jobRequest->driver_id);

            Log::info('Driver fetched for notification', [
                'driver_id' => $jobRequest->driver_id,
                'exists' => $driver ? true : false,
            ]);

            if ($driver && $driver->onesignal_player_id) {

                try {
                    $oneSignal = new OneSignalService;

                    $oneSignal->sendNotification(
                        $driver->onesignal_player_id,
                        'Job Accepted',
                        "Your request for job '{$job->title}' has been accepted",
                        [
                            'job_id' => $job->id,
                            'status' => 'assigned',
                        ]
                    );

                } catch (\Exception $e) {
                    Log::error('OneSignal Error', [
                        'driver_id' => $driver->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // ✅ Final response
            return response()->json([
                'status' => true,
                'message' => 'Job assigned successfully',
                'job' => [
                    'job_id' => $job->id,
                    'driver_id' => $job->driver_id,
                    'status' => $job->status,
                ],
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            Log::error('Job Accept Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getJobDocuments(Request $request, $jobId)
    {
        $user = $request->user();

        // only broker
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only broker can view documents',
            ], 403);
        }

        $job = Job::findOrFail($jobId);

        // ensure broker owns job
        if ($job->broker_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $documents = Document::where('job_id', $job->id)->get();

        return response()->json([
            'status' => true,
            'documents' => $documents,
        ]);
    }

    // public function approveJob(Request $request, $jobId)
    // {
    //     $user = $request->user();

    //     // only broker
    //     if ($user->role !== 'broker') {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Only broker can approve job',
    //         ], 403);
    //     }

    //     $job = Job::findOrFail($jobId);

    //     // ensure broker owns job
    //     if ($job->broker_id !== $user->id) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Unauthorized',
    //         ], 403);
    //     }

    //     // check if document exists
    //     $hasDocument = Document::where('job_id', $job->id)->exists();

    //     if (! $hasDocument) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'No document uploaded by driver',
    //         ], 400);
    //     }

    //     try {

    //         // ✅ update job status
    //         $job->update([
    //             'status' => 'completed',
    //             'approved_at' => now(),
    //         ]);

    //         // 💰 start payout timer
    //         Payment::where('job_id', $job->id)->update([
    //             'released_at' => now()->addDays(2),
    //         ]);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Job approved successfully',
    //         ]);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Approval failed',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function jobsWithPaymentStatus(Request $request)
    {
        $user = $request->user();

        // 🔐 Only broker
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only brokers can view jobs',
            ], 403);
        }

        // 📦 Get all jobs
        $jobs = Job::with('driver')
            ->where('broker_id', $user->id)
            ->latest()
            ->get();

        // 📦 Get all payments for these jobs (optimized)
        $payments = Payment::whereIn('job_id', $jobs->pluck('id'))
            ->get()
            ->keyBy('job_id');

        // 🔄 Map jobs
        $jobs = $jobs->map(function ($job) use ($payments) {

            // 🔍 Check payment
            $payment = $payments[$job->id] ?? null;

            if (! $payment) {
                // ❌ No payment → pending
                $job->payment_status = 'pending';
                $job->payment = null;

            } else {
                // ✅ Payment exists
                if ($payment->status === 'held') {
                    $job->payment_status = 'paid';
                } else {
                    $job->payment_status = 'pending';
                }

                // ✅ Attach full payment data
                $job->payment = $payment;
            }

            return $job;
        });

        return response()->json([
            'status' => true,
            'jobs' => $jobs,
        ]);
    }

    public function startJob(Request $request, $jobId)
    {
        $user = $request->user();

        // 🔐 Only driver allowed
        if ($user->role !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'Only driver can start job',
            ], 403);
        }

        $job = Job::findOrFail($jobId);

        // 🔒 Ensure driver owns job
        if ($job->driver_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // 🔒 Ensure job is assigned
        if ($job->status !== 'assigned') {
            return response()->json([
                'status' => false,
                'message' => 'Job is not in assigned state',
            ], 400);
        }

        try {

            // ✅ Update job status
            $job->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | 🔔 Notify Broker (DB + OneSignal)
            |--------------------------------------------------------------------------
            */

            // Save notification in DB
            Notification::create([
                'user_id' => $job->broker_id,
                'type' => 'job_started',
                'title' => 'Job Started',
                'body' => 'Driver has started your job: '.$job->title,
                'data' => json_encode([
                    'job_id' => $job->id,
                    'driver_id' => $user->id,
                ]),
                'created_at' => now(),
            ]);

            // Send push notification
            $broker = User::find($job->broker_id);

            if ($broker && $broker->onesignal_player_id) {

                $oneSignal = new OneSignalService;

                $oneSignal->sendNotification(
                    $broker->onesignal_player_id,
                    'Job Started',
                    $user->full_name.' started job: '.$job->title,
                    [
                        'job_id' => $job->id,
                        'driver_id' => $user->id,
                        'status' => 'in_progress',
                    ]
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Job started successfully',
                'job' => $job,
            ]);

        } catch (\Exception $e) {

            \Log::error('Start Job Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to start job',
            ], 500);
        }
    }

    public function getJobRoute($jobId, Request $request)
    {
        $user = $request->user();

        $job = Job::findOrFail($jobId);

        // 🔐 Only assigned driver OR broker can view
        if (
            ($user->role === 'driver' && $job->driver_id !== $user->id) &&
            ($user->role === 'broker' && $job->broker_id !== $user->id)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'job' => [
                'job_id' => $job->id,
                'title' => $job->title,

                // 📍 Pickup
                'pickup' => [
                    'address' => $job->pickup_address,
                    'lat' => $job->pickup_lat,
                    'lng' => $job->pickup_lng,
                ],

                // 📍 Delivery
                'delivery' => [
                    'address' => $job->delivery_address,
                    'lat' => $job->delivery_lat,
                    'lng' => $job->delivery_lng,
                ],

                'status' => $job->status,
            ],
        ]);
    }

    public function inProgressJobsWithDocuments(Request $request)
    {
        $user = $request->user();

        // 🔐 Only broker allowed
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only brokers can view in-progress jobs',
            ], 403);
        }

        // 📦 Get all in-progress jobs of this broker
        $jobs = Job::with(['driver', 'documents'])
            ->where('broker_id', $user->id)
            ->where('status', 'in_progress')
            ->latest()
            ->get();

        // 🔄 Format response (clean for app)
        $jobs = $jobs->map(function ($job) {

            return [
                'job_id' => $job->id,
                'title' => $job->title,
                'status' => $job->status,
                'started_at' => $job->started_at,

                // 🚗 Driver info
                'driver' => [
                    'id' => $job->driver->id ?? null,
                    'name' => $job->driver->full_name ?? null,
                    'email' => $job->driver->email ?? null,
                ],

                // 📂 Documents (proof uploaded at start)
                'documents' => $job->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'file_name' => $doc->file_name,
                        'file_url' => $doc->file_url, // already full URL ✅
                        'file_type' => $doc->file_type,
                        'uploaded_at' => $doc->created_at,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'jobs' => $jobs,
        ]);
    }

    public function uploadJobDocuments(Request $request, $jobId)
    {
        $user = $request->user();

        // 🔐 Only driver allowed
        if ($user->role !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'Only driver can upload documents',
            ], 403);
        }

        $job = Job::findOrFail($jobId);

        // 🔒 Ensure driver owns job
        if ($job->driver_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // 🔒 Only allow when job is in progress
        if ($job->status !== 'in_progress') {
            return response()->json([
                'status' => false,
                'message' => 'Documents can only be uploaded when job is in progress',
            ], 400);
        }

        // ✅ Validate multiple files
        $request->validate([
            'documents' => 'required|array|min:1',
            'documents.*.file_name' => 'required|string',
            'documents.*.file_type' => 'required|string',
            'documents.*.file_data' => 'required|string',
        ]);

        try {

            $uploadedDocs = [];

            foreach ($request->documents as $doc) {

                // ✅ Decode base64
                $fileData = base64_decode($doc['file_data']);

                if (! $fileData) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid file data',
                    ], 400);
                }

                // 📁 Unique filename
                $fileName = time().'_'.uniqid().'_'.$doc['file_name'];

                // 📁 Path
                $path = 'job_progress_documents/'.$fileName;

                // 💾 Save file
                Storage::disk('public')->put($path, $fileData);

                // 🌐 URL
                $fileUrl = asset('storage/'.$path);

                // 💾 Save in DB
                $document = Document::create([
                    'job_id' => $job->id,
                    'uploaded_by' => $user->id,
                    'file_url' => $fileUrl,
                    'file_type' => $doc['file_type'],
                    'file_name' => $fileName,
                    'created_at' => now(),
                ]);

                $uploadedDocs[] = $document;
            }

            return response()->json([
                'status' => true,
                'message' => 'Documents uploaded successfully',
                'documents' => $uploadedDocs,
            ]);

        } catch (\Exception $e) {

            \Log::error('Upload Job Documents Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to upload documents',
            ], 500);
        }
    }

    public function markDocumentsUploaded(Request $request, $jobId)
    {
        $user = $request->user();

        // 🔐 Only driver allowed
        if ($user->role !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'Only driver allowed',
            ], 403);
        }

        $job = Job::findOrFail($jobId);

        // 🔒 Ensure driver owns job
        if ($job->driver_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // 🔒 Ensure job is in progress
        if ($job->status !== 'in_progress') {
            return response()->json([
                'status' => false,
                'message' => 'Job must be in progress',
            ], 400);
        }

        // 🔒 Ensure documents exist
        $hasDocuments = Document::where('job_id', $job->id)->exists();

        if (! $hasDocuments) {
            return response()->json([
                'status' => false,
                'message' => 'Please upload documents first',
            ], 400);
        }

        try {

            // ✅ Update job status
            $job->update([
                'status' => 'pending_approval',
                'completed_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | 🔔 Notify Broker
            |--------------------------------------------------------------------------
            */

            Notification::create([
                'user_id' => $job->broker_id,
                'type' => 'documents_uploaded',
                'title' => 'Documents Uploaded',
                'body' => 'Driver uploaded documents for job: '.$job->title,
                'data' => json_encode([
                    'job_id' => $job->id,
                ]),
                'created_at' => now(),
            ]);

            $broker = User::find($job->broker_id);

            if ($broker && $broker->onesignal_player_id) {

                $oneSignal = new OneSignalService;

                $oneSignal->sendNotification(
                    $broker->onesignal_player_id,
                    'Documents Uploaded',
                    $user->full_name.' uploaded documents for job: '.$job->title,
                    [
                        'job_id' => $job->id,
                        'status' => 'pending_approval',
                    ]
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Documents submitted successfully, waiting for approval',
                'job' => $job,
            ]);

        } catch (\Exception $e) {

            \Log::error('Mark Documents Uploaded Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to update status',
            ], 500);
        }
    }

    public function pendingApprovalJobs(Request $request)
    {
        $user = $request->user();

        // 🔐 Only broker allowed
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only brokers can view pending jobs',
            ], 403);
        }

        // 📦 Get jobs with status pending_approval
        $jobs = Job::with(['driver', 'documents'])
            ->where('broker_id', $user->id)
            ->where('status', 'pending_approval')
            ->latest()
            ->get();

        // 🔄 Format response
        $jobs = $jobs->map(function ($job) {

            return [
                'job_id' => $job->id,
                'title' => $job->title,
                'status' => $job->status,
                'completed_at' => $job->completed_at,

                // 🚗 Driver info
                'driver' => [
                    'id' => $job->driver->id ?? null,
                    'name' => $job->driver->full_name ?? null,
                    'email' => $job->driver->email ?? null,
                ],

                // 📂 Documents
                'documents' => $job->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'file_name' => $doc->file_name,
                        'file_url' => $doc->file_url,
                        'file_type' => $doc->file_type,
                        'uploaded_at' => $doc->created_at,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'jobs' => $jobs,
        ]);
    }

    public function rejectJob(Request $request, $jobId)
    {
        $user = $request->user();

        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only broker can reject job',
            ], 403);
        }

        $job = Job::findOrFail($jobId);

        if ($job->broker_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($job->status !== 'pending_approval') {
            return response()->json([
                'status' => false,
                'message' => 'Job is not pending approval',
            ], 400);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {

            $job->update([
                'status' => 'rejected',
            ]);

            // 🔔 Save notification in DB
            Notification::create([
                'user_id' => $job->driver_id,
                'type' => 'job_rejected',
                'title' => 'Job Rejected',
                'body' => 'Your job was rejected: '.$job->title,
                'data' => json_encode([
                    'job_id' => $job->id,
                    'reason' => $request->reason,
                ]),
                'created_at' => now(),
            ]);

            // 🔔 Push Notification (OneSignal)
            $driver = User::find($job->driver_id);

            if ($driver && $driver->onesignal_player_id) {

                $oneSignal = new OneSignalService;

                $oneSignal->sendNotification(
                    $driver->onesignal_player_id,
                    'Job Rejected',
                    'Your job was rejected: '.$job->title,
                    [
                        'job_id' => $job->id,
                        'status' => 'rejected',
                        'reason' => $request->reason,
                    ]
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Job rejected successfully',
                'reason' => $request->reason,
            ]);

        } catch (\Exception $e) {

            \Log::error('Reject Job Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Rejection failed',
            ], 500);
        }
    }

    public function approveJob(Request $request, $jobId)
    {
        $user = $request->user();

        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only broker can approve job',
            ], 403);
        }

        $job = Job::findOrFail($jobId);

        if ($job->broker_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($job->status !== 'pending_approval') {
            return response()->json([
                'status' => false,
                'message' => 'Job is not pending approval',
            ], 400);
        }

        try {

            $job->update([
                'status' => 'completed',
                'approved_at' => now(),
            ]);

            // 🔔 Save notification in DB
            Notification::create([
                'user_id' => $job->driver_id,
                'type' => 'job_approved',
                'title' => 'Job Approved',
                'body' => 'Your job has been approved: '.$job->title,
                'data' => json_encode([
                    'job_id' => $job->id,
                ]),
                'created_at' => now(),
            ]);

            // 🔔 Push Notification (OneSignal)
            $driver = User::find($job->driver_id);

            if ($driver && $driver->onesignal_player_id) {

                $oneSignal = new OneSignalService;

                $oneSignal->sendNotification(
                    $driver->onesignal_player_id,
                    'Job Approved',
                    'Your job has been approved: '.$job->title,
                    [
                        'job_id' => $job->id,
                        'status' => 'completed',
                    ]
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Job approved successfully',
            ]);

        } catch (\Exception $e) {

            \Log::error('Approve Job Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Approval failed',
            ], 500);
        }
    }

    public function editJob(Request $request, $jobId)
    {
        $user = $request->user();

        // 🔐 Only broker allowed
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only brokers can edit jobs',
            ], 403);
        }

        $job = Job::find($jobId);

        if (! $job) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found',
            ], 404);
        }

        // 🔒 Ensure broker owns this job
        if ($job->broker_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // 🔒 Prevent editing after assignment
        if ($job->status !== 'open') {
            return response()->json([
                'status' => false,
                'message' => 'Only open jobs can be edited',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'pickup_address' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'payment_rate' => 'nullable|numeric',
            'load_type' => 'nullable|string',
            'load_weight' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        try {

            $job->update([
                'title' => $request->title ?? $job->title,
                'description' => $request->description ?? $job->description,
                'instructions' => $request->instructions ?? $job->instructions,

                'pickup_address' => $request->pickup_address ?? $job->pickup_address,
                'pickup_lat' => $request->pickup_lat ?? $job->pickup_lat,
                'pickup_lng' => $request->pickup_lng ?? $job->pickup_lng,

                'delivery_address' => $request->delivery_address ?? $job->delivery_address,
                'delivery_lat' => $request->delivery_lat ?? $job->delivery_lat,
                'delivery_lng' => $request->delivery_lng ?? $job->delivery_lng,

                'scheduled_at' => $request->scheduled_at ?? $job->scheduled_at,
                'payment_rate' => $request->payment_rate ?? $job->payment_rate,
                'load_type' => $request->load_type ?? $job->load_type,
                'load_weight' => $request->load_weight ?? $job->load_weight,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Job updated successfully',
                'job' => $job,
            ]);

        } catch (\Exception $e) {

            Log::error('Edit Job Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to update job',
            ], 500);
        }
    }

    public function deleteJob(Request $request, $jobId)
    {
        $user = $request->user();

        // 🔐 Only broker allowed
        if ($user->role !== 'broker') {
            return response()->json([
                'status' => false,
                'message' => 'Only brokers can delete jobs',
            ], 403);
        }

        $job = Job::find($jobId);

        if (! $job) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found',
            ], 404);
        }

        // 🔒 Ensure broker owns this job
        if ($job->broker_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // 🔒 Prevent deleting assigned/in-progress/completed jobs
        if ($job->status !== 'open') {
            return response()->json([
                'status' => false,
                'message' => 'Only open jobs can be deleted',
            ], 400);
        }

        try {

            // ❌ Delete related job requests
            JobRequest::where('job_id', $job->id)->delete();

            // ❌ Delete related payments if exists
            Payment::where('job_id', $job->id)->delete();

            // ❌ Delete job
            $job->delete();

            return response()->json([
                'status' => true,
                'message' => 'Job deleted successfully',
            ]);

        } catch (\Exception $e) {

            Log::error('Delete Job Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete job',
            ], 500);
        }
    }


    public function getNotifications(Request $request)
{
    $user = $request->user();

    // Only broker and driver can view notifications
    if (! in_array($user->role, ['broker', 'driver'])) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized role',
        ], 403);
    }

    $notifications = Notification::where('user_id', $user->id)
        ->latest()
        ->get()
        ->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'body' => $notification->body,
                'data' => $notification->data
                    ? json_decode($notification->data, true)
                    : null,
                'created_at' => $notification->created_at,
            ];
        });

    return response()->json([
        'status' => true,
        'message' => 'Notifications fetched successfully',
        'role' => $user->role,
        'notifications' => $notifications,
    ]);
}


public function deleteNotifications(Request $request)
{
    $user = $request->user();

    // Only broker and driver can delete notifications
    if (! in_array($user->role, ['broker', 'driver'])) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized role',
        ], 403);
    }

    $clearAll = $request->boolean('clear_all');

    /*
    |--------------------------------------------------------------------------
    | Clear All Notifications
    |--------------------------------------------------------------------------
    */
    if ($clearAll) {
        $deletedCount = Notification::where('user_id', $user->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'All notifications deleted successfully',
            'deleted_count' => $deletedCount,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Single / Multiple Notifications
    |--------------------------------------------------------------------------
    */
    $validator = Validator::make($request->all(), [
        'notification_ids' => 'required|array|min:1',
        'notification_ids.*' => 'required|integer|distinct',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors(),
        ], 422);
    }

    $notificationIds = $request->notification_ids;

    $deletedCount = Notification::where('user_id', $user->id)
        ->whereIn('id', $notificationIds)
        ->delete();

    return response()->json([
        'status' => true,
        'message' => 'Notifications deleted successfully',
        'deleted_count' => $deletedCount,
    ]);
}
}
