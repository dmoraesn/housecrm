<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Construtora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

/**
 * Screen for automated creation via CNPJ lookup.
 */
class ConstrutoraCreateAutoScreen extends Screen
{
    // CORREÇÃO: Inicializa a propriedade para evitar o erro "must not be accessed before initialization".
    public Construtora $construtora;

    public function __construct()
    {
        $this->construtora = new Construtora();
    }

    public function query(Construtora $construtora): array
    {
        // Agora, você pode simplificar a lógica, pois a propriedade já existe.
        // O $construtora que chega é o passado pela rota. Se for passado um ID, ele carrega.
        // Como o objetivo é CRIAÇÃO, vamos garantir que seja sempre uma nova instância,
        // mas respeitando a injeção do Orchid.
        
        // Se a rota não passar um modelo (criação), $construtora será uma nova instância.
        // O código original estava confuso, pois você injetava um modelo e depois criava um novo
        // se ele existisse (o oposto do esperado).

        // Mantendo a intenção de CRIAÇÃO e carregamento temporário:
        if (session()->has('construtora_temp_data')) {
            $this->construtora->fill(session('construtora_temp_data'));
        }

        return [
            'construtora' => $this->construtora,
        ];
    }

    public function name(): ?string
    {
        return 'Cadastrar Construtora (Automático)';
    }

    public function description(): ?string
    {
        return 'Digite o CNPJ e clique em "Buscar na Receita" para preencher automaticamente.';
    }

    public function commandBar(): array
    {
        return [
            Button::make('Salvar')
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                Group::make([
                    Input::make('construtora.cnpj')
                        ->title('CNPJ')
                        ->mask('99.999.999/9999-99')
                        ->placeholder('00.000.000/0000-00')
                        ->required(),
                        
                    Button::make('Buscar na Receita')
                        ->icon('bs.search')
                        ->method('buscarCnpj')
                        ->novalidate()
                        ->turbo(false),
                ]),
                // Remaining editable fields
                Input::make('construtora.nome')->title('Razão Social')->required(),
                Input::make('construtora.nome_fantasia')->title('Nome Fantasia'),
                Input::make('construtora.telefone')->title('Telefone')->mask('(99) 99999-9999'),
                Input::make('construtora.email')->title('E-mail')->type('email'),
                Input::make('construtora.cep')->title('CEP')->mask('99999-999'),

                Group::make([
                    Input::make('construtora.logradouro')->title('Logradouro'),
                    Input::make('construtora.numero')->title('Número'),
                ]),

                Input::make('construtora.complemento')->title('Complemento'),

                Group::make([
                    Input::make('construtora.bairro')->title('Bairro'),
                    Input::make('construtora.cidade')->title('Cidade')->required(),
                    Input::make('construtora.uf')->title('UF')->maxlength(2)->required(),
                ]),

                Input::make('construtora.socios')
                    ->title('Sócios')
                    ->placeholder('Separados por vírgula'),

                Input::make('construtora.situacao')
                    ->title('Situação Cadastral')
                    ->readonly()
                    ->help('Atualizado automaticamente pela Receita Federal'),
            ])->title('Dados da Construtora'),
        ];
    }

    public function buscarCnpj(Request $request)
    {
        $cnpj = preg_replace('/\D/', '', $request->input('construtora.cnpj', ''));

        if (strlen($cnpj) !== 14 || !ctype_digit($cnpj)) {
            Toast::error('CNPJ inválido. Digite exatamente 14 números.');
            return back();
        }

        if (Construtora::where('cnpj', $cnpj)->exists()) {
            Toast::error('Este CNPJ já está cadastrado. Edite o registro existente.');
            return back();
        }

        $resposta = Http::timeout(15)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

        if ($resposta->failed()) {
            Toast::error('CNPJ não encontrado ou serviço indisponível.');
            return back();
        }

        $dados = $resposta->json();

        session([
            'construtora_temp_data' => [
                'cnpj'          => $cnpj,
                'nome'          => $dados['razao_social'] ?? '',
                'nome_fantasia' => $dados['nome_fantasia'] ?? '',
                'telefone'      => $dados['ddd_telefone_1'] ?? $dados['ddd_telefone_2'] ?? '',
                'email'         => $dados['email'] ?? '',
                'cep'           => preg_replace('/\D/', '', $dados['cep'] ?? ''),
                'logradouro'    => $dados['logradouro'] ?? '',
                'numero'        => $dados['numero'] ?? '',
                'complemento'   => $dados['complemento'] ?? '',
                'bairro'        => $dados['bairro'] ?? '',
                'cidade'        => $dados['municipio'] ?? '',
                'uf'            => $dados['uf'] ?? '',
                'situacao'      => $dados['descricao_situacao_cadastral'] ?? 'DESCONHECIDA',
                'socios'        => collect($dados['qsa'] ?? [])->pluck('nome_socio')->implode(', '),
            ]
        ]);

        Toast::success('Dados carregados com sucesso! Revise e salve.');
        return back();
    }

    public function save(Construtora $construtora, Request $request)
    {
        $data = $request->input('construtora', []);
        
        $cnpjValue = $request->input('construtora.cnpj');
        $data['cnpj'] = preg_replace('/\D/', '', $cnpjValue ?? '');
        $data['cep'] = preg_replace('/\D/', '', $data['cep'] ?? '');

        $rules = [
            'cnpj'   => ['required', 'digits:14', Rule::unique('construtoras', 'cnpj')],
            'nome'   => 'required',
            'cidade' => 'required',
            'uf'     => 'required|size:2',
        ];

        validator($data, $rules)->validate();

        $this->construtora->fill($data)->save(); // Usa $this->construtora para garantir que é a instância atual da tela
        
        session()->forget('construtora_temp_data');

        Toast::success('Construtora salva com sucesso!');
        // Redireciona para a tela de edição após a criação
        return redirect()->route('platform.construtoras.edit', $this->construtora);
    }
}