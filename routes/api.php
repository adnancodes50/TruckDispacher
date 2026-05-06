<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppAuthController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\JobApiController;



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

// Route::post('/send-test-notification', [NotificationController::class, 'sendTestNotification']);
Route::middleware('auth:sanctum')->get('/broker/jobs-with-payment', [JobApiController::class, 'jobsWithPaymentStatus']);


Route::middleware('auth:sanctum')->post('/jobs/create', [JobApiController::class, 'createJob']);
Route::middleware('auth:sanctum')->get('/broker/jobs', [JobApiController::class, 'brokerJobs']);

Route::middleware('auth:sanctum')->get('/driver/jobs', [JobApiController::class, 'driverJobs']);

Route::middleware('auth:sanctum')->post('/jobs/{jobId}/request', [JobApiController::class, 'sendJobRequest']);
Route::middleware('auth:sanctum')->get('/broker/job-requests', [JobApiController::class, 'getBrokerJobRequests']);
Route::middleware('auth:sanctum')->post('/job-requests/{requestId}/accept', [JobApiController::class, 'acceptJobRequest']);
Route::middleware('auth:sanctum')->get('/broker/in-progress-jobs', [JobApiController::class, 'inProgressJobsWithDocuments']);
Route::middleware('auth:sanctum')->get('/notifications', [JobApiController::class, 'notifications']);

Route::middleware('auth:sanctum')->post('/jobs/{jobId}/review', [ReviewController::class, 'store']);
Route::middleware('auth:sanctum')->post('/jobs/{jobId}/pay', [JobApiController::class, 'payJob']);
Route::get('/payment-success', [JobApiController::class, 'paymentSuccess']);
Route::middleware('auth:sanctum')->post('/start-job/{jobId}', [JobApiController::class, 'startJob']);
Route::middleware('auth:sanctum')->get('/jobs/{jobId}/route', [JobApiController::class, 'getJobRoute']); // ✅ ADD THIS

// Route::middleware('auth:sanctum')->post('/jobs/{jobId}/complete', [JobApiController::class, 'completeJob']);
Route::middleware('auth:sanctum')->get('/jobs/{jobId}/documents', [JobApiController::class, 'getJobDocuments']);

// Route::middleware('auth:sanctum')->post('/jobs/{jobId}/approve', [JobApiController::class, 'approveJob']);
Route::middleware('auth:sanctum')->get('/driver/assigned-jobs', [JobApiController::class, 'driverAssignedJobs']);
Route::middleware('auth:sanctum')->post('/jobs/{jobId}/upload-documents', [JobApiController::class, 'uploadJobDocuments']);
Route::middleware('auth:sanctum')->post('/jobs/{jobId}/submit-documents', [JobApiController::class, 'markDocumentsUploaded']);
Route::middleware('auth:sanctum')->get('/broker/pending-approval-jobs', [JobApiController::class, 'pendingApprovalJobs']);
Route::middleware('auth:sanctum')->post('/jobs/{jobId}/approve', [JobApiController::class, 'approveJob']);
Route::middleware('auth:sanctum')->post('/jobs/{jobId}/reject', [JobApiController::class, 'rejectJob']);
// ─────────────────────────────────
// Password Reset (no auth required)
// ─────────────────────────────────
Route::post('/forgot-password', [AppAuthController::class, 'forgotPassword']);
Route::match(['get', 'post'], '/reset-password',  [AppAuthController::class, 'resetPassword']);


