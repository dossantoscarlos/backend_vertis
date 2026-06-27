<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['external_id', 'name', 'type', 'contact', 'phone', 'region_external_id', 'status'])]
class Partner extends Model
{
    public function getRouteKeyName(): string
    {
        return 'external_id';
    }
}
