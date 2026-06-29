<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Support\DashboardSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SurveyController extends Controller
{
    public function index(DashboardSerializer $serializer): JsonResponse
    {
        $surveys = Survey::query()->oldest('id')->get();
        return response()->json(
            $surveys->map(fn (Survey $survey) => $serializer->survey($survey))
        );
    }

    public function store(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $data = $this->validated($request);
        $externalId = $data['id'] ?? 'srv-' . time() . rand(100, 999);

        $survey = Survey::query()->create($this->toAttributes($data, $externalId));

        return response()->json($serializer->survey($survey), 201);
    }

    public function show(Survey $survey, DashboardSerializer $serializer): JsonResponse
    {
        return response()->json($serializer->survey($survey));
    }

    public function update(Request $request, Survey $survey, DashboardSerializer $serializer): JsonResponse
    {
        $data = $this->validated($request, $survey);

        $survey->update($this->toAttributes($data, $survey->external_id));

        return response()->json($serializer->survey($survey));
    }

    public function destroy(Survey $survey): JsonResponse
    {
        $survey->delete();
        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Survey $survey = null): array
    {
        return $request->validate([
            'id' => ['sometimes', 'string', Rule::unique('surveys', 'external_id')->ignore($survey)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
            'type' => ['required', 'string', 'in:online,porta'],
            'responsible' => ['required', 'string', 'max:255'],
            'targetAudience' => ['required', 'string', 'max:255'],
            'link' => ['required_if:type,online', 'nullable', 'string', 'max:1000'],
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
            'description' => $data['description'] ?? '',
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
            'type' => $data['type'],
            'responsible' => $data['responsible'],
            'target_audience' => $data['targetAudience'],
            'link' => $data['type'] === 'online' ? ($data['link'] ?? null) : null,
        ];
    }
}
