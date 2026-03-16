<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppAuthController;
use App\Http\Controllers\Api\ReviewController;


Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'id' => $user?->id,
        'name' => $user?->name,
        'email' => $user?->email,
    ]);
});
// login api
// http://localhost:8000/api/login
Route::post('/login', [AppAuthController::class, 'login']);

Route::post('/register-broker', [AppAuthController::class, 'registerBroker']);
Route::post('/register-driver', [AppAuthController::class, 'registerDriver']);

Route::middleware('auth:sanctum')->post('/logout', [AppAuthController::class, 'logout']);

Route::post('/send-test-notification', [NotificationController::class, 'sendTestNotification']);


use App\Http\Controllers\Api\JobApiController;

Route::middleware('auth:sanctum')->post('/jobs/create', [JobApiController::class, 'createJob']);
Route::middleware('auth:sanctum')->get('/broker/jobs', [JobApiController::class, 'brokerJobs']);

Route::middleware('auth:sanctum')->get('/driver/jobs', [JobApiController::class, 'driverJobs']);

Route::middleware('auth:sanctum')->post('/jobs/{jobId}/request', [JobApiController::class, 'sendJobRequest']);
Route::middleware('auth:sanctum')->get('/broker/job-requests', [JobApiController::class, 'getBrokerJobRequests']);
Route::middleware('auth:sanctum')->post('/job-requests/{requestId}/accept', [JobApiController::class, 'acceptJobRequest']);
Route::middleware('auth:sanctum')->get('/notifications', [JobApiController::class, 'notifications']);

Route::middleware('auth:sanctum')->post('/jobs/{jobId}/review', [ReviewController::class, 'store']);
