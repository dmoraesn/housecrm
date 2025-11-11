<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Throwable;

class LeadController extends Controller
{
    /**
     * Atualiza o status e ordem de um lead movido no Kanban.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request): JsonResponse
    {
        // Validação de entrada
        $validated = $request->validate([
            'id'     => ['required', 'integer', 'exists:leads,id'],
            'status' => ['required', 'string', 'max:100'],
            'order'  => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            // Transação para segurança de escrita
            DB::beginTransaction();

            /** @var \App\Models\Lead $lead */
            $lead = Lead::findOrFail($validated['id']);

            $novoStatus = $validated['status'];
            $novaOrdem  = $validated['order'] ?? 0;

            // Atualiza ordem dos demais leads no mesmo status
            $this->ajustarOrdem($lead, $novoStatus, $novaOrdem);

            // Atualiza o lead movido
            $lead->update([
                'status' => $novoStatus,
                'order'  => $novaOrdem,
            ]);

            DB::commit();

            Log::info('✅ Lead atualizado via Kanban.', [
                'lead_id' => $lead->id,
                'novo_status' => $lead->status,
                'nova_ordem' => $lead->order,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lead atualizado com sucesso.',
                'lead' => [
                    'id' => $lead->id,
                    'status' => $lead->status,
                    'order' => $lead->order,
                ],
            ], 200);
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('❌ Erro ao atualizar lead no Kanban.', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar o status do lead.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorganiza a ordem dos leads quando um lead é movido de coluna
     * ou de posição dentro da mesma coluna.
     */
    private function ajustarOrdem(Lead $lead, string $novoStatus, int $novaOrdem): void
    {
        // Se o lead foi movido para outra coluna, libera sua posição antiga
        if ($lead->status !== $novoStatus) {
            Lead::where('status', $lead->status)
                ->where('id', '<>', $lead->id)
                ->orderBy('order')
                ->get()
                ->each(function ($l, $index) {
                    $l->update(['order' => $index]);
                });
        }

        // Reordena os leads na nova coluna
        $leadsNaColuna = Lead::where('status', $novoStatus)
            ->where('id', '<>', $lead->id)
            ->orderBy('order')
            ->get();

        $novaSequencia = [];
        foreach ($leadsNaColuna as $index => $l) {
            $novaSequencia[$index >= $novaOrdem ? $index + 1 : $index] = $l->id;
        }

        foreach ($novaSequencia as $ordem => $id) {
            Lead::where('id', $id)->update(['order' => $ordem]);
        }
    }
}
