<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\AITemplate;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
// REMOVIDO: use App\Models\LeadStatus; <-- Causador do erro "Class not found"
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\App; // Adicionado para verificar o ambiente

class LeadAiController extends Controller
{
    public function generateFollowUp(Request $request)
    {
        $leadId         = $request->input('lead_id');
        $contextoExtra = $request->input('contexto_extra', '');

        $request->validate([
            'lead_id'        => 'required|integer|exists:leads,id',
            'contexto_extra' => 'nullable|string|max:1000',
        ]);

        $statusValue = null;

        try {
            $lead = Lead::findOrFail($leadId);

            // CORREÇÃO: Pega apenas o valor string do status.
            $statusValue = $lead->status;
            
            // CORREÇÃO: Se o status já for um Enum (do Model Lead), pega o valor. Caso contrário, usa a string.
            $statusString = $statusValue instanceof \BackedEnum
                ? $statusValue->value
                : ($statusValue ?? 'novo');

            // REMOVIDO: $statusEnum = LeadStatus::from($statusValue); 

            $leadData = [
                'nome'               => $lead->nome ?? 'Cliente',
                'email'              => $lead->email ?? '',
                'telefone'           => $lead->telefone_formatado ?? $lead->telefone ?? '',
                'origem'             => $lead->origem?->value ?? $lead->origem ?? 'não informado',
                'mensagem'           => $lead->mensagem ?? '',
                'valor_interesse'    => $lead->valor_interesse ?? 0,
                'observacoes'        => $lead->observacoes ?? '',
                'gancho_selecionado' => $contextoExtra,
                'contexto_extra'     => $contextoExtra,
            ];

            // Linha 49 do erro (aproximadamente)
            // REVERTIDO: Chama getTemplate com a STRING do status
            $template = AITemplate::getTemplate($statusString);

            if (!$template || empty($template['prompt'] ?? null)) {
                throw new \Exception("Template de IA não configurado para o status: {$statusString}");
            }

            // REVERTIDO: Chama getFormattedPrompt com a STRING do status
            $prompt = AITemplate::getFormattedPrompt($statusString, $leadData);

            if (empty($prompt)) {
                throw new \Exception("Prompt gerado vazio para o lead #{$lead->id}");
            }

            // CORREÇÃO: Usar o namespace 'openai.model' que foi configurado via AppServiceProvider
            // ou definido no config/openai.php, priorizando-o sobre o antigo 'services.openai.model'.
            $model = config('openai.model')
                ?? config('services.openai.model') // Mantém o fallback caso o AppServiceProvider não tenha sido corrigido
                ?? env('OPENAI_MODEL')
                ?? 'gpt-4o-mini';

            $response = OpenAI::chat()->create([
                'model'       => $model,
                // CORREÇÃO: Usar o valor de temperatura configurado em vez de um valor fixo
                'temperature' => (float) config('openai.temperature') ?? 0.8, 
                'max_tokens'  => $template['max_tokens'] ?? 400,
                'messages'    => [
                    ['role' => 'system', 'content' => 'Você é um corretor de imóveis experiente, educado e proativo. Responda em português brasileiro, de forma natural e persuasiva.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
            ]);

            $message = trim($response->choices[0]->message->content ?? '');

            if (empty($message)) {
                throw new \Exception('A IA retornou mensagem vazia');
            }

            $historico = $lead->fresh()->historico_interacoes ?? [];
            $historico[] = [
                'data'     => now()->toDateTimeString(),
                'acao'     => 'followup_ia',
                'mensagem' => $message,
                'resposta' => null,
            ];

            $lead->historico_interacoes = $historico;
            $lead->save();

            Toast::success('Mensagem gerada com sucesso!');

            return response()->json([
                'success'        => true,
                'content'        => $message,
                'tokens'         => $response->usage->totalTokens ?? 0,
                'model'          => $model,
                'historico_html' => $lead->fresh()->historico_formatado,
            ]);

        } catch (\Throwable $e) {
            
            // CORREÇÃO: Remoção da referência inválida a $this->app
            $errorMessage = "Erro interno.";
            // Uso correto da Facade App para verificar o modo debug
            if (App::runningInConsole() || config('app.debug')) {
                 // Apenas para debug: exibe o erro interno para desenvolvedores
                 $errorMessage = $e->getMessage();
            }

            // Captura o erro da API Key (problema original)
            if (str_contains($e->getMessage(), 'API key') || str_contains($e->getMessage(), 'The OpenAI API key')) {
                 $errorMessage = 'A chave da API do OpenAI está faltando ou inválida. Verifique GlobalSettings e .env.';
            }
            
            // Usa statusValue se estiver definido
            $logStatus = $statusValue ?? 'desconhecido';

            Log::error('Erro crítico no LeadAiController', [
                'lead_id'        => $leadId ?? null,
                'status'         => $logStatus,
                'contexto_extra' => $contextoExtra,
                'erro'           => $e->getMessage(),
                'arquivo'        => $e->getFile(),
                'linha'          => $e->getLine(),
                'trace'          => $e->getTraceAsString(),
            ]);

            Toast::error('Erro ao gerar mensagem com IA: '.$errorMessage);

            return response()->json([
                'success' => false,
                'error'   => $errorMessage,
            ], 500);
        }
    }
}