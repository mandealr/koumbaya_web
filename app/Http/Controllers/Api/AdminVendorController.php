<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Notifications\VendorAccountCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminVendorController extends Controller
{
    /**
     * Get all Business vendors
     */
    public function index(Request $request)
    {
        $query = User::with(['roles'])
            ->whereHas('roles', function ($q) {
                $q->where('name', 'Business');
            });

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%");
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
                    $query->whereNotNull('email_verified_at');
                    break;
                case 'unverified':
                    $query->whereNull('email_verified_at');
                    break;
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $vendors = $query->get();

        return response()->json([
            'success' => true,
            'vendors' => $vendors->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'first_name' => $vendor->first_name,
                    'last_name' => $vendor->last_name,
                    'email' => $vendor->email,
                    'phone' => $vendor->phone,
                    'company_name' => $vendor->company_name,
                    'avatar_url' => $vendor->avatar_url,
                    'is_active' => $vendor->is_active,
                    'email_verified_at' => $vendor->email_verified_at,
                    'created_at' => $vendor->created_at,
                    'roles' => $vendor->roles->pluck('name')->toArray(),
                ];
            })
        ]);
    }

    /**
     * Create a new Business vendor
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create the user with a temporary random password
            $temporaryPassword = Str::random(32);
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company_name' => $request->company_name,
                'password' => Hash::make($temporaryPassword),
                'user_type_id' => 2, // Customer type pour les vendeurs Business
                'is_active' => true,
                'email_verified_at' => now(), // Auto-verify admin-created accounts
            ]);

            // Assign Business role
            $businessRole = Role::where('name', 'Business')->first();
            if ($businessRole) {
                $user->roles()->attach($businessRole->id);
            }

            // Send password creation email with custom notification
            $token = Password::broker()->createToken($user);
            $user->notify(new VendorAccountCreated($token));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vendeur Pro créé avec succès. Un email de création de mot de passe a été envoyé.',
                'vendor' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'company_name' => $user->company_name,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du vendeur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a Business vendor
     */
    public function update(Request $request, $id)
    {
        $vendor = User::find($id);

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendeur non trouvé'
            ], 404);
        }

        // Verify it's a Business vendor
        if (!$vendor->roles->contains('name', 'Business')) {
            return response()->json([
                'success' => false,
                'message' => 'Cet utilisateur n\'est pas un vendeur professionnel'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $vendor->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company_name' => $request->company_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vendeur Pro mis à jour avec succès',
                'vendor' => [
                    'id' => $vendor->id,
                    'first_name' => $vendor->first_name,
                    'last_name' => $vendor->last_name,
                    'email' => $vendor->email,
                    'phone' => $vendor->phone,
                    'company_name' => $vendor->company_name,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du vendeur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a Business vendor
     */
    public function destroy($id)
    {
        $vendor = User::find($id);

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendeur non trouvé'
            ], 404);
        }

        // Verify it's a Business vendor
        if (!$vendor->roles->contains('name', 'Business')) {
            return response()->json([
                'success' => false,
                'message' => 'Cet utilisateur n\'est pas un vendeur professionnel'
            ], 403);
        }

        try {
            // Check if vendor has active products
            $activeProducts = $vendor->products()->where('status', 'active')->count();
            if ($activeProducts > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un vendeur avec des produits actifs'
                ], 422);
            }

            $vendor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vendeur Pro supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du vendeur: ' . $e->getMessage()
            ], 500);
        }
    }
}
