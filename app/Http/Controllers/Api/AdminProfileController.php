<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Models\UserLoginHistory;
use App\Models\NotificationType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AdminProfileController extends Controller
{
    /**
     * Get admin profile
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Calculate admin stats
            $stats = [
                'actions_count' => $this->getActionCount($user->id),
                'login_streak' => $this->getLoginStreak($user->id),
                'last_login' => $user->last_login_at,
                'created_at' => $user->created_at
            ];

            // Get active sessions
            $sessions = UserSession::getActiveSessions($user->id);

            // Get admin notification preferences
            $adminNotifications = NotificationType::getByCategory('admin');

            return response()->json([
                'success' => true,
                'data' => [
                    'profile' => [
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'position' => $user->position,
                        'bio' => $user->bio,
                        'timezone' => $user->timezone,
                        'language' => $user->language,
                        'two_factor_enabled' => $user->two_factor_enabled
                    ],
                    'stats' => $stats,
                    'sessions' => $this->formatSessions($sessions),
                    'admin_notifications' => $adminNotifications
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update admin profile
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:100',
                'bio' => 'nullable|string|max:1000'
            ]);

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'bio' => $request->bio
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => [
                    'user' => $user->fresh()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $request->validate([
                'current_password' => 'required|string',
                'new_password' => ['required', 'confirmed', Password::min(8)]
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le mot de passe actuel est incorrect'
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du mot de passe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $request->validate([
                'timezone' => 'required|string|max:50',
                'language' => 'required|string|max:5',
                'notifications' => 'array'
            ]);

            $user->update([
                'timezone' => $request->timezone,
                'language' => $request->language
            ]);

            // Update notification preferences
            if ($request->has('notifications')) {
                foreach ($request->notifications as $notification) {
                    if (isset($notification['id']) && isset($notification['enabled'])) {
                        NotificationType::where('type_id', $notification['id'])
                            ->update(['enabled' => $notification['enabled']]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Préférences mises à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des préférences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle 2FA
     */
    public function toggle2FA(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $request->validate([
                'enabled' => 'required|boolean'
            ]);

            $user->update([
                'two_factor_enabled' => $request->enabled
            ]);

            return response()->json([
                'success' => true,
                'message' => $request->enabled 
                    ? 'Authentification 2FA activée' 
                    : 'Authentification 2FA désactivée'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification 2FA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terminate session
     */
    public function terminateSession(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'session_id' => 'required|integer'
            ]);

            $session = UserSession::where('id', $request->session_id)
                ->where('user_id', Auth::id())
                ->where('is_current', false)
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session non trouvée'
                ], 404);
            }

            $session->delete();

            return response()->json([
                'success' => true,
                'message' => 'Session déconnectée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terminate all sessions
     */
    public function terminateAllSessions(): JsonResponse
    {
        try {
            UserSession::where('user_id', Auth::id())
                ->where('is_current', false)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Toutes les sessions ont été déconnectées'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload profile image
     */
    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $user = Auth::user();
            
            if ($request->hasFile('profile_image')) {
                $path = $request->file('profile_image')->store('avatars', 'public');
                
                $user->update([
                    'avatar' => $path
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Photo de profil mise à jour avec succès',
                'data' => [
                    'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload de l\'image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export activity log
     */
    public function exportActivityLog(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get login history and other activities
            $loginHistory = UserLoginHistory::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            $activityLog = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email
                ],
                'export_date' => now()->toISOString(),
                'login_history' => $loginHistory,
                'profile_updates' => [], // You can implement this based on your needs
                'actions' => [] // You can implement this based on your needs
            ];

            return response()->json([
                'success' => true,
                'data' => $activityLog
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export du journal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate action count for the current month
     */
    private function getActionCount(int $userId): int
    {
        // This would typically count actions from an audit log table
        // For now, we'll use login history as a proxy
        return UserLoginHistory::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    /**
     * Calculate login streak
     */
    private function getLoginStreak(int $userId): int
    {
        $logins = UserLoginHistory::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->pluck('created_at')
            ->map(function ($date) {
                return $date->format('Y-m-d');
            })
            ->unique()
            ->values();

        $streak = 0;
        $currentDate = now()->format('Y-m-d');

        foreach ($logins as $loginDate) {
            if ($loginDate === $currentDate || 
                $loginDate === now()->subDays($streak)->format('Y-m-d')) {
                $streak++;
                $currentDate = now()->subDays($streak)->format('Y-m-d');
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Format sessions for frontend
     */
    private function formatSessions($sessions): array
    {
        return $sessions->map(function ($session) {
            return [
                'id' => $session->id,
                'device' => $session->device ?: 'Unknown Device',
                'location' => $session->location ?: 'Unknown Location',
                'last_activity' => $session->last_activity,
                'current' => $session->is_current
            ];
        })->toArray();
    }
}