<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminCustomerController extends Controller
{
    /**
     * Get all customers (particulier role)
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'userType'])
            ->whereHas('roles', function ($q) {
                $q->where('name', 'particulier')->orWhere('name', 'Particulier');
            });

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
        $perPage = $request->get('per_page', 15);
        $customers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'customers' => $customers->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'first_name' => $customer->first_name,
                        'last_name' => $customer->last_name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'avatar_url' => $customer->avatar_url,
                        'is_active' => $customer->is_active,
                        'email_verified_at' => $customer->verified_at,
                        'created_at' => $customer->created_at,
                        'user_type' => $customer->userType ? $customer->userType->name : null,
                        'roles' => $customer->roles->pluck('name')->toArray(),
                    ];
                })->values(),
                'pagination' => [
                    'total' => $customers->total(),
                    'per_page' => $customers->perPage(),
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'from' => $customers->firstItem(),
                    'to' => $customers->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Get customer statistics
     */
    public function statistics()
    {
        $baseQuery = User::whereHas('roles', function ($q) {
            $q->where('name', 'particulier')->orWhere('name', 'Particulier');
        });

        $total = (clone $baseQuery)->count();
        $active = (clone $baseQuery)->where('is_active', true)->count();
        $inactive = (clone $baseQuery)->where('is_active', false)->count();
        $verified = (clone $baseQuery)->whereNotNull('verified_at')->count();
        $unverified = (clone $baseQuery)->whereNull('verified_at')->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'verified' => $verified,
                'unverified' => $unverified,
            ]
        ]);
    }

    /**
     * Toggle customer active status
     */
    public function toggleStatus($id)
    {
        $customer = User::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }

        // Verify it's a customer
        if (!$customer->roles->whereIn('name', ['particulier', 'Particulier'])->count()) {
            return response()->json([
                'success' => false,
                'message' => 'Cet utilisateur n\'est pas un client'
            ], 403);
        }

        $customer->is_active = !$customer->is_active;
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut du client mis à jour',
            'is_active' => $customer->is_active
        ]);
    }
}
