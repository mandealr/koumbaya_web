<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use Illuminate\Http\Request;

class UserTypeController extends Controller
{
    /**
     * Get all user types
     */
    public function index()
    {
        $userTypes = UserType::withCount(['roles', 'privileges'])->get();

        return response()->json([
            'success' => true,
            'user_types' => $userTypes->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'code' => $type->code,
                    'description' => $type->description,
                    'is_active' => $type->is_active,
                    'roles_count' => $type->roles_count ?? 0,
                    'privileges_count' => $type->privileges_count ?? 0,
                    'users_count' => $type->users()->count(),
                ];
            })
        ]);
    }

    /**
     * Get user type statistics
     */
    public function statistics()
    {
        $total = UserType::count();
        $active = UserType::where('is_active', true)->count();

        $adminType = UserType::where('code', 'admin')->first();
        $customerType = UserType::where('code', 'customer')->first();

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $total,
                'active' => $active,
                'admin_users' => $adminType ? $adminType->users()->count() : 0,
                'customer_users' => $customerType ? $customerType->users()->count() : 0,
            ]
        ]);
    }
}
