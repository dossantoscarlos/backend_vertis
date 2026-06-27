<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesInternalApiRequests;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Support\DashboardSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    use AuthorizesInternalApiRequests;

    public function index(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json(Partner::query()->oldest('id')->get()->map(fn (Partner $partner): array => $serializer->partner($partner))->values());
    }

    public function store(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        $data = $this->validated($request);
        $partner = Partner::query()->create($this->toAttributes($data, $data['id'] ?? 'par-'.Str::ulid()));

        return response()->json($serializer->partner($partner), 201);
    }

    public function show(Request $request, Partner $partner, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json($serializer->partner($partner));
    }

    public function update(Request $request, Partner $partner, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);
        $partner->update($this->toAttributes($this->validated($request, $partner), $partner->external_id));

        return response()->json($serializer->partner($partner->refresh()));
    }

    public function destroy(Request $request, Partner $partner): JsonResponse
    {
        $this->authorizeInternalRequest($request);
        $partner->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Partner $partner = null): array
    {
        return $request->validate([
            'id' => ['sometimes', 'string', Rule::unique('partners', 'external_id')->ignore($partner)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'regionId' => ['required', 'string', 'exists:regions,external_id'],
            'status' => ['required', 'string', 'max:255'],
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
            'contact' => $data['contact'],
            'phone' => $data['phone'],
            'region_external_id' => $data['regionId'],
            'status' => $data['status'],
        ];
    }
}
