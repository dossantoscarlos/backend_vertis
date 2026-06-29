<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['external_id', 'name', 'type', 'region_external_id', 'start_date', 'end_date', 'status', 'description', 'responsible', 'vote_goal'])]
class Campaign extends Model
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
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'vote_goal' => 'integer',
        ];
    }
}
