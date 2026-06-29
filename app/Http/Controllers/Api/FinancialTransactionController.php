<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesInternalApiRequests;
use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Support\DashboardSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FinancialTransactionController extends Controller
{
    use AuthorizesInternalApiRequests;

    public function index(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json(
            FinancialTransaction::query()
                ->latest('id')
                ->get()
                ->map(fn (FinancialTransaction $tx): array => $serializer->financialTransaction($tx))
                ->values()
        );
    }

    public function store(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        $data = $this->validated($request);
        $tx = FinancialTransaction::query()->create(
            $this->toAttributes($data, $data['id'] ?? 'fin-'.Str::ulid())
        );

        return response()->json($serializer->financialTransaction($tx), 201);
    }

    public function show(Request $request, FinancialTransaction $financialTransaction, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        return response()->json($serializer->financialTransaction($financialTransaction));
    }

    public function update(Request $request, FinancialTransaction $financialTransaction, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);
        
        $data = $this->validated($request, $financialTransaction);
        $financialTransaction->update($this->toAttributes($data, $financialTransaction->external_id));

        return response()->json($serializer->financialTransaction($financialTransaction->refresh()));
    }

    public function destroy(Request $request, FinancialTransaction $financialTransaction): JsonResponse
    {
        $this->authorizeInternalRequest($request);
        $financialTransaction->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?FinancialTransaction $tx = null): array
    {
        $data = $request->validate([
            'id' => ['sometimes', 'string', Rule::unique('financial_transactions', 'external_id')->ignore($tx)],
            'type' => ['required', 'string', 'in:receita,despesa'],
            'transactionDate' => ['required', 'date'],
            'competencyDate' => ['nullable', 'date'],
            'projectedCost' => ['required', 'numeric', 'min:0'],
            'finalCost' => ['required', 'numeric', 'min:0'],
            'entityType' => ['required', 'string', 'in:campanha,locais,eventos'],
            'entityExternalId' => ['required', 'string', 'max:255'],
            'responsible' => ['required', 'string', 'max:255'],
            'approver' => ['required', 'string', 'max:255'],
        ]);

        if (empty($data['competencyDate'])) {
            $data['competencyDate'] = $data['transactionDate'];
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function toAttributes(array $data, string $externalId): array
    {
        return [
            'external_id' => $externalId,
            'type' => $data['type'],
            'transaction_date' => $data['transactionDate'],
            'competency_date' => $data['competencyDate'],
            'projected_cost' => $data['projectedCost'],
            'final_cost' => $data['finalCost'],
            'entity_type' => $data['entityType'],
            'entity_external_id' => $data['entityExternalId'],
            'responsible' => $data['responsible'],
            'approver' => $data['approver'] ?? null,
        ];
    }
}
