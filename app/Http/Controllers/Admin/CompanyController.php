<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * Display a paginated listing of companies with filters.
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
                'industry_code' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'verified_only' => 'nullable|boolean',
                'featured_only' => 'nullable|boolean',
                'has_active_jobs' => 'nullable|boolean',
                'country' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'sort_by' => 'nullable|in:name,newest,size',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid filters provided',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Company::active()->withCount(['activeJobs']);

            // Apply search if provided
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('about', 'like', "%{$searchTerm}%")
                      ->orWhere('industry_code', 'like', "%{$searchTerm}%");
                });
            }

            // Apply filters
            if ($request->filled('industry_code')) {
                $query->where('industry_code', $request->input('industry_code'));
            }

            if ($request->filled('location')) {
                $location = $request->input('location');
                $query->where(function ($q) use ($location) {
                    $q->where('city', 'like', "%{$location}%")
                      ->orWhere('state', 'like', "%{$location}%")
                      ->orWhere('country', 'like', "%{$location}%");
                });
            }

            if ($request->boolean('verified_only')) {
                $query->verified();
            }

            if ($request->boolean('featured_only')) {
                $query->featured();
            }

            if ($request->boolean('has_active_jobs')) {
                $query->has('activeJobs');
            }

            if ($request->filled('country')) {
                $query->where('country', $request->input('country'));
            }

            if ($request->filled('state')) {
                $query->where('state', $request->input('state'));
            }

            if ($request->filled('city')) {
                $query->where('city', $request->input('city'));
            }

            // Apply sorting
            switch ($request->input('sort_by', 'name')) {
                case 'newest':
                    $query->latest();
                    break;
                case 'size':
                    $query->orderBy('size_max', 'desc');
                    break;
                default:
                    $query->orderBy('name');
            }

            $perPage = $request->input('per_page', 15);
            $companies = $query->paginate($perPage);

            // Transform the companies data
            $companies->getCollection()->transform(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'slug' => $company->slug,
                    'about' => $company->about,
                    'website' => $company->website,
                    'industry_code' => $company->industry_code,
                    'location' => [
                        'city' => $company->city,
                        'state' => $company->state,
                        'country' => $company->country,
                        'full_address' => $company->getFullAddress(),
                    ],
                    'size_range' => $company->getSizeRange(),
                    'is_verified' => $company->is_verified,
                    'is_featured' => $company->is_featured,
                    'active_jobs_count' => $company->active_jobs_count,
                    'social_links' => $company->getActiveSocialLinks(),
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $companies
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve companies',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified company.
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
            $company = Company::active()
                ->withCount(['activeJobs'])
                ->with(['activeJobs' => function ($query) {
                    $query->latest()->limit(5);
                }])
                ->where('slug', $slug)
                ->firstOrFail();

            $response = [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
                'email' => $company->email,
                'phone' => $company->phone,
                'about' => $company->about,
                'website' => $company->website,
                'location' => [
                    'address' => $company->address,
                    'city' => $company->city,
                    'state' => $company->state,
                    'country' => $company->country,
                    'postal_code' => $company->postal_code,
                    'full_address' => $company->getFullAddress(),
                ],
                'size_range' => $company->getSizeRange(),
                'industry_code' => $company->industry_code,
                'is_verified' => $company->is_verified,
                'is_featured' => $company->is_featured,
                'social_links' => $company->getActiveSocialLinks(),
                'active_jobs_count' => $company->active_jobs_count,
                'recent_jobs' => $company->activeJobs->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'title' => $job->title,
                        'slug' => $job->slug,
                        'employment_type' => $job->employment_type,
                        'location' => $job->location,
                        'salary_range' => $job->getSalaryRange(),
                        'posted_at' => $job->created_at->format('Y-m-d\TH:i:s\Z'),
                    ];
                }),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $response
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Company not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve company',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all jobs for a specific company.
     *
     * @param  string  $slug
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function jobs($slug, Request $request)
    {
        try {
            $userId = Auth::id(); 
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            //
            $company = Company::active()
                ->where('slug', $slug)
                ->firstOrFail();

            $validator = Validator::make($request->all(), [
                'employment_type' => 'nullable|in:full_time,part_time,contract',
                'work_mode' => 'nullable|in:remote,hybrid,onsite',
                'sort_by' => 'nullable|in:latest,deadline',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid filters provided',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = $company->activeJobs();

            if ($request->filled('employment_type')) {
                $query->where('employment_type', $request->input('employment_type'));
            }

            if ($request->filled('work_mode')) {
                $query->where('work_mode', $request->input('work_mode'));
            }

            // Apply sorting
            if ($request->input('sort_by') === 'deadline') {
                $query->orderBy('deadline');
            } else {
                $query->latest();
            }

            $jobs = $query->paginate($request->input('per_page', 15));

            // Transform the jobs data
            $jobs->getCollection()->transform(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'slug' => $job->slug,
                    'employment_type' => $job->employment_type,
                    'work_mode' => $job->work_mode,
                    'location' => $job->location,
                    'experience_level' => $job->experience_level,
                    'salary_range' => $job->getSalaryRange(),
                    'deadline' => $job->deadline?->format('Y-m-d\TH:i:s\Z'),
                    'posted_at' => $job->created_at->format('Y-m-d\TH:i:s\Z'),
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $jobs
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Company not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve company jobs',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}