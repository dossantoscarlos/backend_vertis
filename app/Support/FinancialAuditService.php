<?php

namespace App\Support;

use App\Models\FinancialTransaction;
use App\Models\AuditoriaFinanceira;
use App\Models\User;
use App\Models\Campaign;
use App\Models\CampaignLocation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FinancialAuditService
{
    /**
     * Audit a financial transaction creation.
     *
     * @param FinancialTransaction $tx
     * @param array $payload
     * @return void
     */
    public static function audit(FinancialTransaction $tx, array $payload): void
    {
        try {
            $request = request();

            // 1. Resolve logged-in user
            $user = Auth::user();
            if (!$user && $request) {
                $email = $request->header('X-User-Email');
                if ($email) {
                    $user = User::where('email', $email)->first();
                }
            }

            $usuarioLogadoId = $user ? $user->id : ($request ? $request->header('X-User-Id') : null);
            $usuarioLogadoNome = $user ? $user->name : ($request ? $request->header('X-User-Name') : 'Sistema');

            // 2. Resolve approver user if name is provided
            $approverName = $tx->approver;
            $approverUser = null;
            if ($approverName) {
                $approverUser = User::where('name', $approverName)->first();
            }

            // 3. Resolve and map entity type and descriptive name
            $tipoEntidade = strtoupper($tx->entity_type);
            $entityDesc = $tx->entity_external_id;

            if ($tx->entity_type === 'campanha') {
                $tipoEntidade = 'CAMPANHA';
                $entity = Campaign::where('external_id', $tx->entity_external_id)->first();
                $entityDesc = $entity ? $entity->name : $tx->entity_external_id;
            } elseif ($tx->entity_type === 'locais') {
                $entity = CampaignLocation::where('external_id', $tx->entity_external_id)->first();
                if ($entity && str_contains(strtolower($entity->type), 'comit')) {
                    $tipoEntidade = 'COMITE';
                } else {
                    $tipoEntidade = 'LOCAL_HABITACAO';
                }
                $entityDesc = $entity ? $entity->name : $tx->entity_external_id;
            } elseif ($tx->entity_type === 'eventos') {
                $tipoEntidade = 'EVENTO';
                $entityDesc = 'Evento ' . $tx->entity_external_id;
            }

            $tipoLancamento = strtoupper($tx->type); // RECEITA or DESPESA
            $valorFormatado = number_format($tx->final_cost, 2, ',', '.');
            
            // Format example: Despesa de R$ 1.500,00 lançada para Campanha X por João Silva
            $tipoLancamentoPt = $tipoLancamento === 'RECEITA' ? 'Receita' : 'Despesa';
            $descricaoCurta = "{$tipoLancamentoPt} de R$ {$valorFormatado} lançada para {$entityDesc} por {$usuarioLogadoNome}";

            AuditoriaFinanceira::create([
                'lancamento_id' => $tx->external_id,
                'tipo_lancamento' => $tipoLancamento,
                'valor' => $tx->final_cost,
                'tipo_entidade' => $tipoEntidade,
                'entidade_id' => $tx->entity_external_id,
                'entidade_descricao' => $entityDesc,
                'usuario_logado_id' => $usuarioLogadoId,
                'usuario_logado_nome' => $usuarioLogadoNome,
                'aprovado_por_id' => $approverUser ? $approverUser->id : null,
                'aprovado_por_nome' => $approverName,
                'descricao_curta' => $descricaoCurta,
                'payload' => $payload,
                'criado_em' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao salvar auditoria financeira: ' . $e->getMessage(), [
                'exception' => $e,
                'lancamento_id' => $tx->external_id,
            ]);
        }
    }
}
