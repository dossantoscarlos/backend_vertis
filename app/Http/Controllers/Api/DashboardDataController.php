<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesInternalApiRequests;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignLocation;
use App\Models\Partner;
use App\Models\Region;
use App\Models\User;
use App\Support\DashboardSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DashboardDataController extends Controller
{
    use AuthorizesInternalApiRequests;

    public function __invoke(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json([
            'regions' => Region::query()->oldest('id')->get()->map(fn (Region $region): array => $serializer->region($region))->values(),
            'campaigns' => Campaign::query()->oldest('id')->get()->map(fn (Campaign $campaign): array => $serializer->campaign($campaign))->values(),
            'partners' => Partner::query()->oldest('id')->get()->map(fn (Partner $partner): array => $serializer->partner($partner))->values(),
            'locations' => CampaignLocation::query()->oldest('id')->get()->map(fn (CampaignLocation $location): array => $serializer->location($location))->values(),
            'users' => User::query()->with('roles')->oldest('id')->get()->map(fn (User $user): array => $serializer->user($user))->values(),
            'roles' => Role::query()->with('permissions')->oldest('id')->get()->map(fn (Role $role): array => $serializer->role($role))->values(),
            'availablePermissions' => config('acl.permissions', []),
        ]);
    }
}
