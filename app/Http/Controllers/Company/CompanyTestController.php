<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Test;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CompanyTestController extends Controller
{
    /**
     * Display a listing of company tests.
     */
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            
            if (!$user->company_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please add a company to your account.',
                ], 412);
            }

            $tests = Test::where('creator_type', Company::class)
                ->where('creator_id', $user->company_id)
                ->when($request->filled('is_active'), function ($q) use ($request) {
                    $q->where('is_active', $request->boolean('is_active'));
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return response()->json([
                'status' => 'success',
                'data' => $tests
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve tests',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a newly created test.
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
                ], 412);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'instructions' => 'nullable|string',
                'time_limit' => 'required|integer|min:0',
                'passing_score' => 'required|numeric|min:0|max:100',
                'is_active' => 'boolean',
                'submission_type' => ['required', Rule::in(['online', 'document_upload', 'both'])],
                'visibility_type' => ['required', Rule::in(['view_before_start', 'hidden_until_start'])],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $test = Test::create([
                'title' => $request->title,
                'description' => $request->description,
                'instructions' => $request->instructions,
                'creator_type' => Company::class,
                'creator_id' => $user->company_id,
                'time_limit' => $request->time_limit,
                'passing_score' => $request->passing_score,
                'is_active' => $request->input('is_active', true),
                'submission_type' => $request->submission_type,
                'visibility_type' => $request->visibility_type,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Test created successfully',
                'data' => $test
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create test',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified test.
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
                ], 412);
            }

            $test = Test::where('creator_type', Company::class)
                ->where('creator_id', $user->company_id)
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $test
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Test not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve test',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified test.
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
                ], 412);
            }

            $test = Test::where('creator_type', Company::class)
                ->where('creator_id', $user->company_id)
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'instructions' => 'nullable|string',
                'time_limit' => 'sometimes|required|integer|min:0',
                'passing_score' => 'sometimes|required|numeric|min:0|max:100',
                'is_active' => 'boolean',
                'submission_type' => ['sometimes', Rule::in(['online', 'document_upload', 'both'])],
                'visibility_type' => ['sometimes', Rule::in(['view_before_start', 'hidden_until_start'])],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $test->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Test updated successfully',
                'data' => $test
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Test not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update test',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified test.
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
                ], 412);
            }

            $test = Test::where('creator_type', Company::class)
                ->where('creator_id', $user->company_id)
                ->findOrFail($id);

            $test->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Test deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Test not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete test',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
// ```

// ---

// ## 🧪 **TEST IT NOW:**

// **POST** `https://appealing-perception-production.up.railway.app/api/employers/tests`

// **Headers:**
// ```
// Authorization: Bearer <employer_token>
// Accept: application/json
// Content-Type: application/json