<?php

namespace App\Support;

use App\Models\User;

class SupportAccess
{
    /**
     * @return array<string, int>
     */
    private function supportRanks(): array
    {
        return [
            'role-suporte-n1' => 1,
            'role-suporte-n2' => 2,
            'role-suporte-n3' => 3,
        ];
    }

    public function supportRank(?string $roleId): int
    {
        return $this->supportRanks()[$roleId ?? ''] ?? 0;
    }

    public function supportLevelLabel(?string $roleId): ?string
    {
        return match ($this->supportRank($roleId)) {
            1 => 'N1',
            2 => 'N2',
            3 => 'N3',
            default => null,
        };
    }

    public function canAccess(User $user, int $minimumRank = 1): bool
    {
        if ($user->status !== 'ativo') {
            return false;
        }

        return $this->supportRank($this->primaryRoleId($user)) >= $minimumRank;
    }

    public function primaryRoleId(User $user): ?string
    {
        $role = $user->roles->first();

        return $role?->external_id;
    }
}
