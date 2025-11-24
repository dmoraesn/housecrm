<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\AITemplate;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Enums\LeadStatus;

class LeadAiController extends Controller
{
    public function generateFollowUp(Request $request)
    {
        $request->validate([
            'lead_id'        => 'required|exists:leads,id',
            'contexto_extra' => 'nullable|string|max:1000',
        ]);

        try {
            $lead = Lead::findOrFail($request->lead_id);

            // 🔥 CORREÇÃO: converter ENUM → string
            $status = $lead->status instanceof LeadStatus
                ? $lead->status->value
                : ($lead->status ?? 'novo');

            // Dados do lead com fallbacks seguros
            $leadData = [
                'nome'              => $lead->nome,
                'email'             => $lead->email ?? '',
                'telefone'          => $lead->telefone ?? '',
                'origem'            => $lead->origem ?? 'não informado',
                'mensagem'          => $lead->mensagem ?? '',
                'valor_interesse'   => $lead->valor_interesse ?? 0,
                'observacoes'       => $lead->observacoes ?? '',
                'gancho_selecionado'=> $request->contexto_extra ?? '',
                'contexto_extra'    => $request->contexto_extra ?? '',
            ];

            // Usa o template ativo ou fallback interno
            $template = AITemplate::getTemplate($status);
            if (!$template) {
                throw new Exception("Nenhum prompt configurado para o status: {$status}");
            }

            $prompt = AITemplate::getFormattedPrompt($status, $leadData);
            if (empty($prompt)) {
                throw new Exception("Falha ao formatar o prompt para o lead #{$lead->id}");
            }

            $response = OpenAI::chat()->create([
                'model'       => config('services.openai.model', 'gpt-4o-mini'),
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'Você é um corretor de imóveis experiente, educado e proativo. Responda sempre em português brasileiro, de forma natural e humana.'
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt
                    ],
                ],
                'max_tokens'  => $template['max_tokens'] ?? 250,
                'temperature' => 0.8,
            ]);

            $message = trim($response->choices[0]->message->content ?? '');
            $tokens  = $response->usage->totalTokens ?? 0;

            return response()->json([
                'success' => true,
                'message' => $message,
                'tokens'  => $tokens,
                'model'   => $response->model,
            ]);

        } catch (Exception $e) {
            Log::error('Erro ao gerar follow-up com IA', [
                'lead_id' => $request->lead_id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Não foi possível gerar a mensagem. Tente novamente.',
            ], 500);
        }
    }
}
