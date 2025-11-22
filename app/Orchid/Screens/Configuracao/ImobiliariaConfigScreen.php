<?php

namespace App\Orchid\Screens\Configuracao;

use App\Models\ImobiliariaConfig;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ImobiliariaConfigScreen extends Screen
{
    public $name = 'Configurações da Imobiliária';
    public $description = 'Gerencie os dados da empresa para documentos e impressões.';

    public function query(): iterable
    {
        // Carrega a config. Se não existir, cria instância vazia.
        $config = ImobiliariaConfig::firstOrNew(['id' => 1]);

        return [
            'config' => $config,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Salvar Alterações')
                ->icon('bs.save')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                // MUDANÇA AQUI: Apontamos para 'config.logo_id' e usamos targetId()
                Picture::make('config.logo_id')
                    ->title('Logo da Imobiliária')
                    ->targetId() 
                    ->help('Essa imagem será usada no cabeçalho dos PDFs.'),

                Group::make([
                    Input::make('config.nome_fantasia')
                        ->title('Nome Fantasia')
                        ->placeholder('Ex: House Imóveis')
                        ->required(),

                    Input::make('config.razao_social')
                        ->title('Razão Social')
                        ->placeholder('Ex: House Negócios Imobiliários LTDA'),
                ]),

                Group::make([
                    Input::make('config.cnpj')
                        ->title('CNPJ')
                        ->mask('99.999.999/9999-99')
                        ->placeholder('00.000.000/0000-00'),

                    Input::make('config.creci')
                        ->title('CRECI')
                        ->placeholder('Ex: J-12345'),
                ]),

                Group::make([
                    Input::make('config.telefone')
                        ->title('Telefone / WhatsApp')
                        ->mask('(99) 99999-9999'),

                    Input::make('config.email')
                        ->title('E-mail de Contato')
                        ->type('email'),
                ]),

                TextArea::make('config.endereco_completo')
                    ->title('Endereço Completo')
                    ->rows(3)
                    ->placeholder('Rua, Número, Bairro, Cidade - UF, CEP')
                    ->help('Este endereço aparecerá no rodapé dos contratos.'),
            ])->title('Dados Cadastrais'),
        ];
    }

    public function save(Request $request)
    {
        $config = ImobiliariaConfig::firstOrNew(['id' => 1]);
        
        // O fill agora funciona direto pois logo_id está no fillable
        $config->fill($request->input('config'));
        $config->save();

        // O anexo é salvo automaticamente pelo Picture targetId() -> logo_id
        // Não precisamos mais do sync() manual complexo.

        Toast::success('Configurações atualizadas com sucesso!');
    }
}