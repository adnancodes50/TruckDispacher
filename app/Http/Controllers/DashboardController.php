<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Job;
use Illuminate\Http\Request;

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
            'total_revenue' => \App\Models\Payment::where('status', 'paid')->sum('amount'),
            'total_reviews' => \App\Models\Review::count(),
        ];

        return view('dashboard', compact('stats'));
    }
}
