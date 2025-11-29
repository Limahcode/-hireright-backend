<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyStaff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CompanyStaffController extends Controller
{
    /**
     * Default permissions for staff members
     */
    protected const DEFAULT_PERMISSIONS = [
        'view_jobs',
        'manage_applications'
    ];

    /**
     * Admin permissions
     */
    protected const ADMIN_PERMISSIONS = [
        'view_jobs',
        'manage_jobs',
        'manage_applications',
        'manage_staff',
        'manage_company',
    ];

    /**
     * Display a listing of company staff.
     */
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            $user = User::findOrFail($userId);
            if (!$user->company_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please add a company to your account.',
                    'error' => ''
                ], 412);
            }
            //
            $query = CompanyStaff::where('company_id', $user->company_id)
                ->with('user:id,first_name,last_name,email')
                ->when($request->input('status'), function ($q, $status) {
                    return $q->where('status', $status);
                })
                ->when($request->input('department'), function ($q, $department) {
                    return $q->where('department', $department);
                });

            $staff = $query->get()->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->user->first_name . ' ' . $staff->user->last_name,
                    'email' => $staff->user->email,
                    'job_title' => $staff->job_title,
                    'department' => $staff->department,
                    'is_admin' => $staff->is_admin,
                    'status' => $staff->status,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $staff
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve staff list'
            ], 500);
        }
    }

    /**
     * Invite a new staff member.
     */
    public function invite(Request $request)
    {
        try {
            $userId = Auth::id();
            // Re-fetch the user using the User model
            $user = User::findOrFail($userId);
            //
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'job_title' => 'required|string|max:255',
                'department' => 'nullable|string|max:255',
                'is_admin' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if user already exists
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // Create new user with temporary password
                $tempPassword = Str::random(12);
                $user = User::create([
                    'email' => $request->email,
                    'password' => Hash::make($tempPassword),
                    'first_name' => explode('@', $request->email)[0], // Temporary name
                    'last_name' => '',
                ]);
            }

            // Check if user is already a staff member
            $existingStaff = CompanyStaff::where('company_id', Auth::user()->company_id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingStaff) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User is already a staff member'
                ], 422);
            }

            // Create staff record
            $staff = CompanyStaff::create([
                'company_id' => Auth::user()->company_id,
                'user_id' => $user->id,
                'job_title' => $request->job_title,
                'department' => $request->department,
                'is_admin' => $request->input('is_admin', false),
                'permissions' => $request->input('is_admin', false) ? self::ADMIN_PERMISSIONS : self::DEFAULT_PERMISSIONS,
                'status' => 'active'
            ]);

            // Send invitation email
            // Mail::to($request->email)->send(new \App\Mail\StaffInvitation(
            //     Auth::user()->company,
            //     $user,
            //     isset($tempPassword) ? $tempPassword : null
            // ));

            return response()->json([
                'status' => 'success',
                'message' => 'Staff invitation sent successfully',
                'data' => $staff
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send invitation'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            //
            $staff = CompanyStaff::where('company_id', $user->company_id)
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'job_title' => 'sometimes|required|string|max:255',
                'department' => 'nullable|string|max:255',
                'is_admin' => 'boolean',
                'status' => 'in:active,inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $staff->update($validator->validated());

            // Update permissions if admin status changes
            if ($request->has('is_admin')) {
                $staff->permissions = $request->is_admin ? self::ADMIN_PERMISSIONS : self::DEFAULT_PERMISSIONS;
                $staff->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Staff member updated successfully',
                'data' => $staff
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update staff member'
            ], 500);
        }
    }

    public function updatePermissions(Request $request, $staffId)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            //
            $staff = CompanyStaff::where('company_id', $user->company_id)
                ->findOrFail($staffId);

            // Update permissions if admin status changes
            if ($request->has('is_admin')) {
                $staff->permissions = $request->is_admin ? self::ADMIN_PERMISSIONS : self::DEFAULT_PERMISSIONS;
                $staff->save();
            } else {
                //
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Staff member updated successfully',
                'data' => $staff
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update staff member'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $userId = Auth::id();
            $user = User::findOrFail($userId);
            //
            $staff = CompanyStaff::where('company_id', $user->company_id)
                ->findOrFail($id);

            $staff->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Staff member removed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove staff member'
            ], 500);
        }
    }
}
