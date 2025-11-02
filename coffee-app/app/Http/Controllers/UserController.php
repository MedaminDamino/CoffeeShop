<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="username", type="string", example="johndoe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="role", type="string", enum={"user", "admin", "super_admin"}, example="user"),
 *     @OA\Property(property="birthday", type="string", format="date", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = User::select('id', 'name', 'username', 'email', 'role', 'birthday', 'created_at');

        // Handle sorting
        if ($request->has('sort_by')) {
            $sortBy = $request->get('sort_by');
            $sortDirection = $request->get('sort_direction', 'asc');

            // Map frontend column keys to database columns
            $columnMapping = [
                'id' => 'id',
                'username' => 'username',
                'email' => 'email',
                'role' => 'role',
                'birthday' => 'birthday',
                'created_at' => 'created_at',
            ];

            if (array_key_exists($sortBy, $columnMapping)) {
                $query->orderBy($columnMapping[$sortBy], $sortDirection);
            } else {
                $query->orderBy('id', 'asc'); // Default fallback
            }
        } else {
            $query->orderBy('id', 'asc'); // Default sort
        }

        if ($request->has('per_page') || $request->has('page')) {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            return $query->paginate($perPage, ['*'], 'page', $page);
        }

        return $query->get();
    }

    /**
     * @OA\Post(
     *     path="/api/user/feedback",
     *     summary="Submit user feedback",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="feedback", type="string", nullable=true, example="Great service!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Feedback submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Feedback submitted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function submitFeedback(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'feedback' => 'nullable|string',
        ]);

        $user->update(['feedback' => $request->feedback]);

        return response()->json(['message' => 'Feedback submitted successfully']);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}/role",
     *     summary="Update user role",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", enum={"user", "admin", "super_admin"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Super admin access required"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function updateRole(Request $request, User $user)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'role' => ['required', Rule::in(['user', 'admin', 'super_admin'])],
        ]);

        $user->update(['role' => $request->role]);

        return $user;
    }

    private function authorizeAdmin()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'super_admin'])) {
            abort(403, 'Admin access required');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "username", "email", "password", "role"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123!"),
     *             @OA\Property(property="role", type="string", enum={"user", "admin", "super_admin"}, example="user"),
     *             @OA\Property(property="birthday", type="string", format="date", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:user,admin,super_admin',
            'birthday' => 'nullable|date|before:today',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'birthday' => $request->birthday,
        ]);

        return response()->json($user, 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Super admin access required"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function destroy(User $user)
    {
        $this->authorizeSuperAdmin();

        // Prevent deleting self
        if ($user->id === auth()->id()) {
            abort(403, 'Cannot delete your own account');
        }

        $user->delete();

        return response()->noContent();
    }

    private function authorizeSuperAdmin()
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'Super admin access required');
        }
    }
}
