<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Privilege;
use App\Models\UserType;
use Illuminate\Http\Request;

class PrivilegeController extends Controller
{
    /**
     * Get all privileges
     */
    public function index()
    {
        $privileges = Privilege::with(['userType', 'roles'])->get();

        return response()->json([
            'success' => true,
            'privileges' => $privileges->map(function ($privilege) {
                return [
                    'id' => $privilege->id,
                    'name' => $privilege->name,
                    'description' => $privilege->description,
                    'user_type' => $privilege->userType ? [
                        'id' => $privilege->userType->id,
                        'name' => $privilege->userType->name,
                        'code' => $privilege->userType->code,
                    ] : null,
                    'roles_count' => $privilege->roles->count(),
                ];
            })
        ]);
    }

    /**
     * Get privilege statistics
     */
    public function statistics()
    {
        $total = Privilege::count();

        $adminType = UserType::where('code', 'admin')->first();
        $customerType = UserType::where('code', 'customer')->first();

        $adminPrivileges = Privilege::where('user_type_id', $adminType?->id)->count();
        $customerPrivileges = Privilege::where('user_type_id', $customerType?->id)->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $total,
                'admin_privileges' => $adminPrivileges,
                'customer_privileges' => $customerPrivileges,
            ]
        ]);
    }
}
