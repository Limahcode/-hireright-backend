<?php

use App\Http\Controllers\Admin\AdminTestController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\JobCategoryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Candidate\CandidateController;
use App\Http\Controllers\Candidate\CandidateJobApplicationController;
use App\Http\Controllers\Candidate\ExperienceController;
use App\Http\Controllers\Candidate\EducationController;
use App\Http\Controllers\Candidate\CertificationController;
use App\Http\Controllers\Candidate\SavedJobController;
use App\Http\Controllers\Candidate\JobAlertController;
use App\Http\Controllers\Candidate\TestController;
use App\Http\Controllers\Company\CompanyJobController;
use App\Http\Controllers\Company\CompanyStaffController;
use App\Http\Controllers\Company\CompanyTestController;
use App\Http\Controllers\Company\JobApplicationController;
use App\Http\Controllers\FileStorageController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Health check
Route::get('/health', [HealthController::class, 'check']);

Route::get('/test-log', function () {
    Log::info('This is a test log message.');
    return 'Log test complete.';
});
Route::get('/clear-test-users', function() {
    // Delete all users except ID 1 (keep admin if exists)
    DB::table('users')->where('id', '>', 1)->delete();
    
    return response()->json([
        'message' => 'All test users cleared',
        'remaining_users' => DB::table('users')->count()
    ]);
});

Route::get('/debug-jobs', function() {
    return response()->json([
        'pending_jobs' => DB::table('jobs')->count(),
        'failed_jobs' => DB::table('failed_jobs')->count(),
        'recent_jobs' => DB::table('jobs')->latest('id')->take(5)->get(),
        'recent_failed' => DB::table('failed_jobs')->latest('id')->take(5)->get()
    ]);
});

Route::get('/clear-failed-jobs', function() {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('failed_jobs')->truncate();
    DB::table('jobs')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    return 'Cleared!';
});
// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('validate/email-otp', [AuthController::class, 'validateEmailOtp']);
    Route::post('validate/phone-otp', [AuthController::class, 'validatePhoneOtp']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('request-password-reset', [AuthController::class, 'requestPasswordReset']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Candidate Routes
Route::middleware(['auth:sanctum', 'can:candidate'])->prefix('candidates')->group(function () {
    // Company Dashboard
    Route::get('dashboard', [CandidateController::class, 'dashboard']);
    //
    Route::get('profile', [CandidateController::class, 'getProfile']);
    Route::post('profile', [CandidateController::class, 'storeProfile']);
    //
    Route::apiResource('experiences', ExperienceController::class);
    Route::post('experiences/batch', [ExperienceController::class, 'batchStore']);
    Route::put('experiences/batch', [ExperienceController::class, 'batchUpdate']);
    //
    Route::apiResource('education', EducationController::class);
    Route::post('education/batch', [EducationController::class, 'batchStore']);
    Route::put('education/batch', [EducationController::class, 'batchUpdate']);
    //
    Route::apiResource('certifications', CertificationController::class);
    Route::post('certifications/batch', [CertificationController::class, 'batchStore']);
    Route::put('certifications/batch', [CertificationController::class, 'batchUpdate']);

    // Job Applications
    Route::get('jobs', [CandidateJobApplicationController::class, 'index']);
    Route::get('jobs/recommended', [CandidateJobApplicationController::class, 'getRecommendations']);
    Route::get('jobs/{jobId}', [CandidateJobApplicationController::class, 'show']);
    Route::post('jobs/{jobId}/apply', [CandidateJobApplicationController::class, 'apply']);
    Route::get('applications', [CandidateJobApplicationController::class, 'applications']);
    Route::post('applications/{id}/withdraw', [CandidateJobApplicationController::class, 'withdraw']);
    //
    Route::post('jobs/{jobId}/save', [SavedJobController::class, 'save']);
    Route::delete('jobs/{jobId}/delete', [SavedJobController::class, 'remove']);
    Route::get('savedjobs', [SavedJobController::class, 'index']);

    // Job Alerts
    Route::apiResource('alerts', JobAlertController::class);
    Route::get('alerts/{alert}/matches', [JobAlertController::class, 'getMatches']);
    Route::put('alerts/{alert}/toggle', [JobAlertController::class, 'toggleStatus']);

    // Test Taking
    Route::get('tests/assigned', [TestController::class, 'assignedTests']);
    Route::get('tests/{test}', [TestController::class, 'show']);
    Route::post('tests/{test}/start', [TestController::class, 'startTest']);
    Route::get('tests/{test}/questions', [TestController::class, 'getQuestions']);
    Route::post('tests/{test}/questions/{question}/upload', [TestController::class, 'uploadAttachment']);
    Route::post('tests/{test}/submit', [TestController::class, 'submit']);
    Route::get('tests/{test}/result', [TestController::class, 'viewResult']);

    Route::apiResource('notifications', NotificationController::class)->only(['index', 'update', 'destroy']);
});

// Employer Routes
// Employer Routes
Route::middleware(['auth:sanctum', 'can:employer'])->prefix('employers')->group(function () {
    // Company Dashboard
    Route::get('dashboard', [CompanyController::class, 'dashboard']);
    // Company Profile
    Route::post('company', [CompanyController::class, 'store']);
    Route::put('company', [CompanyController::class, 'update']);
    Route::get('company', [CompanyController::class, 'show']);
    // Staff Management
    Route::get('company/staffs', [CompanyStaffController::class, 'index']);
    Route::put('staff/{staffId}/permissions', [CompanyStaffController::class, 'updatePermissions']);
    
    // Job Management
    Route::apiResource('jobs', CompanyJobController::class);
    Route::put('jobs/{job}/status', [CompanyJobController::class, 'updateStatus']);
    //
    Route::get('jobs/{jobId}/applications', [JobApplicationController::class, 'getApplicationsForJob']);
    Route::get('jobs/{jobId}/candidates', [JobApplicationController::class, 'getCandidatesForJob']);
    Route::get('company/candidates', [JobApplicationController::class, 'getAllCandidatesForCompany']);
    //
    Route::put('jobs/{jobId}/test', [CompanyJobController::class, 'assignTest']);
    Route::delete('jobs/{jobId}/test', [CompanyJobController::class, 'removeTest']);

    // Test Management
    Route::apiResource('tests', CompanyTestController::class)->names([
        'index' => 'company.tests.index',
        'store' => 'company.tests.store',
        'show' => 'company.tests.show',
        'update' => 'company.tests.update',
        'destroy' => 'company.tests.destroy',
    ]);
    Route::post('tests/{test}/questions', [CompanyTestController::class, 'addQuestion']);
    Route::put('tests/{test}/questions/{question}', [CompanyTestController::class, 'updateQuestion']);
    Route::delete('tests/{test}/questions/{question}', [CompanyTestController::class, 'removeQuestion']);
    Route::post('tests/{test}/questions/{question}/options', [CompanyTestController::class, 'addOptions']);
    Route::put('tests/{test}/questions/{question}/options', [CompanyTestController::class, 'updateOptions']);
    Route::post('tests/{test}/questions/{question}/attachments', [CompanyTestController::class, 'addAttachment']);
    Route::delete('tests/{test}/questions/{question}/attachments/{attachment}', [CompanyTestController::class, 'removeAttachment']);
    Route::get('tests/{test}/submissions', [CompanyTestController::class, 'getSubmissions']);
    Route::get('tests/{test}/submissions/{submission}', [CompanyTestController::class, 'viewSubmission']);
    Route::post('tests/{test}/submissions/{submission}/grade', [CompanyTestController::class, 'gradeSubmission']);
    Route::get('tests/{test}/jobs', [CompanyTestController::class, 'assignedJobs']);

    Route::get('dashboard/stats', [CompanyController::class, 'dashboard']);
});
// Admin Routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::put('users/{userId}/status', [UserController::class, 'updateStatus']);
    Route::get('users/{userId}/activity', [UserController::class, 'activityLog']);

    Route::apiResource('companies', AdminCompanyController::class);
    Route::put('companies/{company}/verify', [AdminCompanyController::class, 'verifyCompany']);
    Route::put('companies/{company}/status', [AdminCompanyController::class, 'updateStatus']);

    // Route::apiResource('tests', AdminTestController::class);
    Route::apiResource('tests', AdminTestController::class)->names([
    'index' => 'admin.tests.index',
    'store' => 'admin.tests.store',
    'show' => 'admin.tests.show',
    'update' => 'admin.tests.update',
    'destroy' => 'admin.tests.destroy',
]);
    Route::put('tests/{test}/status', [AdminTestController::class, 'updateStatus']);

    Route::get('settings', [SettingController::class, 'index']);
    Route::put('settings', [SettingController::class, 'update']);

    Route::get('reports/users', [ReportController::class, 'userStats']);
    Route::get('reports/jobs', [ReportController::class, 'jobStats']);
    Route::get('reports/applications', [ReportController::class, 'applicationStats']);
    Route::get('reports/tests', [ReportController::class, 'testStats']);

    Route::apiResource('job/categories', JobCategoryController::class);
});

// Common Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [UserProfileController::class, 'viewProfile']);
    Route::put('profile', [UserProfileController::class, 'updateProfile']);
});

Route::middleware(['auth:sanctum'])->prefix('storage')->group(function () {
    // Initialize & confirm file upload 
    Route::prefix('upload')->group(function () {
        Route::post('init', [FileStorageController::class, 'initializeUpload']);
        Route::put('confirm', [FileStorageController::class, 'confirmUpload']);
    });
    // File operations
    Route::prefix('files')->group(function () {
        Route::get('{fileId}', [FileStorageController::class, 'getFile']);
        Route::delete('{fileId}', [FileStorageController::class, 'deleteFile']);
    });
    //
    Route::prefix('entity')->group(function () {
        Route::get('files', [FileStorageController::class, 'getFiles']);
    });
});