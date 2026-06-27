<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesInternalApiRequests;
use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Support\DashboardSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegionController extends Controller
{
    use AuthorizesInternalApiRequests;

    public function index(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json(Region::query()->oldest('id')->get()->map(fn (Region $region): array => $serializer->region($region))->values());
    }

    public function store(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        $data = $this->validated($request);
        $region = Region::query()->create($this->toAttributes($data, $data['id'] ?? 'reg-'.Str::ulid()));

        return response()->json($serializer->region($region), 201);
    }

    public function show(Request $request, Region $region, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json($serializer->region($region));
    }

    public function update(Request $request, Region $region, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        $region->update($this->toAttributes($this->validated($request, $region), $region->external_id));

        return response()->json($serializer->region($region->refresh()));
    }

    public function destroy(Request $request, Region $region): JsonResponse
    {
        $this->authorizeInternalRequest($request);
        $region->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Region $region = null): array
    {
        return $request->validate([
            'id' => ['sometimes', 'string', Rule::unique('regions', 'external_id')->ignore($region)],
            'name' => ['required', 'string', 'max:255'],
            'uf' => ['required', 'string', 'size:2'],
            'municipalities' => ['required', 'integer', 'min:0'],
            'population' => ['required', 'integer', 'min:0'],
            'coordinator' => ['required', 'string', 'max:255'],
            'voteGoal' => ['nullable', 'integer', 'min:0'],
            'votesProjected' => ['nullable', 'integer', 'min:0'],
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
            'uf' => Str::upper($data['uf']),
            'municipalities' => $data['municipalities'],
            'population' => $data['population'],
            'coordinator' => $data['coordinator'],
            'vote_goal' => $data['voteGoal'] ?? null,
            'votes_projected' => $data['votesProjected'] ?? null,
        ];
    }
}
