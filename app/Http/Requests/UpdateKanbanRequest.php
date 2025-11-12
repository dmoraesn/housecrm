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
        'novo', 'qualificacao', 'visita', 'negociacao', 'fechamento', 'perdido',
    ];

    /**
     * Determine if the user is authorized to make this request.
     * Deve garantir que o usuário está logado e tem a permissão 'platform.leads'.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Certifique-se de que o usuário tem permissão para atualizar leads
        // Se a permissão no seu sistema for 'platform.leads', utilize-a aqui:
        return $this->user() && $this->user()->hasAccess('platform.leads');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id'             => 'required|integer|exists:leads,id',
            'status'         => ['required', 'string', Rule::in(self::STATUS_OPTIONS)],
            // column_order é um array de objetos {id: integer, order: integer}
            'column_order'   => 'required|array',
            'column_order.*.id'      => 'required|integer|exists:leads,id',
            'column_order.*.order'   => 'required|integer|min:0',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * Sobrescreve para garantir que retorne JSON em caso de falha.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Erro de validação dos dados do Kanban.',
                'errors'  => $validator->errors(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}