<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'external_id',
    'type',
    'transaction_date',
    'competency_date',
    'projected_cost',
    'final_cost',
    'entity_type',
    'entity_external_id',
    'responsible',
    'approver',
])]
class FinancialTransaction extends Model
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
            'transaction_date' => 'date:Y-m-d',
            'competency_date' => 'date:Y-m-d',
            'projected_cost' => 'decimal:2',
            'final_cost' => 'decimal:2',
        ];
    }
}
