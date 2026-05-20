<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
   use App\Models\Review;
use App\Models\Job;
use Illuminate\Support\Facades\Validator;


class ReviewController extends Controller
{

public function store(Request $request, $jobId)
{
    $user = $request->user();

    // 🔐 Only broker can review
    if ($user->role !== 'broker') {
        return response()->json([
            'status' => false,
            'message' => 'Only brokers can submit reviews'
        ], 403);
    }

    // 📦 Find Job
    $job = Job::find($jobId);

    if (! $job) {
        return response()->json([
            'status' => false,
            'message' => 'Job not found'
        ], 404);
    }

    // 🔒 Ensure broker owns this job
    if ($job->broker_id !== $user->id) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 403);
    }

    // 🔒 Job must be completed
    if ($job->status !== 'completed') {
        return response()->json([
            'status' => false,
            'message' => 'Review allowed only after job completion'
        ], 400);
    }

    // 🔒 Driver must exist
    if (! $job->driver_id) {
        return response()->json([
            'status' => false,
            'message' => 'Driver not found for this job'
        ], 400);
    }

    // 🔒 Prevent duplicate review
    $existingReview = Review::where('job_id', $job->id)->first();

    if ($existingReview) {
        return response()->json([
            'status' => false,
            'message' => 'Review already submitted'
        ], 400);
    }

    // ✅ Validation
    $validator = Validator::make($request->all(), [
        'punctuality' => 'required|integer|min:1|max:5',
        'safety' => 'required|integer|min:1|max:5',
        'compliance' => 'required|integer|min:1|max:5',
        'professionalism' => 'required|integer|min:1|max:5',
        'comments' => 'nullable|string|max:1000',
        'driver_blocked' => 'nullable|boolean'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | ⭐ Calculate Overall Rating
    |--------------------------------------------------------------------------
    */

    $overallRating = round(
        (
            $request->punctuality +
            $request->safety +
            $request->compliance +
            $request->professionalism
        ) / 4,
        1
    );

    /*
    |--------------------------------------------------------------------------
    | 💾 Save Review
    |--------------------------------------------------------------------------
    */

    $review = Review::create([
        'job_id' => $job->id,
        'driver_id' => $job->driver_id,
        'broker_id' => $user->id,

        'punctuality' => $request->punctuality,
        'safety' => $request->safety,
        'compliance' => $request->compliance,
        'professionalism' => $request->professionalism,

        'overall_rating' => $overallRating,

        'comments' => $request->comments,
        'driver_blocked' => $request->driver_blocked ?? false,

        'is_visible' => true,
        'is_hidden' => false
    ]);

    /*
    |--------------------------------------------------------------------------
    | 🚚 Update Driver Average Rating
    |--------------------------------------------------------------------------
    */

    $driver = User::find($job->driver_id);

    if ($driver) {

        $averageRating = Review::where('driver_id', $driver->id)
            ->avg('overall_rating');

        $totalReviews = Review::where('driver_id', $driver->id)
            ->count();

        $driver->update([
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
        ]);
    }

    return response()->json([
        'status' => true,
        'message' => 'Review submitted successfully',

        'review' => [
            'id' => $review->id,
            'job_id' => $review->job_id,
            'driver_id' => $review->driver_id,
            'overall_rating' => $review->overall_rating,
            'comments' => $review->comments,
        ]
    ]);
}
}
