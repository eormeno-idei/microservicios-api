<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\UI\Support\UIDebug;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * Query params:
     * - per_page: items per page (default: 15)
     * - search: search by name or email
     * - sort_by: name|email|roles (default: created_at)
     * - sort_direction: asc|desc (default: desc)
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string', 'max:255'],
            'sort_by' => ['sometimes', 'in:name,email,roles,created_at'],
            'sort_direction' => ['sometimes', 'in:asc,desc'],
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDirection = $validated['sort_direction'] ?? 'desc';
        $search = $validated['search'] ?? null;
        if ($search === '') {
            $search = null;
        }

        $query = User::with('roles')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });

        // Ordenamiento especial para roles
        if ($sortBy === 'roles') {
            $query->withCount('roles')
                ->orderBy('roles_count', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $users = $query->paginate($perPage);

        // Transformar los datos para incluir roles como string
        $transformedUsers = $users->getCollection()->map(function ($user) {
            $rolesString = $user->roles
                ->pluck('name')
                ->sort()
                ->values()
                ->implode(', ');

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => $user->email_verified_at ? true : false,
                'roles' => $rolesString,
                'created_at' => $user->created_at->diffForHumans(),
                'updated_at' => $user->updated_at->diffForHumans(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Usuarios recuperados exitosamente',
            'data' => [
                'users' => $transformedUsers,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'total_pages' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total_items' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],
                'links' => [
                    'first' => $users->url(1),
                    'last' => $users->url($users->lastPage()),
                    'prev' => $users->previousPageUrl(),
                    'next' => $users->nextPageUrl(),
                ],
            ]
        ]);
    }

    public function count(Request $request): JsonResponse
    {
        $search = $request->query('search', null);
        if ($search === '') {
            $search = null;
        }

        $totalUsers = User::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })->count();

        UIDebug::info("Total users count with search term: " . ($search ?? 'null') . " is " . $totalUsers);

        return response()->json([
            'status' => 'success',
            'message' => 'Conteo de usuarios recuperado exitosamente',
            'data' => [
                'count' => $totalUsers,
            ]
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (isset($validated['roles'])) {
            $user->assignRole($validated['roles']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario creado exitosamente',
            'data' => $user->load('roles'),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => "Usuario $user->name recuperado exitosamente",
            'data' => $user->load('roles'),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'confirmed', Password::defaults()],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:roles,name'],
            "send_reset_email" => ['sometimes', 'boolean'],
            "send_verification_email" => ['sometimes', 'boolean'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update(array_filter($validated, fn($key) => $key !== 'roles', ARRAY_FILTER_USE_KEY));

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        if (!empty($validated['send_reset_email'])) {
            // Send password reset email
            $token = PasswordBroker::createToken($user);
            $user->sendPasswordResetNotification($token);
        }

        if (!empty($validated['send_verification_email'])) {
            // Mark email as unverified and send verification email
            $user->email_verified_at = null;
            $user->save();
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario actualizado exitosamente',
            'data' => $user->fresh()->load('roles'),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        // Delete the user if it is different from the currently authenticated user
        if (auth()->id() === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se puede eliminar el usuario autenticado actualmente',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Usuario $user->name eliminado exitosamente",
        ]);
    }
}
