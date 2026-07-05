<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $ip = $request->ip();
        $lockoutKey = 'login_lockout:' . $ip;
        $attemptsKey = 'login_attempts:' . $ip;

        // Check if currently locked out
        if (Cache::has($lockoutKey)) {
            $lockoutTimestamp = Cache::get($lockoutKey);
            if (time() < $lockoutTimestamp) {
                $remainingSeconds = $lockoutTimestamp - time();
                $remainingMinutes = (int) ceil($remainingSeconds / 60);
                throw ValidationException::withMessages([
                    'email' => ["Muitas tentativas de login. Por favor, aguarde {$remainingMinutes} minutos."],
                ]);
            }
        }

        $credentials = $request->validate([
            "email" => ["required", "email"],
            "password" => ["required", "string"],
        ]);

        $user = User::query()
            ->with("roles.permissions")
            ->where("email", str($credentials["email"])->lower()->toString())
            ->first();

        if (! $user || ! Hash::check($credentials["password"], $user->password)) {
            $this->handleFailedAttempt($attemptsKey, $lockoutKey);

            throw ValidationException::withMessages([
                "email" => ["E-mail ou senha incorretos."],
            ]);
        }

        if ($user->status !== "ativo") {
            $this->handleFailedAttempt($attemptsKey, $lockoutKey);

            throw ValidationException::withMessages([
                "email" => ["Usuário sem acesso ativo ao sistema."],
            ]);
        }

        // Success! Clear attempts
        Cache::forget($attemptsKey);
        Cache::forget($lockoutKey);

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

    private function handleFailedAttempt(string $attemptsKey, string $lockoutKey): void
    {
        $attempts = Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addDays(1));

        if ($attempts >= 3) {
            $exponent = (int) floor(($attempts - 3) / 3);
            $waitMinutes = 12 * (3 ** $exponent);
            $lockoutEndTimestamp = time() + ($waitMinutes * 60);
            Cache::put($lockoutKey, $lockoutEndTimestamp, $waitMinutes * 60);

            throw ValidationException::withMessages([
                'email' => ["Limite de tentativas excedido. Aguarde {$waitMinutes} minutos."],
            ]);
        }
    }
}
