<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesInternalApiRequests;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\DashboardSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class DashboardUserController extends Controller
{
    use AuthorizesInternalApiRequests;

    public function index(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json(User::query()->with('roles')->oldest('id')->get()->map(fn (User $user): array => $serializer->user($user))->values());
    }

    public function store(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        $data = $this->validated($request);
        $user = User::query()->create($this->toAttributes($data, $data['id'] ?? 'usr-'.Str::ulid()));
        $this->syncRole($user, $data['roleId']);

        return response()->json($serializer->user($user->load('roles')), 201);
    }

    public function show(Request $request, User $user, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json($serializer->user($user->load('roles')));
    }

    public function update(Request $request, User $user, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        $data = $this->validated($request, $user);
        $user->update($this->toAttributes($data, $user->external_id ?? (string) $user->getKey(), false));
        $this->syncRole($user, $data['roleId']);

        return response()->json($serializer->user($user->refresh()->load('roles')));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorizeInternalRequest($request);
        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'id' => ['sometimes', 'string', Rule::unique('users', 'external_id')->ignore($user)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'roleId' => ['required', 'string', 'exists:roles,external_id'],
            'status' => ['required', 'string', 'max:255'],
            'createdAt' => ['sometimes', 'date'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function toAttributes(array $data, string $externalId, bool $includePassword = true): array
    {
        $attributes = [
            'external_id' => $externalId,
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
            'status' => $data['status'],
        ];

        if ($includePassword) {
            $attributes['password'] = Hash::make(Str::random(32));
            $attributes['email_verified_at'] = now();
        }

        return $attributes;
    }

    private function syncRole(User $user, string $roleId): void
    {
        $role = Role::query()->where('external_id', $roleId)->firstOrFail();
        $user->syncRoles([$role->name]);
    }
}
