<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            "email" => ["required", "email"],
            "password" => ["required", "string"],
        ]);

        $user = User::query()
            ->with("roles.permissions")
            ->where("email", str($credentials["email"])->lower()->toString())
            ->first();

        if (! $user || ! Hash::check($credentials["password"], $user->password)) {
            throw ValidationException::withMessages([
                "email" => ["E-mail ou senha incorretos."],
            ]);
        }

        if ($user->status !== "ativo") {
            throw ValidationException::withMessages([
                "email" => ["Usuário sem acesso ativo ao sistema."],
            ]);
        }

        $role = $user->roles->first();
        $permissions = $user->roles
            ->pluck("permissions")
            ->flatten()
            ->pluck("name")
            ->unique()
            ->sort()
            ->values()
            ->all();

        return response()->json([
            "user" => [
                "id" => $user->external_id ?? (string) $user->getKey(),
                "name" => $user->name,
                "email" => $user->email,
                "roleId" => $role?->external_id ?? "",
                "roleName" => $role?->name ?? "",
                "supportLevel" => $this->supportLevel($role?->external_id),
                "permissions" => $permissions,
            ],
        ]);
    }

    private function supportLevel(?string $roleId): ?string
    {
        return match ($roleId) {
            "role-suporte-n1" => "N1",
            "role-suporte-n2" => "N2",
            "role-suporte-n3" => "N3",
            default => null,
        };
    }
}
