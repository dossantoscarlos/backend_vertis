<?php

namespace App\Support;

use App\Models\Campaign;
use App\Models\CampaignLocation;
use App\Models\Partner;
use App\Models\Region;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DashboardSerializer
{
    /**
     * @return array{id: string, name: string, uf: string, municipalities: int, population: int, coordinator: string, voteGoal?: int|null, votesProjected?: int|null}
     */
    public function region(Region $region): array
    {
        return [
            'id' => $region->external_id,
            'name' => $region->name,
            'uf' => $region->uf,
            'municipalities' => $region->municipalities,
            'population' => $region->population,
            'coordinator' => $region->coordinator,
            'voteGoal' => $region->vote_goal,
            'votesProjected' => $region->votes_projected,
        ];
    }

    /**
     * @return array{id: string, name: string, type: string, regionId: string, startDate: string, endDate: string, status: string, description: string, responsible: string, voteGoal?: int|null}
     */
    public function campaign(Campaign $campaign): array
    {
        return [
            'id' => $campaign->external_id,
            'name' => $campaign->name,
            'type' => $campaign->type,
            'regionId' => $campaign->region_external_id,
            'startDate' => $campaign->start_date->toDateString(),
            'endDate' => $campaign->end_date->toDateString(),
            'status' => $campaign->status,
            'description' => (string) $campaign->description,
            'responsible' => (string) $campaign->responsible,
            'voteGoal' => $campaign->vote_goal,
        ];
    }

    /**
     * @return array{id: string, name: string, type: string, contact: string, phone: string, regionId: string, status: string}
     */
    public function partner(Partner $partner): array
    {
        return [
            'id' => $partner->external_id,
            'name' => $partner->name,
            'type' => $partner->type,
            'contact' => $partner->contact,
            'phone' => $partner->phone,
            'regionId' => $partner->region_external_id,
            'status' => $partner->status,
        ];
    }

    /**
     * @return array{id: string, name: string, address: string, regionId: string, type: string, capacity: int, responsible: string}
     */
    public function location(CampaignLocation $location): array
    {
        return [
            'id' => $location->external_id,
            'name' => $location->name,
            'address' => $location->address,
            'regionId' => $location->region_external_id,
            'type' => $location->type,
            'capacity' => $location->capacity,
            'responsible' => $location->responsible,
        ];
    }

    /**
     * @return array{id: string, name: string, email: string, roleId: string, status: string, createdAt: string}
     */
    public function user(User $user): array
    {
        $role = $user->roles->first();

        return [
            'id' => $user->external_id ?? (string) $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'roleId' => $role?->external_id ?? '',
            'status' => $user->status,
            'createdAt' => $user->created_at->toDateString(),
        ];
    }

    /**
     * @return array{id: string, name: string, description: string, permissions: array<int, string>}
     */
    public function role(Role $role): array
    {
        return [
            'id' => $role->external_id ?? str($role->name)->slug()->prepend('role-')->toString(),
            'name' => $role->name,
            'description' => (string) $role->description,
            'permissions' => $role->permissions->pluck('name')->sort()->values()->all(),
        ];
    }

    /**
     * @return array{id: string, type: string, transactionDate: string, competencyDate: string, projectedCost: float, finalCost: float, entityType: string, entityExternalId: string, responsible: string, approver: string|null}
     */
    public function financialTransaction(\App\Models\FinancialTransaction $tx): array
    {
        return [
            'id' => $tx->external_id,
            'type' => $tx->type,
            'transactionDate' => $tx->transaction_date->toDateString(),
            'competencyDate' => $tx->competency_date->toDateString(),
            'projectedCost' => (float) $tx->projected_cost,
            'finalCost' => (float) $tx->final_cost,
            'entityType' => $tx->entity_type,
            'entityExternalId' => $tx->entity_external_id,
            'responsible' => $tx->responsible,
            'approver' => $tx->approver,
        ];
    }

    /**
     * @return array{id: string, name: string, description: string, startDate: string, endDate: string, type: string, responsible: string, targetAudience: string, link: string|null}
     */
    public function survey(\App\Models\Survey $survey): array
    {
        return [
            'id' => $survey->external_id,
            'name' => $survey->name,
            'description' => (string) $survey->description,
            'startDate' => $survey->start_date->toDateString(),
            'endDate' => $survey->end_date->toDateString(),
            'type' => $survey->type,
            'responsible' => $survey->responsible,
            'targetAudience' => $survey->target_audience,
            'link' => $survey->link,
        ];
    }
}
