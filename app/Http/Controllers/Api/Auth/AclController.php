<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Api\Auth\AclUserRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\DashboardSerializer;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class AclController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(AclUserRequest $request, DashboardSerializer $serializer): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->email())
            ->firstOrFail();

        $user->load('roles.permissions');

        $userRoles = $user->roles
            ->map(fn ($role): array => $serializer->role($role))
            ->values();
        $roles = Role::query()
            ->with('permissions')
            ->get()
            ->map(fn (Role $role): array => $serializer->role($role))
            ->values();

        return response()->json([
            'user' => [
                'id' => $user->external_id ?? (string) $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $userRoles->pluck('id')->all(),
                'permissions' => $userRoles->pluck('permissions')->flatten()->unique()->values()->all(),
            ],
            'roles' => $roles,
            'availablePermissions' => config('acl.permissions', []),
        ]);
    }

}
