<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user profile
     */
    public function show()
    {
        $user = auth()->user();
        
        return $this->sendResponse([
            'user' => $user->load('userType', 'country')
        ], 'Profile retrieved successfully');
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000',
        ]);

        $user->update($request->only([
            'first_name',
            'last_name', 
            'email',
            'phone',
            'city',
            'address',
            'bio'
        ]));

        return $this->sendResponse([
            'user' => $user->fresh()
        ], 'Profile updated successfully');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Current password is incorrect', ['current_password' => ['The current password is incorrect.']], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->sendResponse([], 'Password updated successfully');
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
        ]);

        // For now, we'll store preferences in a JSON column if it exists,
        // or handle it as a separate preferences table later
        $preferences = [
            'email_notifications' => $request->get('email_notifications', true),
            'push_notifications' => $request->get('push_notifications', false),
        ];

        // If preferences column exists, update it
        if (\Schema::hasColumn('users', 'preferences')) {
            $user->update(['preferences' => json_encode($preferences)]);
        }

        return $this->sendResponse([
            'preferences' => $preferences
        ], 'Preferences updated successfully');
    }

    /**
     * Get user preferences
     */
    public function getPreferences()
    {
        $user = auth()->user();

        // Default preferences
        $preferences = [
            'email_notifications' => true,
            'push_notifications' => false,
        ];

        // If preferences column exists, get from there
        if (\Schema::hasColumn('users', 'preferences') && $user->preferences) {
            $preferences = array_merge($preferences, json_decode($user->preferences, true) ?? []);
        }

        return $this->sendResponse([
            'preferences' => $preferences
        ], 'Preferences retrieved successfully');
    }

    /**
     * Send success response
     */
    protected function sendResponse($data, $message = 'Success', $code = 200)
    {
        $response = [
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send error response
     */
    protected function sendError($message = 'Error', $errors = [], $code = 400)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if(!empty($errors)){
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}