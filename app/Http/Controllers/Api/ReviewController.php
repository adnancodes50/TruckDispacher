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
    // $user = $request->user();
    $user = $request->user();
    // Only broker can give review
    if ($user->role !== 'broker') {
        return response()->json([
            'status' => false,
            'message' => 'Only brokers can submit reviews'
        ], 403);
    }

    $job = Job::find($jobId);

    if (!$job) {
        return response()->json([
            'status' => false,
            'message' => 'Job not found'
        ], 404);
    }

    // Check job belongs to broker
    if ($job->broker_id !== $user->id) {
        return response()->json([
            'status' => false,
            'message' => 'You are not authorized to review this job'
        ], 403);
    }

    // Job must be completed
    if ($job->status !== 'completed') {
        return response()->json([
            'status' => false,
            'message' => 'Review can only be submitted after job completion'
        ], 400);
    }

    // Prevent duplicate review
    $existingReview = Review::where('job_id', $jobId)->first();

    if ($existingReview) {
        return response()->json([
            'status' => false,
            'message' => 'Review already submitted for this job'
        ], 400);
    }

    // Validation
    $validator = Validator::make($request->all(), [
        'punctuality' => 'required|integer|min:1|max:5',
        'safety' => 'required|integer|min:1|max:5',
        'compliance' => 'required|integer|min:1|max:5',
        'professionalism' => 'required|integer|min:1|max:5',
        'comments' => 'nullable|string',
        'driver_blocked' => 'nullable|boolean'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $review = Review::create([
        'job_id' => $job->id,
        'driver_id' => $job->driver_id,
        'broker_id' => $user->id,
        'punctuality' => $request->punctuality,
        'safety' => $request->safety,
        'compliance' => $request->compliance,
        'professionalism' => $request->professionalism,
        'comments' => $request->comments,
        'driver_blocked' => $request->driver_blocked ?? false,
        'is_visible' => true,
        'is_hidden' => false
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Review submitted successfully',
        'review' => $review
    ]);
}
}
