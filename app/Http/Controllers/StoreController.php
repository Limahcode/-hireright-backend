<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class StoreController extends Controller
{
    /**
     * Handle store creation for an authenticated user.
     */
    public function store(Request $request)
    {
        // Get authenticated user
        $user = Auth::user();
        // Check if the user already has a store
        if ($user->store_id) {
            return response()->json(['error' => 'User already has a store.'], 409);
        }
        // Validate store creation request
        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'apply_vat' => 'nullable|boolean',
            'currency_code' => 'nullable|string|max:10',
            'vat_percent' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:active,inactive',
        ]);
        //
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // Create the store
        $store = Store::create([
            'user_id' => $user->id, // Associate with the current user
            'store_name' => $request->store_name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'currency_code' => $request->currency_code,
            'apply_vat' => $request->apply_vat ?? false,
            'vat_percent' => $request->vat_percent,
            'status' => 'status',
        ]);
        // Update the user's store_id
        $user = User::findOrFail($user->id);
        $user->store_id = $store->id;
        $user->save();
        // 
        return response()->json(['message' => 'Store created successfully', 'store' => $store], 201);
    }

    /**
     * Fetch the store details of the authenticated user's store.
     */
    public function show()
    {
        // Get authenticated user
        $user = Auth::user();
        // Check if user has a store
        if (!$user->store_id) {
            return response()->json(['error' => 'User does not have a store.'], 404);
        }
        // Retrieve the store
        $store = Store::find($user->store_id);
        // 
        return response()->json($store, 200);
    }

    /**
     * Update store details.
     */
    public function update(Request $request)
    {
        // Get authenticated user
        $user = Auth::user();
        // Check if user has a store
        if (!$user->store_id) {
            return response()->json(['error' => 'User does not have a store.'], 404);
        }
        // Retrieve the store
        $store = Store::find($user->store_id);
        // Validate update request
        $validator = Validator::make($request->all(), [
            'store_name' => 'sometimes|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'currency_code' => 'nullable|string|max:10',
            'apply_vat' => 'nullable|boolean',
            'vat_percent' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:active,inactive',
        ]);
        // 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // Update store details
        $store->update($request->only([
            'store_name',
            'slug',
            'address',
            'phone',
            'email'
        ]));
        return response()->json(['message' => 'Store updated successfully', 'store' => $store], 200);
    }
}
