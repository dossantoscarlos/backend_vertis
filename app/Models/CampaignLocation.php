<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['external_id', 'name', 'address', 'region_external_id', 'type', 'capacity', 'responsible'])]
class CampaignLocation extends Model
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
            'capacity' => 'integer',
        ];
    }
}
