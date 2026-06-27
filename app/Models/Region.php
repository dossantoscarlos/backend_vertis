<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['external_id', 'name', 'uf', 'municipalities', 'population', 'coordinator', 'vote_goal', 'votes_projected'])]
class Region extends Model
{
    public function getRouteKeyName(): string
    {
        return 'external_id';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'municipalities' => 'integer',
            'population' => 'integer',
            'vote_goal' => 'integer',
            'votes_projected' => 'integer',
        ];
    }
}
