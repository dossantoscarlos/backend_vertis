<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesInternalApiRequests;
use App\Http\Controllers\Controller;
use App\Models\CampaignLocation;
use App\Support\DashboardSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CampaignLocationController extends Controller
{
    use AuthorizesInternalApiRequests;

    public function index(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json(CampaignLocation::query()->oldest('id')->get()->map(fn (CampaignLocation $campaignLocation): array => $serializer->location($campaignLocation))->values());
    }

    public function store(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        $data = $this->validated($request);
        $campaignLocation = CampaignLocation::query()->create($this->toAttributes($data, $data['id'] ?? 'loc-'.Str::ulid()));

        return response()->json($serializer->location($campaignLocation), 201);
    }

    public function show(Request $request, CampaignLocation $campaignLocation, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json($serializer->location($campaignLocation));
    }

    public function update(Request $request, CampaignLocation $campaignLocation, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);
        $campaignLocation->update($this->toAttributes($this->validated($request, $campaignLocation), $campaignLocation->external_id));

        return response()->json($serializer->location($campaignLocation->refresh()));
    }

    public function destroy(Request $request, CampaignLocation $campaignLocation): JsonResponse
    {
        $this->authorizeInternalRequest($request);
        $campaignLocation->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?CampaignLocation $campaignLocation = null): array
    {
        return $request->validate([
            'id' => ['sometimes', 'string', Rule::unique('campaign_locations', 'external_id')->ignore($campaignLocation)],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'regionId' => ['required', 'string', 'exists:regions,external_id'],
            'type' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:0'],
            'responsible' => ['required', 'string', 'max:255'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function toAttributes(array $data, string $externalId): array
    {
        return [
            'external_id' => $externalId,
            'name' => $data['name'],
            'address' => $data['address'],
            'region_external_id' => $data['regionId'],
            'type' => $data['type'],
            'capacity' => $data['capacity'],
            'responsible' => $data['responsible'],
        ];
    }
}
