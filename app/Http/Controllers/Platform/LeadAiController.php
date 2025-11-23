<?php
namespace App\Http\Controllers\Platform;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\AITemplate;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Exception;
class LeadAiController extends Controller
{
    /**
     * Gera follow-up com IA para um lead.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateFollowUp(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'contexto_extra' => 'nullable|string'
        ]);
        try {
            $lead = Lead::findOrFail($request->lead_id);
            
            // --- CORREÇÃO DE SINTAXE (PARSE ERROR) ---
            // Substituindo o operador Null Coalescing (??) por Operador Ternário
            // para compatibilidade com versões antigas do PHP (< 7.0).
            $interesse = isset($lead->interesse) ? $lead->interesse : 'imóvel em geral';
            $ultimoContato = isset($lead->ultimo_contato) ? $lead->ultimo_contato : 'nenhum';
            $contextoExtra = isset($request->contexto_extra) ? $request->contexto_extra : 'nenhum';
            
            // Integração com AITemplate
            $data = [
                'nome' => $lead->nome,
                'email' => $lead->email ?? '',
                'telefone' => $lead->telefone ?? '',
                'origem' => optional($lead->origem)->nome ?? $lead->origem->name ?? '', // << CORREÇÃO AQUI
                'mensagem' => $lead->mensagem ?? '',
                'valor_interesse' => $interesse,
                'observacoes' => $ultimoContato . '. Contexto extra: ' . $contextoExtra,
            ];
            $status = $request->status ?? $lead->status ?? 'novo'; // Fallback para status
            $prompt = AITemplate::getFormattedPrompt($status, $data);
            
            if (empty($prompt)) {
                throw new Exception('Template de prompt não encontrado para o status: ' . $status);
            }
            
            // Usando a configuração do .env (config('openai.model', 'gpt-3.5-turbo'))
            $aiModel = config('openai.model', 'gpt-3.5-turbo');
            $result = OpenAI::chat()->create([
                'model' => $aiModel,
                'messages' => [
                    ['role' => 'system', 'content' => 'Você é um corretor de imóveis experiente.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => AITemplate::getTemplate($status)['max_tokens'] ?? 200,
                'temperature' => 0.7,
            ]);
            $followup = trim($result->choices[0]->message->content);
            // Opcional: salvar no lead como nota
            // $lead->notas()->create(['conteudo' => $followup, 'tipo' => 'followup_ia']);
            return response()->json([
                'success' => true,
                'message' => $followup,
                'lead_id' => $lead->id
            ]);
        } catch (Exception $e) {
            \Log::error('Erro na geração de follow-up IA: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao gerar follow-up: ' . $e->getMessage()
            ], 500);
        }
    }
}