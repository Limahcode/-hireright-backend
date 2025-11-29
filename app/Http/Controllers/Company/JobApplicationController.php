<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class JobApplicationController extends Controller
{

    public function getApplicationsForJob(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            //
            $validator = Validator::make($request->all(), [
                'jobId' => 'nullable|exists:job_listings,id',
                'status' => ['nullable', Rule::in([
                    'applied',
                    'under_review',
                    'shortlisted',
                    'interview_scheduled',
                    'test_invited',
                    'test_completed',
                    'offered',
                    'hired',
                    'rejected',
                    'withdrawn'
                ])],
                'sortBy' => 'nullable|in:latest,status',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid filters provided',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = JobApplication::whereHas('jobListing', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })->with([
                'jobListing:id,title',
                'user:id,first_name,last_name,email',
                'reviewedBy:id,first_name,last_name'
            ])->when($request->filled('jobId'), function ($q) use ($request) {
                $q->where('job_id', $request->jobId);
            })->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            });

            $applications = $query->latest()->get()->map(function ($application) {
                return [
                    'id' => $application->id,
                    'job_id' => $application->jobListing->id,
                    'job_title' => $application->jobListing->title,
                    'candidate_name' => $application->user->first_name . ' ' . $application->user->last_name,
                    'candidate_email' => $application->user->email,
                    'status' => $application->status,
                    'applied_at' => $application->created_at->format('Y-m-d\TH:i:s\Z'),
                    'reviewed_at' => $application->reviewed_at?->format('Y-m-d\TH:i:s\Z'),
                    'reviewed_by' => $application->reviewedBy ?
                        $application->reviewedBy->first_name . ' ' . $application->reviewedBy->last_name :
                        null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $applications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve applications'
            ], 500);
        }
    }

    public function getCandidatesForJob(Request $request, $jobId)
    {
        try {
            // Ensure the user is authenticated
            $userId = Auth::id();
            $user = User::findOrFail($userId);

            // Validate the job ID
            $validator = Validator::make(['jobId' => $jobId], [
                'jobId' => 'required|exists:job_listings,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid job ID provided',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Base query: Get candidates who applied to the specified job
            $query = JobApplication::where('job_id', $jobId)
                ->whereHas('jobListing', function ($q) use ($user) {
                    $q->where('company_id', $user->company_id); // Ensure the job belongs to the user's company
                })
                ->with([
                    'user:id,first_name,last_name,email', // Include candidate details
                    'reviewedBy:id,first_name,last_name' // Include reviewer details
                ]);

            // Apply filters (if any)
            if ($request->filled('status')) {
                $query->where('status', $request->status); // Filter by application status
            }

            // Apply sorting (if any)
            if ($request->filled('sortBy')) {
                $sortBy = $request->sortBy;
                if ($sortBy === 'latest') {
                    $query->latest(); // Sort by latest applications first
                } elseif ($sortBy === 'oldest') {
                    $query->oldest(); // Sort by oldest applications first
                }
            }

            // Paginate the results (optional)
            $applications = $query->paginate(10); // Adjust the number of items per page as needed

            // Format the response to focus on candidates
            $candidates = $applications->map(function ($application) {
                return [
                    'id' => $application->user->id, // Candidate ID
                    'name' => $application->user->first_name . ' ' . $application->user->last_name,
                    'email' => $application->user->email,
                    'status' => $application->status, // Application status
                    'applied_at' => $application->created_at->format('Y-m-d\TH:i:s\Z'),
                    'reviewed_at' => $application->reviewed_at?->format('Y-m-d\TH:i:s\Z'),
                    'reviewed_by' => $application->reviewedBy ?
                        $application->reviewedBy->first_name . ' ' . $application->reviewedBy->last_name :
                        null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'candidates' => $candidates,
                    'pagination' => [
                        'total' => $applications->total(),
                        'per_page' => $applications->perPage(),
                        'current_page' => $applications->currentPage(),
                        'last_page' => $applications->lastPage(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve candidates for the job',
                'error' => $e->getMessage() // Include the error message for debugging
            ], 500);
        }
    }

    public function getAllCandidatesForCompany(Request $request)
    {
        try {
            // Ensure the user is authenticated
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            // Validate the request
            $validator = Validator::make($request->all(), [
                'status' => ['nullable', Rule::in([
                    'applied',
                    'under_review',
                    'shortlisted',
                    'interview_scheduled',
                    'test_invited',
                    'test_completed',
                    'offered',
                    'hired',
                    'rejected',
                    'withdrawn'
                ])],
                'sortBy' => 'nullable|in:latest,oldest', // Sort by application date
                'page' => 'nullable|integer|min:1', // Pagination page
                'size' => 'nullable|integer|min:1|max:100', // Pagination size
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid filters provided',
                    'errors' => $validator->errors()
                ], 422);
            }
            // Base query: Get all candidates who applied to jobs within the user's company
            $query = JobApplication::whereHas('jobListing', function ($q) use ($user) {
                $q->where('company_id', $user->company_id); // Ensure the job belongs to the user's company
            })
                ->with([
                    'user:id,first_name,last_name,email', // Include candidate details
                    'reviewedBy:id,first_name,last_name' // Include reviewer details
                ])
                ->select('user_id', 'status', 'created_at', 'reviewed_at')
                ->groupBy('user_id', 'status', 'created_at', 'reviewed_at'); // Fix SQL error

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status); // Filter by application status
            }
            // Apply sorting
            if ($request->filled('sortBy')) {
                $sortBy = $request->sortBy;
                if ($sortBy === 'latest') {
                    $query->latest(); // Sort by latest applications first
                } elseif ($sortBy === 'oldest') {
                    $query->oldest(); // Sort by oldest applications first
                }
            }
            // Paginate the results
            $page = $request->input('page', 1); // Default page is 1
            $size = $request->input('size', 10); // Default size is 10
            $applications = $query->paginate($size, ['*'], 'page', $page);
            // Format the response to focus on candidates
            $candidates = $applications->map(function ($application) {
                return [
                    'id' => $application->user->id, // Candidate ID
                    'name' => $application->user->first_name . ' ' . $application->user->last_name,
                    'email' => $application->user->email,
                    'status' => $application->status, // Latest application status
                    'applied_at' => $application->created_at->format('Y-m-d\TH:i:s\Z'), // Latest application date
                    'reviewed_at' => $application->reviewed_at?->format('Y-m-d\TH:i:s\Z'),
                    'reviewed_by' => $application->reviewedBy ?
                        $application->reviewedBy->first_name . ' ' . $application->reviewedBy->last_name :
                        null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'candidates' => $candidates,
                    'pagination' => [
                        'total' => $applications->total(),
                        'per_page' => $applications->perPage(),
                        'current_page' => $applications->currentPage(),
                        'last_page' => $applications->lastPage(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve candidates',
                'error' => $e->getMessage() // Include the error message for debugging
            ], 500);
        }
    }

    /**
     * Update application status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            //
            $application = JobApplication::whereHas('jobListing', function ($q) {
                $q->where('company_id', Auth::user()->company_id);
            })->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status' => ['required', Rule::in([
                    'under_review',
                    'shortlisted',
                    'interview_scheduled',
                    'test_invited',
                    'test_completed',
                    'offered',
                    'hired',
                    'rejected'
                ])],
                'rejection_reason' => [
                    Rule::requiredIf(function () use ($request) {
                        return $request->status === 'rejected';
                    }),
                    'nullable',
                    'string',
                    'max:1000'
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = [
                'status' => $request->status,
                'rejection_reason' => $request->rejection_reason,
            ];

            // Set reviewed_at and reviewed_by if first time reviewing
            if (!$application->reviewed_at && $request->status !== 'applied') {
                $data['reviewed_at'] = now();
                $data['reviewed_by'] = Auth::id();
            }

            $application->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Application status updated successfully',
                'data' => $application
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update application status'
            ], 500);
        }
    }

    /**
     * View application details
     */
    public function show($id)
    {
        try {
            $userId = Auth::id();
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            //
            $application = JobApplication::whereHas('jobListing', function ($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
                ->with([
                    'jobListing:id,title',
                    'user:id,first_name,last_name,email',
                    'reviewedBy:id,first_name,last_name'
                ])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $application->id,
                    'job_title' => $application->jobListing->title,
                    'candidate' => [
                        'name' => $application->user->first_name . ' ' . $application->user->last_name,
                        'email' => $application->user->email,
                    ],
                    'cover_letter' => $application->cover_letter,
                    'answers' => $application->answers,
                    'status' => $application->status,
                    'applied_at' => $application->created_at->format('Y-m-d\TH:i:s\Z'),
                    'reviewed_at' => $application->reviewed_at?->format('Y-m-d\TH:i:s\Z'),
                    'reviewed_by' => $application->reviewedBy ?
                        $application->reviewedBy->first_name . ' ' . $application->reviewedBy->last_name :
                        null,
                    'rejection_reason' => $application->rejection_reason,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve application details'
            ], 500);
        }
    }
}
