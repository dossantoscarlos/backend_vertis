<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesInternalApiRequests;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Support\DashboardSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    use AuthorizesInternalApiRequests;

    public function index(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json(Campaign::query()->oldest('id')->get()->map(fn (Campaign $campaign): array => $serializer->campaign($campaign))->values());
    }

    public function store(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        $data = $this->validated($request);
        $campaign = Campaign::query()->create($this->toAttributes($data, $data['id'] ?? 'cam-'.Str::ulid()));

        return response()->json($serializer->campaign($campaign), 201);
    }

    public function show(Request $request, Campaign $campaign, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json($serializer->campaign($campaign));
    }

    public function update(Request $request, Campaign $campaign, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);
        $campaign->update($this->toAttributes($this->validated($request, $campaign), $campaign->external_id));

        return response()->json($serializer->campaign($campaign->refresh()));
    }

    public function destroy(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorizeInternalRequest($request);
        $campaign->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Campaign $campaign = null): array
    {
        return $request->validate([
            'id' => ['sometimes', 'string', Rule::unique('campaigns', 'external_id')->ignore($campaign)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'regionId' => ['required', 'string', 'exists:regions,external_id'],
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
            'status' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'responsible' => ['required', 'string', 'max:255'],
            'voteGoal' => ['nullable', 'integer', 'min:0'],
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
            'type' => $data['type'],
            'region_external_id' => $data['regionId'],
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
            'status' => $data['status'],
            'description' => $data['description'] ?? '',
            'responsible' => $data['responsible'],
            'vote_goal' => $data['voteGoal'] ?? null,
        ];
    }
}
