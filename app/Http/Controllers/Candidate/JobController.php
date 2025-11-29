<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class JobController extends Controller
{
    /**
     * Display a paginated listing of available jobs with filters.
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
            //
            $validator = Validator::make($request->all(), [
                'search' => 'nullable|string|max:255',
                'employment_type' => ['nullable', Rule::in(JobListing::EMPLOYMENT_TYPES)],
                'work_mode' => ['nullable', Rule::in(JobListing::WORK_MODES)],
                'type' => ['nullable', Rule::in(JobListing::TYPES)],
                'location' => 'nullable|string|max:255',
                'min_salary' => 'nullable|numeric|min:0',
                'experience_level' => 'nullable|string',
                'company_id' => 'nullable|exists:companies,id',
                'featured_only' => 'nullable|boolean',
                'sort_by' => 'nullable|in:latest,salary_min,deadline',
                'remote_regions' => 'nullable|array',
                'remote_regions.*' => 'string',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid filters provided',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = JobListing::active()
                ->with(['company:id,name,logo,industry']);

            // Apply search if provided
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('location', 'like', "%{$searchTerm}%")
                      ->orWhereHas('company', function ($q) use ($searchTerm) {
                          $q->where('name', 'like', "%{$searchTerm}%");
                      });
                });
            }

            // Apply filters
            if ($request->filled('employment_type')) {
                $query->where('employment_type', $request->input('employment_type'));
            }

            if ($request->filled('work_mode')) {
                $query->where('work_mode', $request->input('work_mode'));
            }

            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }

            if ($request->filled('location')) {
                $query->where('location', 'like', "%{$request->input('location')}%");
            }

            if ($request->filled('min_salary')) {
                $query->where('salary_min', '>=', $request->input('min_salary'))
                      ->where('hide_salary', false);
            }

            if ($request->filled('experience_level')) {
                $query->where('experience_level', $request->input('experience_level'));
            }

            if ($request->filled('company_id')) {
                $query->where('company_id', $request->input('company_id'));
            }

            if ($request->boolean('featured_only')) {
                $query->featured();
            }

            if ($request->filled('remote_regions')) {
                $query->where(function ($q) use ($request) {
                    foreach ($request->input('remote_regions') as $region) {
                        $q->orWhereJsonContains('remote_regions', $region);
                    }
                });
            }

            // Apply sorting
            switch ($request->input('sort_by', 'latest')) {
                case 'salary_min':
                    $query->orderBy('salary_min', 'desc');
                    break;
                case 'deadline':
                    $query->orderBy('deadline', 'asc');
                    break;
                default:
                    $query->latest();
            }

            $perPage = $request->input('per_page', 15);
            $jobs = $query->paginate($perPage);

            // Transform the jobs data
            $jobs->getCollection()->transform(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'slug' => $job->slug,
                    'company' => [
                        'id' => $job->company->id,
                        'name' => $job->company->name,
                        'logo' => $job->company->logo,
                        'industry' => $job->company->industry,
                    ],
                    'location' => $job->location,
                    'employment_type' => $job->employment_type,
                    'work_mode' => $job->work_mode,
                    'type' => $job->type,
                    'experience_level' => $job->experience_level,
                    'min_years_experience' => $job->min_years_experience,
                    'salary_range' => $job->getSalaryRange(),
                    'positions_available' => $job->positions_available,
                    'deadline' => $job->deadline?->format('Y-m-d\TH:i:s\Z'),
                    'is_featured' => $job->is_featured,
                    'posted_at' => $job->created_at->format('Y-m-d\TH:i:s\Z'),
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $jobs
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve jobs',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified job listing.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($slug)
    {
        try {
            $userId = Auth::id(); 
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            //
            $job = JobListing::active()
                ->with(['company:id,name,logo,industry,website,description'])
                ->where('slug', $slug)
                ->firstOrFail();

            $response = [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'description' => $job->description,
                'requirements' => $job->requirements,
                'responsibilities' => $job->responsibilities,
                'benefits' => $job->benefits,
                'company' => [
                    'id' => $job->company->id,
                    'name' => $job->company->name,
                    'logo' => $job->company->logo,
                    'industry' => $job->company->industry,
                    'website' => $job->company->website,
                    'description' => $job->company->description,
                ],
                'location' => $job->location,
                'employment_type' => $job->employment_type,
                'work_mode' => $job->work_mode,
                'type' => $job->type,
                'positions_available' => $job->positions_available,
                'experience_level' => $job->experience_level,
                'min_years_experience' => $job->min_years_experience,
                'salary_range' => $job->getSalaryRange(),
                'remote_regions' => $job->remote_regions,
                'deadline' => $job->deadline?->format('Y-m-d\TH:i:s\Z'),
                'reference_code' => $job->reference_code,
                'is_featured' => $job->is_featured,
                'posted_at' => $job->created_at->format('Y-m-d\TH:i:s\Z'),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $response
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
     * Get similar job listings.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function similar($slug)
    {
        try {
            $userId = Auth::id(); 
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            //
            $job = JobListing::where('slug', $slug)->firstOrFail();

            $similarJobs = JobListing::active()
                ->with(['company:id,name,logo,industry'])
                ->where('id', '!=', $job->id)
                ->where(function ($query) use ($job) {
                    $query->where('employment_type', $job->employment_type)
                          ->orWhere('experience_level', $job->experience_level)
                          ->orWhere('location', 'like', "%{$job->location}%");
                })
                ->limit(5)
                ->latest()
                ->get()
                ->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'title' => $job->title,
                        'slug' => $job->slug,
                        'company' => [
                            'name' => $job->company->name,
                            'logo' => $job->company->logo,
                        ],
                        'location' => $job->location,
                        'employment_type' => $job->employment_type,
                        'salary_range' => $job->getSalaryRange(),
                        'posted_at' => $job->created_at->format('Y-m-d\TH:i:s\Z'),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $similarJobs
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job listing not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve similar jobs',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}