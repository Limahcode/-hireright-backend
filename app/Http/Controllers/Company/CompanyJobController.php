<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyJobController extends Controller
{
    /**
     * Display a listing of the company's job listings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            if (!$user->company_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please add a company to your account.',
                    'error' => ''
                ], 412);
            }
            //
            $validator = Validator::make($request->all(), [
                'status' => ['nullable', Rule::in(JobListing::STATUSES)],
                'employment_type' => ['nullable', Rule::in(JobListing::EMPLOYMENT_TYPES)],
                'sort_by' => 'nullable|in:latest,deadline,responses',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid filters provided',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = JobListing::where('company_id', $user->company_id)
                ->when($request->filled('status'), function ($q) use ($request) {
                    $q->where('status', $request->input('status'));
                })
                ->when($request->filled('employment_type'), function ($q) use ($request) {
                    $q->where('employment_type', $request->input('employment_type'));
                });

            // Apply sorting
            switch ($request->input('sort_by', 'latest')) {
                case 'deadline':
                    $query->orderBy('deadline');
                    break;
                default:
                    $query->latest();
            }

            $jobs = $query->paginate($request->input('per_page', 15));

            return response()->json([
                'status' => 'success',
                'data' => $jobs
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve job listings',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a newly created job listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            if (!$user->company_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please add a company to your account.',
                    'error' => ''
                ], 412);
            }
            //
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'requirements' => 'required|string',
                'responsibilities' => 'required|string',
                'benefits' => 'nullable|string',
                'employment_type' => ['required', Rule::in(JobListing::EMPLOYMENT_TYPES)],
                'work_mode' => ['required', Rule::in(JobListing::WORK_MODES)],
                'positions_available' => 'required|integer|min:1',
                'experience_level' => 'required|string',
                'min_years_experience' => 'required|integer|min:0',
                'salary_min' => 'nullable|numeric|min:0',
                'salary_max' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value && $request->input('salary_min') && $value < $request->input('salary_min')) {
                            $fail('Maximum salary must be greater than minimum salary.');
                        }
                    },
                ],
                'salary_currency' => 'required|string|size:3',
                'hide_salary' => 'boolean',
                'location' => 'required|string|max:255',
                'remote_regions' => 'nullable|array',
                'remote_regions.*' => 'string',
                'deadline' => 'nullable|date|after:today',
                'is_featured' => 'boolean',
                'is_published' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['company_id'] = $user->company_id;
            $data['created_by'] = $user->id;
            $data['slug'] = Str::slug($data['title']) . '-' . Str::random(8);
            $data['reference_code'] = 'JOB-' . strtoupper(Str::random(8)) . '-' .strtoupper(Str::random(4));
            $data['status'] = $data['is_published'] ? 'published' : 'draft';

            $job = JobListing::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Job listing created successfully',
                'data' => $job
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create job listing',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified job listing.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            if (!$user->company_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please add a company to your account.',
                    'error' => ''
                ], 412);
            }
            //
            $job = JobListing::where('company_id', $user->company_id)
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $job
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job listing not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve job listing',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified job listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            if (!$user->company_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please add a company to your account.',
                    'error' => ''
                ], 412);
            }
            //
            $job = JobListing::where('company_id', $user->company_id)
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'requirements' => 'sometimes|required|string',
                'responsibilities' => 'sometimes|required|string',
                'benefits' => 'nullable|string',
                'employment_type' => ['sometimes', 'required', Rule::in(JobListing::EMPLOYMENT_TYPES)],
                'work_mode' => ['sometimes', 'required', Rule::in(JobListing::WORK_MODES)],
                'positions_available' => 'sometimes|required|integer|min:1',
                'experience_level' => 'sometimes|required|string',
                'min_years_experience' => 'sometimes|required|integer|min:0',
                'salary_min' => 'nullable|numeric|min:0',
                'salary_max' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request, $job) {
                        $minSalary = $request->input('salary_min', $job->salary_min);
                        if ($value && $minSalary && $value < $minSalary) {
                            $fail('Maximum salary must be greater than minimum salary.');
                        }
                    },
                ],
                'salary_currency' => 'sometimes|required|string|size:3',
                'hide_salary' => 'boolean',
                'location' => 'sometimes|required|string|max:255',
                'remote_regions' => 'nullable|array',
                'remote_regions.*' => 'string',
                'deadline' => 'nullable|date|after:today',
                'is_featured' => 'boolean',
                'is_published' => 'boolean',
                'status' => ['sometimes', 'required', Rule::in(JobListing::STATUSES)],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Update slug if title changes
            if (isset($data['title']) && $data['title'] !== $job->title) {
                $data['slug'] = Str::slug($data['title']) . '-' . Str::random(8);
            }

            // Update status based on is_published if provided
            if (isset($data['is_published'])) {
                $data['status'] = $data['is_published'] ? 'published' : 'draft';
            }

            $job->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Job listing updated successfully',
                'data' => $job
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job listing not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update job listing',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified job listing.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            if (!$user->company_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please add a company to your account.',
                    'error' => ''
                ], 412);
            }
            //
            $job = JobListing::where('company_id', $user->company_id)
                ->findOrFail($id);

            $job->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Job listing deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job listing not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete job listing',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the status of a job listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            if (!$user->company_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please add a company to your account.',
                    'error' => ''
                ], 412);
            }
            //
            $job = JobListing::where('company_id', $user->company_id)
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status' => ['required', Rule::in(JobListing::STATUSES)],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $job->update([
                'status' => $request->input('status'),
                'is_published' => $request->input('status') === 'published'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Job status updated successfully',
                'data' => $job
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job listing not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update job status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
