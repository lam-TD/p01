<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API endpoints for managing users"
 * )
 */
class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * @OA\Get(
     *     path="/api/v1/users",
     *     operationId="getUsersList",
     *     tags={"Users"},
     *     summary="Get list of users",
     *     description="Returns list of users with pagination and filtering options",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="tenant_id",
     *         in="query",
     *         description="Filter by tenant ID (super-admin only)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search users by name or email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter users by role",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Field to order by",
     *         required=false,
     *         @OA\Schema(type="string", default="name")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Order direction",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                     @OA\Property(property="tenant_id", type="integer", example=1),
     *                     @OA\Property(
     *                         property="tenant",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Acme Corp")
     *                     ),
     *                     @OA\Property(property="roles", type="array", @OA\Items(type="string", example="admin")),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        // Current user can only see users of their own tenant unless they're super-admin
        if (! $request->user()->hasRole('super-admin')) {
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        // Filter by tenant if specified and user is super-admin
        if ($request->has('tenant_id') && $request->user()->hasRole('super-admin')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Search by name or email
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        // Filter by role
        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Order by
        $orderBy = $request->order_by ?? 'name';
        $direction = $request->direction ?? 'asc';
        $query->orderBy($orderBy, $direction);

        // Paginate
        $users = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'tenant_id' => $user->tenant_id,
                    'tenant' => $user->tenant ? [
                        'id' => $user->tenant->id,
                        'name' => $user->tenant->name,
                    ] : null,
                    'roles' => $user->roles->pluck('name'),
                    'created_at' => $user->created_at->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => $users->currentPage(),
                'from' => $users->firstItem(),
                'last_page' => $users->lastPage(),
                'path' => $users->path(),
                'per_page' => $users->perPage(),
                'to' => $users->lastItem(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Store a newly created user.
     * 
     * @OA\Post(
     *     path="/api/v1/users",
     *     operationId="storeUser",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     description="Creates a new user with specified details",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="tenant_id", type="integer", example=1, description="Required for super-admin only"),
     *             @OA\Property(
     *                 property="roles",
     *                 type="array",
     *                 @OA\Items(type="string", example="admin")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="tenant_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="tenant",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Acme Corp")
     *                 ),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string", example="admin")),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="User created successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'tenant_id' => [
                $request->user()->hasRole('super-admin') ? 'required' : 'prohibited',
                'exists:tenants,id',
            ],
            'roles' => 'sometimes|array',
            'roles.*' => [
                'string',
                Rule::in(Role::whereNot('name', 'super-admin')->pluck('name')->toArray()),
            ],
        ]);

        // Set tenant_id based on current user if not super-admin
        $tenantId = $request->user()->hasRole('super-admin')
            ? $request->tenant_id
            : $request->user()->tenant_id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => $tenantId,
        ]);

        // Assign roles (default to 'user' if none specified)
        if ($request->has('roles') && ! empty($request->roles)) {
            $user->syncRoles($request->roles);
        } else {
            $user->assignRole('user');
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'tenant' => $user->tenant ? [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                ] : null,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at->toIso8601String(),
            ],
            'message' => 'User created successfully.',
        ], 201);
    }

    /**
     * Display the specified user.
     * 
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     operationId="getUserById",
     *     tags={"Users"},
     *     summary="Get user information",
     *     description="Returns detailed information about a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="tenant_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="tenant",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Acme Corp")
     *                 ),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string", example="admin")),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string", example="view_files")),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'tenant' => $user->tenant ? [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                ] : null,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update the specified user.
     * 
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     summary="Update user information",
     *     description="Updates a user's details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Name"),
     *             @OA\Property(property="email", type="string", format="email", example="updated@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="tenant_id", type="integer", example=2, description="Super-admin only"),
     *             @OA\Property(
     *                 property="roles",
     *                 type="array",
     *                 @OA\Items(type="string", example="editor")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Updated Name"),
     *                 @OA\Property(property="email", type="string", format="email", example="updated@example.com"),
     *                 @OA\Property(property="tenant_id", type="integer", example=2),
     *                 @OA\Property(
     *                     property="tenant",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="New Tenant")
     *                 ),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string", example="editor")),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'roles' => 'sometimes|array',
        ];

        // Only super-admin can change tenant_id
        if ($request->user()->hasRole('super-admin')) {
            $rules['tenant_id'] = 'sometimes|exists:tenants,id';
            $rules['roles.*'] = 'string'; // Super-admin can assign any role
        } else {
            $rules['roles.*'] = [
                'string',
                Rule::in(Role::whereNot('name', 'super-admin')->pluck('name')->toArray()),
            ];
        }

        $request->validate($rules);

        // Update user fields
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        // Only super-admin can change tenant_id
        if ($request->user()->hasRole('super-admin') && $request->has('tenant_id')) {
            $user->tenant_id = $request->tenant_id;
        }

        $user->save();

        // Update roles if specified
        if ($request->has('roles')) {
            // Make sure regular admins can't assign super-admin role
            if (! $request->user()->hasRole('super-admin')) {
                $roles = array_filter($request->roles, function ($role) {
                    return $role !== 'super-admin';
                });
                $user->syncRoles($roles);
            } else {
                $user->syncRoles($request->roles);
            }
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'tenant' => $user->tenant ? [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                ] : null,
                'roles' => $user->roles->pluck('name'),
                'updated_at' => $user->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Remove the specified user.
     * 
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     operationId="deleteUser",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     description="Soft deletes a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        // Prevent deleting the last super-admin
        if ($user->hasRole('super-admin') && User::role('super-admin')->count() <= 1) {
            return response()->json([
                'message' => 'Cannot delete the last super-admin user.',
            ], 422);
        }

        // Use soft delete
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
