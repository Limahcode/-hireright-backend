<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CertificationController extends Controller
{
   
    public function index()
    {
        try {
            $userId = Auth::id();
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            //
            $certifications = $user->certifications()
                ->orderBy('issue_date', 'desc')
                ->get()
                ->map(function ($certification) {
                    return [
                        'id' => $certification->id,
                        'name' => $certification->name,
                        'organization' => $certification->organization,
                        'issue_date' => $certification->issue_date->format('Y-m-d'),
                        'expiration_date' => $certification->expiration_date?->format('Y-m-d'),
                        'has_expiry' => $certification->has_expiry,
                        'is_expired' => $certification->isExpired(),
                        'is_active' => $certification->isActive(),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $certifications
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve certifications',
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
                'name' => 'required|string|max:255',
                'organization' => 'required|string|max:255',
                'issue_date' => [
                    'required',
                    'date',
                    'before_or_equal:today',
                ],
                'expiration_date' => [
                    'nullable',
                    'date',
                    'after:issue_date',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->boolean('has_expiry') && !$value) {
                            $fail('The expiration date is required when the certification has an expiry date.');
                        }
                    },
                ],
                'has_expiry' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $certification = $user->certifications()->create($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Certification added successfully',
                'data' => [
                    'id' => $certification->id,
                    'name' => $certification->name,
                    'organization' => $certification->organization,
                    'issue_date' => $certification->issue_date->format('Y-m-d'),
                    'expiration_date' => $certification->expiration_date?->format('Y-m-d'),
                    'has_expiry' => $certification->has_expiry,
                    'is_expired' => $certification->isExpired(),
                    'is_active' => $certification->isActive(),
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create certification',
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
            $certification = $user->certifications()
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $certification->id,
                    'name' => $certification->name,
                    'organization' => $certification->organization,
                    'issue_date' => $certification->issue_date->format('Y-m-d'),
                    'expiration_date' => $certification->expiration_date?->format('Y-m-d'),
                    'has_expiry' => $certification->has_expiry,
                    'is_expired' => $certification->isExpired(),
                    'is_active' => $certification->isActive(),
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certification not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve certification',
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
            $certification = $user->certifications()
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'organization' => 'sometimes|required|string|max:255',
                'issue_date' => [
                    'sometimes',
                    'required',
                    'date',
                    'before_or_equal:today',
                ],
                'expiration_date' => [
                    'nullable',
                    'date',
                    'after:issue_date',
                    function ($attribute, $value, $fail) use ($request, $certification) {
                        $hasExpiry = $request->has('has_expiry') ?
                            $request->boolean('has_expiry') :
                            $certification->has_expiry;

                        if ($hasExpiry && !$value) {
                            $fail('The expiration date is required when the certification has an expiry date.');
                        }
                    },
                ],
                'has_expiry' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $certification->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Certification updated successfully',
                'data' => [
                    'id' => $certification->id,
                    'name' => $certification->name,
                    'organization' => $certification->organization,
                    'issue_date' => $certification->issue_date->format('Y-m-d'),
                    'expiration_date' => $certification->expiration_date?->format('Y-m-d'),
                    'has_expiry' => $certification->has_expiry,
                    'is_expired' => $certification->isExpired(),
                    'is_active' => $certification->isActive(),
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certification not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update certification',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function batchStore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'certifications' => 'required|array|min:1',
                'certifications.*.name' => 'required|string|max:255',
                'certifications.*.organization' => 'required|string|max:255',
                'certifications.*.issue_date' => [
                    'required',
                    'date',
                    'before_or_equal:today',
                ],
                'certifications.*.expiration_date' => [
                    'nullable',
                    'date',
                    'after:certifications.*.issue_date',
                ],
                'certifications.*.has_expiry' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $records = collect($request->certifications)->map(function ($item) {
                return array_merge($item, ['user_id' => Auth::id()]);
            });

            Certification::insert($records->toArray());

            return response()->json([
                'status' => 'success',
                'message' => 'Certification records created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create certification records',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }


    public function batchUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'certifications' => 'required|array|min:1',
                'certifications.*.id' => 'required|exists:certifications,id',
                'certifications.*.name' => 'sometimes|required|string|max:255',
                'certifications.*.organization' => 'sometimes|required|string|max:255',
                'certifications.*.issue_date' => [
                    'sometimes',
                    'required',
                    'date',
                    'before_or_equal:today',
                ],
                'certifications.*.expiration_date' => [
                    'nullable',
                    'date',
                    'after:certifications.*.issue_date',
                ],
                'certifications.*.has_expiry' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            foreach ($request->certifications as $item) {
                $certification = Certification::where('user_id', Auth::id())
                    ->findOrFail($item['id']);

                $certification->update($item);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Certification records updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update certification records',
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
            $certification = $user->certifications()
                ->findOrFail($id);

            $certification->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Certification deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certification not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete certification',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
