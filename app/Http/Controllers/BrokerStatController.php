<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// use App\Models\Job;
// use App\Models\Payment;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class BrokerStatController extends Controller
{

    public function brokerDashboardStats(Request $request)
{
    $user = $request->user();

    // 🔐 Only broker allowed
    if ($user->role !== 'broker') {
        return response()->json([
            'status' => false,
            'message' => 'Only brokers can view dashboard stats',
        ], 403);
    }

    try {

        /*
        |--------------------------------------------------------------------------
        | 💰 Total Paid Amount
        |--------------------------------------------------------------------------
        */
        $totalPaidAmount = Payment::where('broker_id', $user->id)
            ->whereIn('status', ['held', 'paid', 'released'])
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | ⏳ Total Pending Amount
        |--------------------------------------------------------------------------
        */
        $totalPendingAmount = Job::where('broker_id', $user->id)
    ->where('status', 'open')
    ->sum('payment_rate');

        /*
        |--------------------------------------------------------------------------
        | 🚚 Active Jobs
        |--------------------------------------------------------------------------
        */
        $activeJobs = Job::where('broker_id', $user->id)
            ->whereIn('status', [
                'open',
                'assigned',
                'in_progress',
                'pending_approval',
            ])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | 📦 Assigned Jobs
        |--------------------------------------------------------------------------
        */
        $assignedJobs = Job::where('broker_id', $user->id)
            ->where('status', 'assigned')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | ✅ Completed Jobs
        |--------------------------------------------------------------------------
        */
        $completedJobs = Job::where('broker_id', $user->id)
            ->where('status', 'completed')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | 📅 Last Week Jobs
        |--------------------------------------------------------------------------
        */
        $lastWeekJobs = Job::where('broker_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->count();

        /*
        |--------------------------------------------------------------------------
        | 📅 Last Month Jobs
        |--------------------------------------------------------------------------
        */
        $lastMonthJobs = Job::where('broker_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->count();

        /*
        |--------------------------------------------------------------------------
        | 🔥 JOB ACTIVITIES
        |--------------------------------------------------------------------------
        */
        $jobActivities = Job::where('broker_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($job) {

                $activity = 'Job Created';

                switch ($job->status) {

                    case 'open':
                        $activity = 'Job Created';
                        break;

                    case 'assigned':
                        $activity = 'Driver Assigned';
                        break;

                    case 'in_progress':
                        $activity = 'Job In Progress';
                        break;

                    case 'pending_approval':
                        $activity = 'Documents Uploaded';
                        break;

                    case 'completed':
                        $activity = 'Job Completed';
                        break;

                    case 'rejected':
                        $activity = 'Job Rejected';
                        break;

                    default:
                        $activity = 'Job Updated';
                        break;
                }

                return [
                    'type' => 'job',
                    'job_id' => $job->id,
                    'title' => $job->title,
                    'status' => $job->status,
                    'activity' => $activity,
                    'amount' => $job->payment_rate,
                    'created_at' => $job->updated_at,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | 💰 PAYMENT ACTIVITIES
        |--------------------------------------------------------------------------
        */
        $paymentActivities = Payment::where('broker_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($payment) {

                return [
                    'type' => 'payment',
                    'job_id' => $payment->job_id,
                    'title' => 'Payment Transaction',
                    'status' => $payment->status,
                    'activity' => 'Payment Received',
                    'amount' => $payment->amount,
                    'created_at' => $payment->created_at,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | 🔥 MERGE ACTIVITIES + LAST 5
        |--------------------------------------------------------------------------
        */
        $recentActivities = $jobActivities
            ->merge($paymentActivities)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Broker dashboard stats fetched successfully',

            'data' => [

                // 💰 Amounts
                'total_paid_amount' => $totalPaidAmount,
                'total_pending_amount' => $totalPendingAmount,

                // 📦 Jobs
                'active_jobs' => $activeJobs,
                'assigned_jobs' => $assignedJobs,
                'completed_jobs' => $completedJobs,

                // 📅 Analytics
                'last_week_jobs' => $lastWeekJobs,
                'last_month_jobs' => $lastMonthJobs,

                // 🔥 Recent Activities
                'recent_activities' => $recentActivities,
            ],
        ]);

    } catch (\Exception $e) {

        Log::error('Broker Dashboard Stats Error', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch dashboard stats',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function brokerDrivers(Request $request)
{
    $user = $request->user();

    // 🔐 Only broker allowed
    if ($user->role !== 'broker') {
        return response()->json([
            'status' => false,
            'message' => 'Only brokers can view drivers',
        ], 403);
    }

    /*
    |--------------------------------------------------------------------------
    | 🚚 Get Broker Drivers
    |--------------------------------------------------------------------------
    */

    $drivers = User::where('role', 'driver')
        ->whereIn('id', function ($query) use ($user) {

            $query->select('driver_id')
                ->from('jobs')
                ->where('broker_id', $user->id)
                ->whereNotNull('driver_id');

        })
        ->latest()
        ->get();

    /*
    |--------------------------------------------------------------------------
    | 📦 Add Job Stats For Each Driver
    |--------------------------------------------------------------------------
    */

    $drivers = $drivers->map(function ($driver) use ($user) {

        $totalJobs = Job::where('broker_id', $user->id)
            ->where('driver_id', $driver->id)
            ->count();

        $completedJobs = Job::where('broker_id', $user->id)
            ->where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->count();

        $activeJobs = Job::where('broker_id', $user->id)
            ->where('driver_id', $driver->id)
            ->whereIn('status', [
                'assigned',
                'in_progress',
                'pending_approval'
            ])
            ->count();

        return [
            'id' => $driver->id,
            'full_name' => $driver->full_name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'truck_info' => $driver->truck_info,
            'license_number' => $driver->license_number,
            'is_available' => $driver->is_available,
            'average_rating' => $driver->average_rating,

            // 📊 Stats
            'total_jobs' => $totalJobs,
            'completed_jobs' => $completedJobs,
            'active_jobs' => $activeJobs,

            'created_at' => $driver->created_at,
        ];
    });

    return response()->json([
        'status' => true,
        'message' => 'Broker drivers fetched successfully',
        'drivers' => $drivers,
    ]);
}


public function driverDashboardStats(Request $request)
{
    $user = $request->user();

    // 🔐 Only driver allowed
    if ($user->role !== 'driver') {
        return response()->json([
            'status' => false,
            'message' => 'Only drivers can view dashboard stats',
        ], 403);
    }

    try {

        /*
        |--------------------------------------------------------------------------
        | 📦 Driver Jobs
        |--------------------------------------------------------------------------
        */
        $driverJobs = Job::where('driver_id', $user->id)->get();

        /*
        |--------------------------------------------------------------------------
        | 🚚 Active Jobs
        |--------------------------------------------------------------------------
        */
        $activeJobs = Job::where('driver_id', $user->id)
            ->whereIn('status', [
                'assigned',
                'in_progress',
                'pending_approval',
            ])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | ✅ Completed Jobs
        |--------------------------------------------------------------------------
        */
        $completedJobs = Job::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | 📦 Assigned Jobs
        |--------------------------------------------------------------------------
        */
        $assignedJobs = Job::where('driver_id', $user->id)
            ->where('status', 'assigned')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | 💰 This Week Earnings
        |--------------------------------------------------------------------------
        */
        $thisWeekEarnings = Payment::where('driver_id', $user->id)
            ->whereIn('status', [ 'paid',])
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->sum('driver_amount');

        /*
        |--------------------------------------------------------------------------
        | 💰 Total Earnings
        |--------------------------------------------------------------------------
        */
        $totalEarnings = Payment::where('driver_id', $user->id)
            ->whereIn('status', ['held', 'paid', 'released'])
            ->sum('driver_amount');

        /*
        |--------------------------------------------------------------------------
        | 🛣 Total Miles
        |--------------------------------------------------------------------------
        */
        $totalMiles = 0;

        foreach ($driverJobs as $job) {
            if (
                $job->pickup_lat &&
                $job->pickup_lng &&
                $job->delivery_lat &&
                $job->delivery_lng
            ) {
                $totalMiles += $this->calculateMiles(
                    $job->pickup_lat,
                    $job->pickup_lng,
                    $job->delivery_lat,
                    $job->delivery_lng
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ⏱ Total Hours
        |--------------------------------------------------------------------------
        */
        $totalMinutes = 0;

        foreach ($driverJobs as $job) {
            if ($job->started_at) {

                $startTime = Carbon::parse($job->started_at);

                // completed_at first, then approved_at, otherwise now for active job
                if ($job->completed_at) {
                    $endTime = Carbon::parse($job->completed_at);
                } elseif ($job->approved_at) {
                    $endTime = Carbon::parse($job->approved_at);
                } elseif ($job->status === 'in_progress') {
                    $endTime = now();
                } else {
                    continue;
                }

                $totalMinutes += $startTime->diffInMinutes($endTime);
            }
        }

        $totalHours = round($totalMinutes / 60, 1);

        /*
        |--------------------------------------------------------------------------
        | 🔥 Job Activities
        |--------------------------------------------------------------------------
        */
        $jobActivities = Job::where('driver_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($job) {

                $activity = 'Job Updated';

                switch ($job->status) {
                    case 'assigned':
                        $activity = 'Job Assigned';
                        break;

                    case 'in_progress':
                        $activity = 'Job Started';
                        break;

                    case 'pending_approval':
                        $activity = 'Documents Uploaded';
                        break;

                    case 'completed':
                        $activity = 'Job Completed';
                        break;

                    case 'rejected':
                        $activity = 'Job Rejected';
                        break;
                }

                return [
                    'type' => 'job',
                    'job_id' => $job->id,
                    'title' => $job->title,
                    'status' => $job->status,
                    'activity' => $activity,
                    'amount' => $job->payment_rate,
                    'created_at' => $job->updated_at,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | 💰 Payment Activities
        |--------------------------------------------------------------------------
        */
        $paymentActivities = Payment::where('driver_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($payment) {

                return [
                    'type' => 'payment',
                    'job_id' => $payment->job_id,
                    'title' => 'Payment Transaction',
                    'status' => $payment->status,
                    'activity' => 'Payment Received',
                    'amount' => $payment->driver_amount ?? $payment->amount,
                    'created_at' => $payment->created_at,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | 🔥 Merge Activities
        |--------------------------------------------------------------------------
        */
        $recentActivities = $jobActivities
            ->merge($paymentActivities)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Driver dashboard stats fetched successfully',

            'data' => [
                'active_jobs' => $activeJobs,
                'assigned_jobs' => $assignedJobs,
                'completed_jobs' => $completedJobs,

                'miles' => round($totalMiles, 2),
                'hours' => $totalHours,

                'this_week_earnings' => $thisWeekEarnings,
                'total_earnings' => $totalEarnings,

                'recent_activities' => $recentActivities,
            ],
        ]);

    } catch (\Exception $e) {

        Log::error('Driver Dashboard Stats Error', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch driver dashboard stats',
            'error' => $e->getMessage(),
        ], 500);
    }
}

private function calculateMiles($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 3959; // miles

    $latDelta = deg2rad($lat2 - $lat1);
    $lonDelta = deg2rad($lon2 - $lon1);

    $a = sin($latDelta / 2) * sin($latDelta / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($lonDelta / 2) * sin($lonDelta / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return round($earthRadius * $c, 2);
}


public function saveCompanyProfile(Request $request)
{
    $user = $request->user();

    // Only broker can create/update company profile
    if (! $user || $user->role !== 'broker') {
        return response()->json([
            'status' => false,
            'message' => 'Only broker can create or update company profile',
        ], 403);
    }

    $validator = Validator::make($request->all(), [
        'company_name' => 'nullable|string|max:255',
        'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'business_type' => 'nullable|string|max:100',
        'mc_number' => 'nullable|string|max:100',
        'dot_number' => 'nullable|string|max:100',
        'year_founded' => 'nullable|integer|min:1800|max:' . date('Y'),
        'employees' => 'nullable|string|max:100',
        'service_area' => 'nullable|string|max:255',
        'company_description' => 'nullable|string|max:2000',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors(),
        ], 422);
    }

    try {
        $data = $validator->validated();

        if ($request->hasFile('company_logo')) {
            $folder = 'uploads/company_logos';
            $publicFolder = public_path($folder);

            if (! is_dir($publicFolder)) {
                mkdir($publicFolder, 0755, true);
            }

            // Delete old logo if exists
            if ($user->company_logo) {
                $oldPath = parse_url($user->company_logo, PHP_URL_PATH);
                $oldPath = ltrim($oldPath ?? '', '/');

                if (str_starts_with($oldPath, 'uploads/company_logos/')) {
                    $oldFullPath = public_path($oldPath);

                    if (file_exists($oldFullPath)) {
                        @unlink($oldFullPath);
                    }
                }
            }

            $file = $request->file('company_logo');
            $fileName = 'company_logo_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            $file->move($publicFolder, $fileName);

            // Store full URL in database
            $data['company_logo'] = $request->getSchemeAndHttpHost() . '/' . $folder . '/' . $fileName;
        }

        // Create/update same broker profile data
        $user->update($data);
        $user->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Company profile saved successfully',
            'data' => [
                'id' => $user->id,
                'company_name' => $user->company_name,
                'company_logo' => $user->company_logo,
                'business_type' => $user->business_type,
                'mc_number' => $user->mc_number,
                'dot_number' => $user->dot_number,
                'year_founded' => $user->year_founded,
                'employees' => $user->employees,
                'service_area' => $user->service_area,
                'company_description' => $user->company_description,
            ],
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to save company profile',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function brokerProfile(Request $request)
{
    $user = $request->user();

    // Only broker can view this profile page
    if (! $user || $user->role !== 'broker') {
        return response()->json([
            'status' => false,
            'message' => 'Only broker can view profile',
        ], 403);
    }

    /*
    |--------------------------------------------------------------------------
    | Business Stats
    |--------------------------------------------------------------------------
    */

    // Total jobs posted by this broker
    $totalJobsPosted = Job::where('broker_id', $user->id)->count();

    // Active drivers who are working / assigned with this broker
    $activeDrivers = Job::where('broker_id', $user->id)
        ->whereNotNull('driver_id')
        ->whereIn('status', ['assigned', 'in_progress', 'pending_approval', 'completed'])
        ->distinct('driver_id')
        ->count('driver_id');

    // Jobs posted this month
    $thisMonthJobs = Job::where('broker_id', $user->id)
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();

    // Total paid by this broker
    $totalPaid = Payment::where('broker_id', $user->id)
        ->whereIn('status', ['held', 'released', 'paid', 'completed'])
        ->sum('amount');

    return response()->json([
        'status' => true,
        'message' => 'Broker profile fetched successfully',

        'data' => [
            /*
            |--------------------------------------------------------------------------
            | Header Data
            |--------------------------------------------------------------------------
            */
            'header' => [
                'company_name' => $user->company_name,
                'company_logo' => $user->company_logo,
                'business_type' => $user->business_type,
                'role' => $user->role,
            ],

            /*
            |--------------------------------------------------------------------------
            | Company Information Card
            |--------------------------------------------------------------------------
            */
            'company_information' => [
                'email' => $user->email,
                'phone' => $user->phone,
                'location' => $user->location ?? null,
                'company_name' => $user->company_name,
                'business_type' => $user->business_type,
                'mc_number' => $user->mc_number,
                'dot_number' => $user->dot_number,
                'year_founded' => $user->year_founded,
                'employees' => $user->employees,
                'service_area' => $user->service_area,
                'company_description' => $user->company_description,
            ],

            /*
            |--------------------------------------------------------------------------
            | Business Stats Card
            |--------------------------------------------------------------------------
            */
            'business_stats' => [
                'total_jobs_posted' => $totalJobsPosted,
                'active_drivers' => $activeDrivers,
                'this_month_jobs' => $thisMonthJobs,
                'total_paid' => round($totalPaid, 2),
                'total_paid_formatted' => '$' . number_format($totalPaid, 2),
            ],

            /*
            |--------------------------------------------------------------------------
            | Member Since Card
            |--------------------------------------------------------------------------
            */
            'member_since' => [
                'date' => $user->created_at,
                'formatted' => $user->created_at ? $user->created_at->format('F Y') : null,
            ],

            /*
            |--------------------------------------------------------------------------
            | All User Table Data
            |--------------------------------------------------------------------------
            | Password and remember_token are hidden from User model.
            */
            'user' => $user->toArray(),
        ],
    ]);
}

}
