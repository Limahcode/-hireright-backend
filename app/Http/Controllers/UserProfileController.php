<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Notification;

class UserProfileController extends Controller
{
   
    public function viewProfile(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
        // Build the profile response
        $profile = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'phone_2' => $user->phone_2,
            'referral_code' => $user->referral_code,
            'status' => $user->status,
            'dob' => $user->dob,
            'email_verified' => $user->email_verified,
            'phone_verified' => $user->phone_verified,
            'last_seen' => $user->last_seen,
            'address' => $user->address,
            'website' => $user->website,
            'bio' => $user->bio,
            'title' => $user->title,
            'cover_letter' => $user->cover_letter,
        ];

        return response()->json($profile, 200);
    }

    public function updateProfile(Request $request)
    {
        // Get the authenticated user's ID
        $userId = Auth::id(); 
        // Re-fetch the user using the User model
        $user = User::findOrFail($userId); 
        // Define validation rules for profile update
        $rules = [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'phone_2' => 'sometimes|nullable|string|max:20',
            'firebase_device_token' => 'sometimes|nullable|string',
            'dob' => 'sometimes|nullable|date',
            'address' => 'sometimes|nullable|string|max:500',
            'website' => 'sometimes|nullable|url|max:255',
            'bio' => 'sometimes|nullable|string|max:1000',
            'title' => 'sometimes|nullable|string|max:255',
            'cover_letter' => 'sometimes|nullable|string|max:5000',
        ];
        // Validate the request data
        $validator = Validator::make($request->all(), $rules);
        // Return validation errors, if any
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // List of fields that can be updated
        $updatableFields = [
            'first_name',
            'last_name',
            'phone',
            'phone_2',
            'firebase_device_token',
            'dob',
            'address',
            'website',
            'bio',
            'title',
            'cover_letter',
        ];

        // Update only the fields that are present in the request
        foreach ($updatableFields as $field) {
            if ($request->has($field)) {
                $user->{$field} = $request->input($field);
            }
        }

        // Persist the changes
        $user->save();
        // Return success response with the updated user info
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->makeVisible([
                'first_name',
                'last_name',
                'email',
                'phone',
                'phone_2',
                'status',
                'dob',
                'email_verified',
                'phone_verified',
                'last_seen',
                'address',
                'website',
                'bio',
                'title',
                'cover_letter',
            ]),
        ], 200);
    }

    public function getNotifications(Request $request)
    {
        $user = Auth::user();
        // Filter notifications based on status, default to 'active'
        $status = $request->query('status', 'active');
        // Ensure status is either 'active' or 'archived'; otherwise, default to 'active'
        if (!in_array($status, ['active', 'archived'])) {
            $status = 'active';
        }
        // 
        $notifications = Notification::where('user_id', $user->id)
            ->where('status', $status)
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'count' => $notifications->count(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'total_pages' => $notifications->lastPage(),
                'next_page_url' => $notifications->nextPageUrl(),
                'prev_page_url' => $notifications->previousPageUrl(),
            ],
        ]);
    }

    public function archiveNotification($id)
    {
        $user = Auth::user();
        // Attempt to find the notification for the authenticated user
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        //
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found or already archived.',
            ], 404);
        }
        // Update the status to 'archived'
        $notification->update(['status' => 'archived']);
        //
        return response()->json([
            'success' => true,
            'message' => 'Notification archived successfully.',
        ]);
    }

    public function deleteNotification($id)
    {
        $user = Auth::user();
        // Attempt to find the notification for the authenticated user
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        //
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }
        // Update the status to 'deleted' for soft delete behavior
        $notification->update(['status' => 'deleted']);
        //
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
        ]);
    }
}
