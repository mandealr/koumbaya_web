<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    /**
     * Get all users with filters
     */
    public function index(Request $request)
    {
        $query = User::with(['roles']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'verified':
                    $query->whereNotNull('verified_at');
                    break;
                case 'unverified':
                    $query->whereNull('verified_at');
                    break;
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $users = $query->paginate($perPage);

        // Transform users to include role names
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_active' => $user->is_active,
                'verified_at' => $user->verified_at,
                'created_at' => $user->created_at,
                'last_login_date' => $user->last_login_date,
                'roles' => $user->roles->pluck('name')->toArray(),
                'primary_role' => $user->roles->first()->name ?? 'Client'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
                'available_roles' => $this->getAvailableRoles()
            ]
        ]);
    }

    /**
     * Get a single user
     */
    public function show($id)
    {
        $user = User::with(['roles', 'products', 'lotteryTickets'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'stats' => [
                    'total_products' => $user->products()->count(),
                    'total_tickets' => $user->lotteryTickets()->count(),
                    'total_spent' => $user->transactions()->where('status', 'completed')->sum('amount')
                ]
            ]
        ]);
    }

    /**
     * Create a new user (Admin creation - SuperAdmin and Admin can create certain roles)
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();
        
        // Vérifier que l'utilisateur actuel est au moins Admin
        if (!$currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'phone' => 'required|string|unique:users',
            'role' => 'required|string|exists:roles,name',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Empêcher la création de Super Admin
        if ($request->role === 'Super Admin') {
            return response()->json([
                'success' => false,
                'message' => 'La création de Super Admin n\'est pas autorisée'
            ], 403);
        }
        
        // Les Admins réguliers ne peuvent créer que certains rôles
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            $allowedRoles = ['Agent', 'Agent Back Office', 'Business', 'Particulier'];
            if (!in_array($request->role, $allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas créer ce type d\'utilisateur'
                ], 403);
            }
        }

        // Générer un mot de passe temporaire aléatoire
        $temporaryPassword = Str::random(12) . '!@#';

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($temporaryPassword),
            'is_active' => $request->is_active ?? true,
            'verified_at' => now(), // Auto-verify admin-created users
            'user_type_id' => $this->getUserTypeIdForRole($request->role)
        ]);

        // Attach role
        $role = Role::where('name', $request->role)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        // Envoyer l'email d'initialisation du mot de passe
        try {
            $this->sendPasswordInitializationEmail($user, $temporaryPassword, $request->role);
        } catch (\Exception $e) {
            \Log::error('Failed to send password initialization email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès. Un email d\'initialisation du mot de passe a été envoyé.',
            'data' => ['user' => $user->load('roles')]
        ], 201);
    }

    /**
     * Update a user
     */
    public function update(Request $request, $id)
    {
        $currentUser = auth()->user();
        $user = User::findOrFail($id);
        
        // Vérifier que l'utilisateur actuel est au moins Admin
        if (!$currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }
        
        // Empêcher la modification de comptes Super Admin par des admins réguliers
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            $userRoles = $user->roles->pluck('name')->toArray();
            if (in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas modifier ce type d\'utilisateur'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'email' => 'string|email|unique:users,email,' . $id,
            'phone' => 'string|unique:users,phone,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|string|exists:roles,name',
            'is_active' => 'boolean',
            // Champs supplémentaires pour utilisateurs non-admin
            'business_name' => 'nullable|string|max:255',
            'business_email' => 'nullable|email|max:255',
            'business_description' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'bio' => 'nullable|string|max:1000',
            'country_id' => 'nullable|integer|exists:countries,id',
            'language_id' => 'nullable|integer|exists:languages,id',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'is_active',
            'business_name', 'business_email', 'business_description',
            'city', 'address', 'date_of_birth', 'gender', 'bio',
            'country_id', 'language_id',
            'email_notifications', 'sms_notifications', 'push_notifications'
        ]);
        
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        // Update role if provided
        if ($request->filled('role')) {
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès',
            'data' => ['user' => $user->load('roles')]
        ]);
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        $currentUser = auth()->user();
        $user = User::findOrFail($id);
        
        // Vérifier que l'utilisateur actuel est au moins Admin
        if (!$currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }
        
        // Empêcher la désactivation de comptes Super Admin par des admins réguliers
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            $userRoles = $user->roles->pluck('name')->toArray();
            if (in_array('Super Admin', $userRoles) || in_array('Admin', $userRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas modifier le statut de ce type d\'utilisateur'
                ], 403);
            }
        }
        
        // Empêcher l'auto-désactivation
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas modifier votre propre statut'
            ], 403);
        }
        
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'Utilisateur activé' : 'Utilisateur désactivé',
            'data' => ['user' => $user]
        ]);
    }

    /**
     * Get roles available for admin creation
     */
    public function getAdminRoles()
    {
        $currentUser = auth()->user();
        
        // Vérifier que l'utilisateur actuel est au moins Admin
        if (!$currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $query = Role::where('active', true)
            ->where('name', '!=', 'Super Admin'); // Exclure Super Admin toujours
            
        // Super Admin peut créer tous les rôles sauf Super Admin
        if ($currentUser->isSuperAdmin()) {
            $query->whereIn('name', ['Admin', 'Agent', 'Agent Back Office', 'Business', 'Particulier']);
        } 
        // Admin régulier peut créer seulement certains rôles
        else if ($currentUser->isAdmin()) {
            $query->whereIn('name', ['Agent', 'Agent Back Office', 'Business', 'Particulier']);
        }

        $roles = $query->select('name', 'description')
            ->get()
            ->map(function ($role) {
                return [
                    'value' => $role->name,
                    'label' => $this->getRoleLabel($role->name),
                    'description' => $role->description
                ];
            });

        return response()->json([
            'success' => true,
            'data' => ['roles' => $roles]
        ]);
    }

    /**
     * Get available roles (excluding Super Admin for creation)
     */
    private function getAvailableRoles()
    {
        $query = Role::where('active', true)
            ->select('name', 'description');

        // Exclure Super Admin de la liste pour la création
        if (request()->is('*/create') || request()->isMethod('post')) {
            $query->where('name', '!=', 'Super Admin');
        }

        return $query->get()
            ->map(function ($role) {
                return [
                    'value' => $role->name,
                    'label' => $this->getRoleLabel($role->name),
                    'description' => $role->description
                ];
            });
    }

    /**
     * Get user-friendly role label
     */
    private function getRoleLabel($roleName)
    {
        $labels = [
            'Super Admin' => 'Super Administrateur',
            'Admin' => 'Administrateur',
            'Agent' => 'Agent de Support',
            'Agent Back Office' => 'Agent Back Office', 
            'Business Enterprise' => 'Marchand Entreprise',
            'Business Individual' => 'Marchand Particulier',
            'Business' => 'Marchand',
            'Particulier' => 'Client'
        ];

        return $labels[$roleName] ?? $roleName;
    }

    /**
     * Get the appropriate user_type_id for a role
     */
    private function getUserTypeIdForRole($roleName)
    {
        // Mapping des rôles vers les user_type_id
        $typeMap = [
            'Super Admin' => 3, // Admin
            'Admin' => 3, // Admin
            'Agent' => 3, // Admin
            'Agent Back Office' => 3, // Admin
            'Business Enterprise' => 2, // Business
            'Business Individual' => 2, // Business  
            'Business' => 2, // Business
            'Particulier' => 1 // Client
        ];

        return $typeMap[$roleName] ?? 1; // Default to Client
    }

    /**
     * Send password initialization email to newly created user
     */
    private function sendPasswordInitializationEmail(User $user, string $temporaryPassword, string $role)
    {
        $resetUrl = config('app.frontend_url', config('app.url')) . '/reset-password';
        
        Mail::send('emails.admin-user-created', [
            'user' => $user,
            'temporaryPassword' => $temporaryPassword,
            'role' => $this->getRoleLabel($role),
            'resetUrl' => $resetUrl,
            'createdBy' => auth()->user()
        ], function ($message) use ($user) {
            $message->to($user->email, $user->first_name . ' ' . $user->last_name)
                ->subject('Bienvenue sur Koumbaya - Initialisez votre mot de passe');
        });
    }
}