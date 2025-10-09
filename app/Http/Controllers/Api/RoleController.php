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
