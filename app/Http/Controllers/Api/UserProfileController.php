<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

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
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'country_id' => 'nullable|exists:countries,id',
            'bio' => 'nullable|string|max:1000',
        ]);

        $user->update($request->only([
            'first_name',
            'last_name', 
            'email',
            'phone',
            'birth_date',
            'gender',
            'city',
            'address',
            'country_id',
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

        // Default preferences structure
        $defaultPreferences = [
            'email_notifications' => [
                'lottery_results' => true,
                'new_lotteries' => true,
                'ending_soon' => true,
                'account_updates' => true,
                'login_alerts' => true
            ],
            'sms_notifications' => [
                'lottery_results' => false,
                'new_lotteries' => false,
                'ending_soon' => false,
                'account_updates' => false,
                'login_alerts' => false
            ],
            'push_notifications' => true,
            'marketing_emails' => true
        ];

        // If preferences column exists, get from there
        if (\Schema::hasColumn('users', 'preferences') && $user->preferences) {
            $userPrefs = json_decode($user->preferences, true) ?? [];
            $preferences = array_merge($defaultPreferences, $userPrefs);
        } else {
            $preferences = $defaultPreferences;
        }

        return $this->sendResponse([
            'preferences' => $preferences
        ], 'Preferences retrieved successfully');
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Delete old avatar if exists
            if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            
            // Update user avatar
            $user->update([
                'avatar_url' => $avatarPath
            ]);

            return $this->sendResponse([
                'avatar_url' => Storage::disk('public')->url($avatarPath),
                'user' => $user->fresh()
            ], 'Avatar uploaded successfully');

        } catch (\Exception $e) {
            return $this->sendError('Failed to upload avatar', [], 500);
        }
    }

    /**
     * Update detailed notification preferences
     */
    public function updateDetailedPreferences(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'email_notifications' => 'array',
            'email_notifications.lottery_results' => 'boolean',
            'email_notifications.new_lotteries' => 'boolean',
            'email_notifications.ending_soon' => 'boolean',
            'email_notifications.account_updates' => 'boolean',
            'email_notifications.login_alerts' => 'boolean',
            'sms_notifications' => 'array',
            'sms_notifications.lottery_results' => 'boolean',
            'sms_notifications.new_lotteries' => 'boolean',
            'sms_notifications.ending_soon' => 'boolean',
            'sms_notifications.account_updates' => 'boolean',
            'sms_notifications.login_alerts' => 'boolean',
            'push_notifications' => 'boolean',
            'marketing_emails' => 'boolean',
        ]);

        $preferences = $request->only([
            'email_notifications',
            'sms_notifications', 
            'push_notifications',
            'marketing_emails'
        ]);

        // Update user preferences
        $user->update([
            'preferences' => json_encode($preferences),
            'email_notifications' => $request->input('email_notifications.account_updates', true),
            'sms_notifications' => $request->input('sms_notifications.account_updates', false),
            'push_notifications' => $request->get('push_notifications', true),
        ]);

        return $this->sendResponse([
            'preferences' => $preferences
        ], 'Detailed preferences updated successfully');
    }

    /**
     * Get user login sessions
     */
    public function getSessions()
    {
        $user = auth()->user();
        
        // Get recent login history
        $sessions = $user->loginHistory()
                         ->orderBy('created_at', 'desc')
                         ->limit(10)
                         ->get()
                         ->map(function ($session) {
                             return [
                                 'id' => $session->id,
                                 'ip_address' => $session->ip_address,
                                 'user_agent' => $session->user_agent,
                                 'city' => $session->city ?? 'Inconnue',
                                 'country' => $session->country ?? 'Inconnue',
                                 'is_current' => $session->ip_address === request()->ip(),
                                 'last_activity' => $session->created_at,
                                 'status' => $session->status ?? 'active'
                             ];
                         });

        return $this->sendResponse([
            'sessions' => $sessions
        ], 'Sessions retrieved successfully');
    }

    /**
     * Toggle two-factor authentication
     */
    public function toggleTwoFactor(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'enabled' => 'required|boolean'
        ]);

        $user->update([
            'mfa_is_active' => $request->enabled
        ]);

        return $this->sendResponse([
            'two_factor_enabled' => $user->mfa_is_active
        ], 'Two-factor authentication updated successfully');
    }

    /**
     * Revoke a login session
     */
    public function revokeSession($sessionId)
    {
        $user = auth()->user();
        
        // Find the session
        $session = $user->loginHistory()->find($sessionId);
        
        if (!$session) {
            return $this->sendError('Session not found', [], 404);
        }

        // Update session status to revoked
        $session->update(['status' => 'revoked']);

        return $this->sendResponse([], 'Session revoked successfully');
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