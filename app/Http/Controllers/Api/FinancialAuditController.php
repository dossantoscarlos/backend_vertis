<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesInternalApiRequests;
use App\Http\Controllers\Controller;
use App\Models\AuditoriaFinanceira;
use App\Models\User;
use App\Support\DashboardSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialAuditController extends Controller
{
    use AuthorizesInternalApiRequests;

    /**
     * Get list of audit logs.
     *
     * @param Request $request
     * @param DashboardSerializer $serializer
     * @return JsonResponse
     */
    public function index(Request $request, DashboardSerializer $serializer): JsonResponse
    {
        $this->authorizeInternalRequest($request);

        // Verify that the forwarded user has the required permission
        $email = $request->header('X-User-Email');
        $user = $email ? User::where('email', $email)->first() : null;
        abort_unless($user && $user->can('parecer:visualizar'), 403, 'Acesso negado.');

        $audits = AuditoriaFinanceira::query()
            ->orderBy('criado_em', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn (AuditoriaFinanceira $audit): array => $serializer->financialAudit($audit))
            ->values();

        return response()->json($audits);
    }
}
