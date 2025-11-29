<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Education;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EducationController extends Controller
{
   
    public function index()
    {
        try {
            $userId = Auth::id();
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            //
            $education = $user->education()
                ->orderBy('is_current', 'desc')
                ->orderBy('end_date', 'desc')
                ->orderBy('start_date', 'desc')
                ->get()
                ->map(function ($education) {
                    return [
                        'id' => $education->id,
                        'institution' => $education->institution,
                        'degree' => $education->degree,
                        'field_of_study' => $education->field_of_study,
                        'location' => $education->location,
                        'duration' => $education->getDuration(),
                        'start_date' => $education->start_date->format('Y-m-d'),
                        'end_date' => $education->end_date ? $education->end_date->format('Y-m-d') : null,
                        'is_current' => $education->is_current,
                        'activities' => $education->activities,
                        'description' => $education->description,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $education
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve education records',
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
            //
            $validator = Validator::make($request->all(), [
                'institution' => 'required|string|max:255',
                'degree' => 'required|string|max:255',
                'field_of_study' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'start_date' => [
                    'required',
                    'date',
                    'before_or_equal:today',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->end_date && $value > $request->end_date) {
                            $fail('The start date must be before or equal to the end date.');
                        }
                    },
                ],
                'end_date' => [
                    'nullable',
                    'date',
                    'before_or_equal:today',
                    function ($attribute, $value, $fail) use ($request) {
                        if (!$request->boolean('is_current') && !$value) {
                            $fail('The end date is required when not currently studying.');
                        }
                    },
                ],
                'is_current' => 'boolean',
                'activities' => 'nullable|string|max:2000',
                'description' => 'nullable|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $education = $user->education()->create($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Education record added successfully',
                'data' => $education
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create education record',
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
            $education = $user->education()
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $education->id,
                    'institution' => $education->institution,
                    'degree' => $education->degree,
                    'field_of_study' => $education->field_of_study,
                    'location' => $education->location,
                    'duration' => $education->getDuration(),
                    'start_date' => $education->start_date->format('Y-m-d'),
                    'end_date' => $education->end_date ? $education->end_date->format('Y-m-d') : null,
                    'is_current' => $education->is_current,
                    'activities' => $education->activities,
                    'description' => $education->description,
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Education record not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve education record',
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
            $education = $user->education()
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'institution' => 'sometimes|required|string|max:255',
                'degree' => 'sometimes|required|string|max:255',
                'field_of_study' => 'sometimes|required|string|max:255',
                'location' => 'nullable|string|max:255',
                'start_date' => [
                    'sometimes',
                    'required',
                    'date',
                    'before_or_equal:today',
                    function ($attribute, $value, $fail) use ($request, $education) {
                        $endDate = $request->end_date ?? $education->end_date;
                        if ($endDate && $value > $endDate) {
                            $fail('The start date must be before or equal to the end date.');
                        }
                    },
                ],
                'end_date' => [
                    'nullable',
                    'date',
                    'before_or_equal:today',
                    function ($attribute, $value, $fail) use ($request, $education) {
                        $isCurrent = $request->has('is_current') ?
                            $request->boolean('is_current') :
                            $education->is_current;

                        if (!$isCurrent && !$value) {
                            $fail('The end date is required when not currently studying.');
                        }
                    },
                ],
                'is_current' => 'boolean',
                'activities' => 'nullable|string|max:2000',
                'description' => 'nullable|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $education->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Education record updated successfully',
                'data' => $education
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Education record not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update education record',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function batchStore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'education' => 'required|array|min:1',
                'education.*.institution' => 'required|string|max:255',
                'education.*.degree' => 'required|string|max:255',
                'education.*.field_of_study' => 'required|string|max:255',
                'education.*.location' => 'nullable|string|max:255',
                'education.*.start_date' => [
                    'required',
                    'date',
                    'before_or_equal:today',
                ],
                'education.*.end_date' => [
                    'nullable',
                    'date',
                    'before_or_equal:today',
                    'after_or_equal:education.*.start_date',
                ],
                'education.*.is_current' => 'boolean',
                'education.*.activities' => 'nullable|string|max:2000',
                'education.*.description' => 'nullable|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $records = collect($request->education)->map(function ($item) {
                return array_merge($item, ['user_id' => Auth::id()]);
            });

            Education::insert($records->toArray());

            return response()->json([
                'status' => 'success',
                'message' => 'Education records created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create education records',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function batchUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'education' => 'required|array|min:1',
                'education.*.id' => 'required|exists:education,id',
                'education.*.institution' => 'sometimes|required|string|max:255',
                'education.*.degree' => 'sometimes|required|string|max:255',
                'education.*.field_of_study' => 'sometimes|required|string|max:255',
                'education.*.location' => 'nullable|string|max:255',
                'education.*.start_date' => [
                    'sometimes',
                    'required',
                    'date',
                    'before_or_equal:today',
                ],
                'education.*.end_date' => [
                    'nullable',
                    'date',
                    'before_or_equal:today',
                    'after_or_equal:education.*.start_date',
                ],
                'education.*.is_current' => 'boolean',
                'education.*.activities' => 'nullable|string|max:2000',
                'education.*.description' => 'nullable|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            foreach ($request->education as $item) {
                $education = Education::where('user_id', Auth::id())
                    ->findOrFail($item['id']);

                $education->update($item);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Education records updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update education records',
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
            $education = $user->education()->findOrFail($id);

            $education->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Education record deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Education record not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete education record',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
