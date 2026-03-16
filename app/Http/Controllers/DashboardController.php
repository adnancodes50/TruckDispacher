<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Job;
use App\Models\Review;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DashboardController extends Controller
{
    public function index()
    {

        $stats = [
            'total_loads' => Job::count(),
            'active_drivers' => User::where('role', 'driver')->count(),
            'total_brokers' => User::where('role', 'broker')->count(),
            'pending_review' => Job::where('status', 'open')->count(),
            'delivered_this_month' => Job::where('status', 'completed')
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'total_reviews' => Review::count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Top Rated Drivers
        |--------------------------------------------------------------------------
        */

        $topDrivers = Review::select(
                'driver_id',
                DB::raw('AVG((punctuality + safety + compliance + professionalism) / 4) as rating')
            )
            ->with('driver:id,full_name')
            ->groupBy('driver_id')
            ->orderByDesc('rating')
            ->take(4)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Top Rated Brokers
        |--------------------------------------------------------------------------
        */

        $topBrokers = Review::select(
                'broker_id',
                DB::raw('COUNT(id) as total_reviews')
            )
            ->with('broker:id,company_name')
            ->groupBy('broker_id')
            ->orderByDesc('total_reviews')
            ->take(4)
            ->get();


//             $recentJobs = Job::with(['driver', 'broker'])
//         ->where('status', 'completed')
//         ->whereDate('updated_at', Carbon::today())
//         ->latest()
//         ->take(10)
//         ->get();
// // dd($recentJobs);
        return view('dashboard', compact('stats','topDrivers','topBrokers'));
    }
}