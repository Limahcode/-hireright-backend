<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ExperienceController extends Controller
{
   
    public function index()
    {
        try {
            $userId = Auth::id();
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);

            $experiences = $user->experiences()
                ->orderBy('is_current', 'desc')
                ->orderBy('end_date', 'desc')
                ->orderBy('start_date', 'desc')
                ->get()
                ->map(function ($experience) {
                    return [
                        'id' => $experience->id,
                        'company_name' => $experience->company_name,
                        'job_title' => $experience->job_title,
                        'description' => $experience->description,
                        'location' => $experience->location,
                        'employment_type' => $experience->employment_type,
                        'duration' => $experience->getDuration(),
                        'duration_months' => $experience->getDurationInMonths(),
                        'start_date' => $experience->start_date->format('Y-m-d'),
                        'end_date' => $experience->end_date ? $experience->end_date->format('Y-m-d') : null,
                        'is_current' => $experience->is_current,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $experiences
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve experiences',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $userId = Auth::id();
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);

            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'job_title' => 'required|string|max:255',
                'description' => 'nullable|string|max:5000',
                'location' => 'nullable|string|max:255',
                'employment_type' => ['required', Rule::in(Experience::EMPLOYMENT_TYPES)],
                'start_date' => [
                    'required',
                    'date',
                    'before_or_equal:today',
                    'before_or_equal:end_date',
                ],
                'end_date' => [
                    'nullable',
                    'date',
                    'before_or_equal:today',
                    Rule::requiredIf(!$request->boolean('is_current')),
                ],
                'is_current' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            //
            $experience = $user->experiences()->create($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Experience added successfully',
                'data' => $experience
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create experience',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $userId = Auth::id();
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            //
            $experience = $user->experiences()
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $experience->id,
                    'company_name' => $experience->company_name,
                    'job_title' => $experience->job_title,
                    'description' => $experience->description,
                    'location' => $experience->location,
                    'employment_type' => $experience->employment_type,
                    'duration' => $experience->getDuration(),
                    'duration_months' => $experience->getDurationInMonths(),
                    'start_date' => $experience->start_date->format('Y-m-d'),
                    'end_date' => $experience->end_date ? $experience->end_date->format('Y-m-d') : null,
                    'is_current' => $experience->is_current,
                ]
            ], 200);
            //
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Experience not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve experience',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            //
            $experience = $user->experiences()
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'company_name' => 'sometimes|required|string|max:255',
                'job_title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:5000',
                'location' => 'nullable|string|max:255',
                'employment_type' => ['sometimes', 'required', Rule::in(Experience::EMPLOYMENT_TYPES)],
                'start_date' => [
                    'sometimes',
                    'required',
                    'date',
                    'before_or_equal:today',
                    'before_or_equal:end_date',
                ],
                'end_date' => [
                    'nullable',
                    'date',
                    'before_or_equal:today',
                    Rule::requiredIf(function () use ($request, $experience) {
                        return !$request->boolean('is_current', $experience->is_current);
                    }),
                ],
                'is_current' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            //
            $experience->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Experience updated successfully',
                'data' => $experience
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Experience not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update experience',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function batchStore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'experiences' => 'required|array|min:1',
                'experiences.*.company_name' => 'required|string|max:255',
                'experiences.*.job_title' => 'required|string|max:255',
                'experiences.*.description' => 'nullable|string|max:5000',
                'experiences.*.location' => 'nullable|string|max:255',
                'experiences.*.employment_type' => ['required', Rule::in(Experience::EMPLOYMENT_TYPES)],
                'experiences.*.start_date' => [
                    'required',
                    'date',
                    'before_or_equal:today',
                ],
                'experiences.*.end_date' => [
                    'nullable',
                    'date',
                    'before_or_equal:today',
                    'after_or_equal:experiences.*.start_date',
                ],
                'experiences.*.is_current' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $records = collect($request->experiences)->map(function ($item) {
                return array_merge($item, ['user_id' => Auth::id()]);
            });

            Experience::insert($records->toArray());

            return response()->json([
                'status' => 'success',
                'message' => 'Experience records created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create experience records',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function batchUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'experiences' => 'required|array|min:1',
                'experiences.*.id' => 'required|exists:experiences,id',
                'experiences.*.company_name' => 'sometimes|required|string|max:255',
                'experiences.*.job_title' => 'sometimes|required|string|max:255',
                'experiences.*.description' => 'nullable|string|max:5000',
                'experiences.*.location' => 'nullable|string|max:255',
                'experiences.*.employment_type' => ['sometimes', 'required', Rule::in(Experience::EMPLOYMENT_TYPES)],
                'experiences.*.start_date' => [
                    'sometimes',
                    'required',
                    'date',
                    'before_or_equal:today',
                ],
                'experiences.*.end_date' => [
                    'nullable',
                    'date',
                    'before_or_equal:today',
                    'after_or_equal:experiences.*.start_date',
                ],
                'experiences.*.is_current' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            foreach ($request->experiences as $item) {
                $experience = Experience::where('user_id', Auth::id())
                    ->findOrFail($item['id']);

                $experience->update($item);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Experience records updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update experience records',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $userId = Auth::id();
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            //
            $experience = $user->experiences()
                ->findOrFail($id);

            $experience->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Experience deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Experience not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete experience',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
