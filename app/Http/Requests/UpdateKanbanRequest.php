<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateKanbanRequest extends FormRequest
{
    private const STATUS_OPTIONS = [
        'novo',
        'qualificacao',
        'visita',
        'negociacao',
        'fechamento',
        'perdido',
    ];

    /**
     * Verifica permissão do usuário.
     */
    public function authorize(): bool
    {
        return $this->user()
            && $this->user()->hasAccess('platform.leads');
    }

    /**
     * Valida o payload enviado pelo frontend.
     * Agora está alinhado com o payload real:
     *
     * {
     *   id: int,
     *   status: string,
     *   order: int
     * }
     */
    public function rules(): array
    {
        return [
            'id'     => ['required', 'integer', 'exists:leads,id'],
            'status' => ['required', 'string', Rule::in(self::STATUS_OPTIONS)],
            'order'  => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Retorna mensagem JSON limpa e padronizada.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Erro de validação nos dados enviados ao Kanban.',
                'errors'  => $validator->errors(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
