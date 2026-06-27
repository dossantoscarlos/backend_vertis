<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesInternalApiRequests;
use App\Http\Controllers\Controller;
use App\Support\DashboardSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    use AuthorizesInternalApiRequests;

    public function index(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json(Role::query()->with('permissions')->oldest('id')->get()->map(fn (Role $role): array => $serializer->role($role))->values());
    }

    public function store(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        $data = $this->validated($request);
        $role = Role::query()->create([
            'external_id' => $data['id'] ?? 'role-'.Str::ulid(),
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'guard_name' => 'web',
        ]);
        $this->syncPermissions($role, $data['permissions']);

        return response()->json($serializer->role($role->load('permissions')), 201);
    }

    public function show(Request $request, string $role, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json($serializer->role($this->findRole($role)->load('permissions')));
    }

    public function update(Request $request, string $role, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        $roleModel = $this->findRole($role);
        $data = $this->validated($request, $roleModel);
        $roleModel->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
        ]);
        $this->syncPermissions($roleModel, $data['permissions']);

        return response()->json($serializer->role($roleModel->refresh()->load('permissions')));
    }

    public function destroy(Request $request, string $role): JsonResponse
    {
        $this->authorizeInternalRequest($request);
        $this->findRole($role)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'id' => ['sometimes', 'string', Rule::unique('roles', 'external_id')->ignore($role)],
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role)],
            'description' => ['nullable', 'string'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'string', 'exists:permissions,name'],
        ]);
    }

    private function findRole(string $role): Role
    {
        return Role::query()->where('external_id', $role)->firstOrFail();
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function syncPermissions(Role $role, array $permissions): void
    {
        $permissionModels = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $permissions)
            ->get();

        $role->permissions()->sync($permissionModels->pluck('id'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
