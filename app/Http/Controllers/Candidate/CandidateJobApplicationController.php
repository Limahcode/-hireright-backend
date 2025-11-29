<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CandidateJobApplicationController extends Controller
{
    /**
     * Display available job listings with filters
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'type' => ['nullable', Rule::in(['full_time', 'part_time', 'contract', 'internship'])],
                'experience_level' => ['nullable', Rule::in(['entry', 'mid', 'senior', 'lead', 'management'])],
                'salary_min' => 'nullable|numeric',
                'salary_max' => 'nullable|numeric',
                'sort_by' => 'nullable|in:latest,salary_desc,salary_asc',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid filters provided',
                    'errors' => $validator->errors()
                ], 422);
            }

            $jobs = JobListing::active()
                ->with('company:id,name')
                ->when($request->filled('q'), function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->q . '%')
                        ->orWhere('description', 'like', '%' . $request->q . '%');
                })
                ->when($request->filled('location'), function ($q) use ($request) {
                    $q->where('location', 'like', '%' . $request->location . '%');
                })
                ->when($request->filled('type'), function ($q) use ($request) {
                    $q->where('type', $request->type);
                })
                ->when($request->filled('experience_level'), function ($q) use ($request) {
                    $q->where('experience_level', $request->experience_level);
                })
                ->when($request->filled('salary_min'), function ($q) use ($request) {
                    $q->where('salary_min', '>=', $request->salary_min);
                })
                ->when($request->filled('salary_max'), function ($q) use ($request) {
                    $q->where('salary_max', '<=', $request->salary_max);
                })
                ->latest()
                ->paginate(20);

            return response()->json([
                'status' => 'success',
                'data' => $jobs
            ]);
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve jobs'
            ], 500);
        }
    }

    /**
     * Get recommended jobs (random 10 jobs)
     */
    public function getRecommendations()
    {
        try {
            $jobs = JobListing::active()
                ->with('company:id,name')
                ->inRandomOrder()
                ->limit(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $jobs
            ]);
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve recommended jobs'
            ], 500);
        }
    }

    /**
     * Show specific job details
     */
    public function show($jobId)
    {
        try {
            $job = JobListing::active()
                ->with('company')
                ->findOrFail($jobId);

            return response()->json([
                'status' => 'success',
                'data' => $job
            ]);
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Job not found'
            ], 404);
        }
    }

    /**
     * Display candidate's job applications
     */
    public function applications(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => ['nullable', Rule::in(JobApplication::STATUSES)],
                'sort_by' => 'nullable|in:latest,company',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid filters provided',
                    'errors' => $validator->errors()
                ], 422);
            }

            $applications = JobApplication::where('user_id', Auth::id())
                ->whereHas('jobListing', function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->with(['jobListing:id,title,company_id', 'jobListing.company:id,name'])
                ->when($request->filled('status'), function ($q) use ($request) {
                    $q->where('status', $request->status);
                })
                ->latest()
                ->get()
                ->map(function ($application) {
                    return [
                        'id' => $application->id,
                        'job_id' => $application->jobListing->id,
                        'job_title' => $application->jobListing->title,
                        'company_name' => $application->jobListing->company->name,
                        'status' => $application->status,
                        'applied_at' => $application->created_at->format('Y-m-d\TH:i:s\Z'),
                        'last_updated' => $application->updated_at->format('Y-m-d\TH:i:s\Z'),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $applications
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving applications: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'filters' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve applications'
            ], 500);
        }
    }

    /**
     * Apply for a job
     */
    public function apply(Request $request, $jobId)
    {
        try {
            $job = JobListing::active()->findOrFail($jobId);
            // Check if already applied
            $existingApplication = JobApplication::where('job_id', $jobId)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingApplication) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already applied for this job'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'cover_letter' => 'nullable|string|max:5000',
                'answers' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $application = JobApplication::create([
                'job_id' => $jobId,
                'company_id' => $job->company_id,
                'user_id' => Auth::id(),
                'cover_letter' => $request->cover_letter,
                'answers' => $request->answers,
                'status' => 'applied'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Application submitted successfully',
                'data' => $application
            ], 201);
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit application'
            ], 500);
        }
    }

    /**
     * Withdraw an application
     */
    public function withdraw($id)
    {
        try {
            $application = JobApplication::where('user_id', Auth::id())
                ->findOrFail($id);

            if ($application->status === 'withdrawn') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Application already withdrawn'
                ], 422);
            }

            if (in_array($application->status, ['hired', 'rejected'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot withdraw this application'
                ], 422);
            }

            $application->update(['status' => 'withdrawn']);

            return response()->json([
                'status' => 'success',
                'message' => 'Application withdrawn successfully'
            ]);
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to withdraw application'
            ], 500);
        }
    }
}
