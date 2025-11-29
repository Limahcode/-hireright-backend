<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyStaff;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CompanyController extends Controller
{

    public function dashboard(Request $request)
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
            // Get the total number of job listings for the company
            $totalJobs = JobListing::where('company_id', $user->company_id)->count();
            // Get the 10 latest job listings for the company
            $latestJobs = JobListing::where('company_id', $user->company_id)
                ->latest()
                ->take(10)
                ->get();


            // Return the stats as a JSON response
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_jobs' => $totalJobs,
                    'total_applicants' => 0,
                    'total_tests' => 0,
                    'latest_jobs' => $latestJobs,
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


    public function store(Request $request)
    {
        // Check if user already has a company (either as owner or staff)
        $userId = Auth::id();
        $user = User::findOrFail($userId);
        //
        $staffRecord = CompanyStaff::where('user_id', $user->id)
            ->where('company_id', $user->company_id)
            ->where('status', 'active')
            ->first();
        if ($staffRecord) {
            return response()->json([
                'message' => 'Your account already tied to a company.'
            ], 412);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'about' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:50',
            'size_min' => 'nullable|integer|min:1',
            'size_max' => 'nullable|integer|gt:size_min',
            'industry_code' => 'nullable|string|max:50',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            // Create company with owner
            $company = Company::create(array_merge(
                $validator->validated(),
                [
                    'slug' => $this->generateUniqueSlug($request->name),
                    'owner_id' => $user->id,
                    'status' => 'active',
                    'is_verified' => false,
                    'is_featured' => false
                ]
            ));

            // Create CompanyStaff record for owner
            CompanyStaff::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'job_title' => $user->title ?? 'Owner',
                'department' => $request->department ?? 'Management',
                'is_admin' => true,
                'permissions' => ['*'], // Full permissions
                'status' => 'active',
                'notification_preferences' => [
                    'email' => true,
                    'push' => true,
                    'in_app' => true
                ]
            ]);

            //
            $user->company_id = $company->id;
            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'Company added successfully',
                'data' => $company
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create company',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $userId = Auth::id();
        $user = User::findOrFail($userId);
        //
        $company = Company::where('id', $user->company_id)->first();
        if (!$company) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please add a company to your account.',
                'error' => ''
            ], 412);
        }
        // Check if user is authorized (either owner or admin staff)
        $isOwner = $company->owner_id === $user->id;
        $isAdminStaff = CompanyStaff::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('is_admin', true)
            ->where('status', 'active')
            ->exists();

        if (!$isOwner && !$isAdminStaff) {
            return response()->json([
                'message' => 'Unauthorized to update company information'
            ], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'about' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:50',
            'size_min' => 'nullable|integer|min:1',
            'size_max' => 'nullable|integer|gt:size_min',
            'industry_code' => 'nullable|string|max:50',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            // Update slug if name changes
            if ($request->has('name') && $request->name !== $company->name) {
                $company->slug = $this->generateUniqueSlug($request->name);
            }
            // Update company
            $company->update($validator->validated());

            DB::commit();

            return response()->json([
                'message' => 'Company updated successfully',
                'data' => $company->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company update failed: ' . $e->getMessage(), [
                'company_id' => $company->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'message' => 'Failed to update company',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            //
            $company = Company::where('id', $user->company_id)->first();
            if (!$company) {
                return null;
            }
            // Check if user is associated with the company
            $userCompanyStaff = CompanyStaff::where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            $isOwner = $company->owner_id === $user->id;

            // If user is neither owner nor staff member, return basic public information only
            if (!$isOwner && !$userCompanyStaff) {
                return response()->json([
                    'message' => 'Access Denied. You do not have access to this resource.'
                ], 403);
            }
            //
            return response()->json([
                'data' => [
                    // Basic company information
                    'id' => $company->id,
                    'name' => $company->name,
                    'slug' => $company->slug,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'about' => $company->about,
                    'website' => $company->website,
                    'industry_code' => $company->industry_code,

                    // Address information
                    'address' => $company->address,
                    'city' => $company->city,
                    'state' => $company->state,
                    'country' => $company->country,
                    'postal_code' => $company->postal_code,
                    'full_address' => $company->getFullAddress(),

                    // Company size
                    'size_min' => $company->size_min,
                    'size_max' => $company->size_max,
                    'size_range' => $company->getSizeRange(),

                    // Status and verification
                    'status' => $company->status,
                    'is_verified' => $company->is_verified,
                    'is_featured' => $company->is_featured,

                    // Social media
                    'social_links' => $company->getActiveSocialLinks(),

                    // Timestamps
                    'created_at' => $company->created_at,
                    'updated_at' => $company->updated_at,

                    // Related data
                    'owner' => $company->owner,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving company details: ' . $e->getMessage(), [
                'company_id' => $company->id,
                'user_id' => $user->id
            ]);


            return response()->json([
                'message' => 'Failed to retrieve company details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $baseSlug = $slug;
        $counter = 1;

        while (Company::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
