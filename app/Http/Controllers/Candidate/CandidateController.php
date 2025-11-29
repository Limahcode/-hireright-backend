<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\JobAlert;
use App\Models\JobApplication;
use App\Models\SavedJob;
use App\Models\User;
use App\Services\CandidateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CandidateController extends Controller
{
    protected $candidateService;

    public function __construct(CandidateService $candidateService)
    {
        $this->candidateService = $candidateService;
    }

    public function dashboard(Request $request)
    {
        try {
            $userId = Auth::id();
            // $user = User::findOrFail($userId);
            // Get the total number of jobs the candidate has applied for
            $appliedJobs = JobApplication::where('user_id', $userId)->count();
            // Get the total number of jobs the candidate has saved
            $savedJobs = SavedJob::where('user_id', $userId)->count();
            // Get the total number of job alerts for the candidate
            $jobAlerts = JobAlert::where('user_id', $userId)->count();

            // Return the stats as a JSON response
            return response()->json([
                'status' => 'success',
                'data' => [
                    'applied_jobs' => $appliedJobs,
                    'saved_jobs' => $savedJobs,
                    'job_alerts' => $jobAlerts,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve dashboard stats',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getProfile()
    {
        try {
            $profileData = $this->candidateService->getProfileData();
            return response()->json([
                'status' => 'success',
                'data' => $profileData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve profile data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function storeProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                // Education validation
                'education' => 'array',
                'education.*.institution' => 'required|string|max:255',
                'education.*.degree' => 'required|string|max:255',
                'education.*.field_of_study' => 'required|string|max:255',
                'education.*.location' => 'nullable|string|max:255',
                'education.*.start_date' => 'required|date|before_or_equal:today',
                'education.*.end_date' => 'nullable|date|before_or_equal:today',
                'education.*.is_current' => 'boolean',
                'education.*.activities' => 'nullable|string|max:2000',
                'education.*.description' => 'nullable|string|max:2000',

                // Experience validation
                'experiences' => 'array',
                'experiences.*.company_name' => 'required|string|max:255',
                'experiences.*.job_title' => 'required|string|max:255',
                'experiences.*.description' => 'nullable|string|max:5000',
                'experiences.*.location' => 'nullable|string|max:255',
                'experiences.*.employment_type' => ['required', Rule::in(['full_time', 'part_time', 'self_employed', 'freelance', 'contract', 'internship'])],
                'experiences.*.start_date' => 'required|date|before_or_equal:today',
                'experiences.*.end_date' => 'nullable|date|before_or_equal:today',
                'experiences.*.is_current' => 'boolean',

                // Certification validation
                'certifications' => 'array',
                'certifications.*.name' => 'required|string|max:255',
                'certifications.*.organization' => 'required|string|max:255',
                'certifications.*.issue_date' => 'required|date|before_or_equal:today',
                'certifications.*.expiration_date' => 'nullable|date|after:certifications.*.issue_date',
                'certifications.*.has_expiry' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $profileData = $this->candidateService->storeProfileData($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Profile data stored successfully',
                'data' => $profileData
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store profile data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
