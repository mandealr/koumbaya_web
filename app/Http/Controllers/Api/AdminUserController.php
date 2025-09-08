<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
     * Create a new user (Admin creation - SuperAdmin only)
     */
    public function store(Request $request)
    {
        // Vérifier que l'utilisateur actuel est SuperAdmin
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les Super Administrateurs peuvent créer des utilisateurs admin'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8',
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

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => $request->is_active ?? true,
            'verified_at' => now(), // Auto-verify admin-created users
            'user_type_id' => 3 // Admin type
        ]);

        // Attach role
        $role = Role::where('name', $request->role)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'data' => ['user' => $user->load('roles')]
        ], 201);
    }

    /**
     * Update a user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'email' => 'string|email|unique:users,email,' . $id,
            'phone' => 'string|unique:users,phone,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'string|exists:roles,name',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['first_name', 'last_name', 'email', 'phone', 'is_active']);
        
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
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'Utilisateur activé' : 'Utilisateur désactivé',
            'data' => ['user' => $user]
        ]);
    }

    /**
     * Get roles available for admin creation (SuperAdmin only)
     */
    public function getAdminRoles()
    {
        // Vérifier que l'utilisateur actuel est SuperAdmin
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $roles = Role::where('active', true)
            ->where('name', '!=', 'Super Admin') // Exclure Super Admin
            ->whereIn('name', ['Admin', 'Agent', 'Agent Back Office']) // Seulement les rôles admin
            ->select('name', 'description')
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
            'Business' => 'Marchand',
            'Particulier' => 'Client'
        ];

        return $labels[$roleName] ?? $roleName;
    }
}