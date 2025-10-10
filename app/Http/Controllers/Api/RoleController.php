<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\UserType;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Get all roles
     */
    public function index()
    {
        $roles = Role::with(['userType', 'privileges'])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'description' => $role->description,
                        'active' => $role->active,
                        'mutable' => $role->mutable,
                        'user_type' => $role->userType ? [
                            'id' => $role->userType->id,
                            'name' => $role->userType->name,
                            'code' => $role->userType->code,
                        ] : null,
                        'privileges_count' => $role->privileges->count(),
                        'users_count' => $role->users()->count(),
                    ];
                })
            ]
        ]);
    }

    /**
     * Create a new role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'user_type_id' => 'required|exists:user_types,id',
            'active' => 'boolean',
            'privileges' => 'nullable|array',
            'privileges.*' => 'exists:privileges,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_type_id' => $request->user_type_id,
            'active' => $request->active ?? true,
            'mutable' => true, // Les rôles créés manuellement sont modifiables
        ]);

        // Attach privileges if provided
        if ($request->filled('privileges')) {
            $role->privileges()->attach($request->privileges);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rôle créé avec succès',
            'data' => [
                'role' => $role->load(['userType', 'privileges'])
            ]
        ], 201);
    }

    /**
     * Update a role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Vérifier si le rôle est modifiable
        if (!$role->mutable) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rôle système ne peut pas être modifié'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'description' => 'nullable|string|max:500',
            'user_type_id' => 'required|exists:user_types,id',
            'active' => 'boolean',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'user_type_id' => $request->user_type_id,
            'active' => $request->active ?? $role->active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rôle mis à jour avec succès',
            'data' => [
                'role' => $role->load('userType')
            ]
        ]);
    }

    /**
     * Delete a role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Vérifier si le rôle est modifiable
        if (!$role->mutable) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rôle système ne peut pas être supprimé'
            ], 403);
        }

        // Vérifier si des utilisateurs ont ce rôle
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rôle ne peut pas être supprimé car des utilisateurs y sont associés'
            ], 400);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rôle supprimé avec succès'
        ]);
    }

    /**
     * Get all user types for role creation
     */
    public function getUserTypes()
    {
        $userTypes = UserType::where('active', true)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user_types' => $userTypes
            ]
        ]);
    }

    /**
     * Get all privileges for role creation
     */
    public function getPrivileges()
    {
        $privileges = \App\Models\Privilege::where('active', true)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'privileges' => $privileges
            ]
        ]);
    }

    /**
     * Get role statistics
     */
    public function statistics()
    {
        $total = Role::count();
        $active = Role::where('active', true)->count();
        $inactive = Role::where('active', false)->count();

        $adminType = UserType::where('code', 'admin')->first();
        $customerType = UserType::where('code', 'customer')->first();

        $adminRoles = Role::where('user_type_id', $adminType?->id)->count();
        $customerRoles = Role::where('user_type_id', $customerType?->id)->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'admin_roles' => $adminRoles,
                'customer_roles' => $customerRoles,
            ]
        ]);
    }
}
