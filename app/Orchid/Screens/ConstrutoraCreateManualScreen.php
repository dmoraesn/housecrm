<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Construtora;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

/**
 * Screen for manual creation/basic entry.
 */
class ConstrutoraCreateManualScreen extends Screen
{
    public Construtora $construtora;

    public function query(Construtora $construtora): array
    {
        // Always starts with a new model instance
        $this->construtora = $construtora->exists ? new Construtora() : $construtora;

        return [
            'construtora' => $this->construtora,
        ];
    }

    public function name(): ?string
    {
        return 'Cadastrar Construtora (Manual)';
    }

    public function description(): ?string
    {
        return 'Preencha os dados manualmente.';
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
                Input::make('construtora.cnpj')
                    ->title('CNPJ')
                    ->mask('99.999.999/9999-99')
                    ->placeholder('00.000.000/0000-00')
                    ->required(),
                    
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
                    ->help('Opcional'),
            ])->title('Dados da Construtora'),
        ];
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

        $construtora->fill($data)->save();

        Toast::success('Construtora salva com sucesso!');
        // Redireciona para a tela de edição após a criação
        return redirect()->route('platform.construtoras.edit', $construtora);
    }
}