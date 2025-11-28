<?php

namespace App\Orchid\Screens\Configuracao;

use App\Models\GlobalSetting;
use App\Models\ImobiliariaConfig;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ImobiliariaConfigScreen extends Screen
{
    /**
     * @var ImobiliariaConfig|null
     */
    public $config;

    /**
     * @var array
     */
    public $apiKeys = [];

    public $name = 'Configurações da Imobiliária';
    public $description = 'Gerencie todas as configurações do sistema em um único lugar.';

    public function query(): iterable
    {
        $config = ImobiliariaConfig::firstOrNew(['id' => 1]);

        // Carrega valores do GlobalSetting
        $apiKeys = [
            'openai_api_key'     => GlobalSetting::getValue('openai_api_key'),
            'gemini_api_key'     => GlobalSetting::getValue('gemini_api_key'),
            'anthropic_api_key'  => GlobalSetting::getValue('anthropic_api_key'),
            'grok_api_key'       => GlobalSetting::getValue('grok_api_key'),
            'openai_model'       => GlobalSetting::getValue('openai_model', 'gpt-4o-mini'),
            'ai_temperature'     => GlobalSetting::getValue('ai_temperature', '0.7'),
        ];

        $this->config = $config;
        $this->apiKeys = $apiKeys;

        return [
            'config'  => $config,
            'apiKeys' => $apiKeys,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Salvar Todas as Configurações')
                ->icon('bs.save')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::tabs([
                'Geral' => [
                    Layout::rows([
                        Picture::make('config.logo_id')
                            ->title('Logo da Imobiliária')
                            ->targetId()
                            ->help('Imagem usada em PDFs, propostas e contratos.'),

                        Input::make('config.nome_fantasia')
                            ->title('Nome Fantasia')
                            ->placeholder('Ex: House Imóveis')
                            ->required(),

                        Input::make('config.razao_social')
                            ->title('Razão Social')
                            ->placeholder('Ex: House Negócios Imobiliários LTDA'),

                        Input::make('config.cnpj')
                            ->title('CNPJ')
                            ->mask('99.999.999/9999-99')
                            ->placeholder('00.000.000/0000-00'),

                        Input::make('config.creci')
                            ->title('CRECI')
                            ->placeholder('Ex: J-12345'),
                    ]),
                ],

                'Contato' => [
                    Layout::rows([
                        Input::make('config.telefone')
                            ->title('Telefone / WhatsApp')
                            ->mask('(99) 9 9999-9999')
                            ->required(),

                        Input::make('config.email')
                            ->title('E-mail de Contato')
                            ->type('email')
                            ->required(),
                    ]),
                ],

                'Endereço' => [
                    Layout::rows([
                        Input::make('config.cep')
                            ->title('CEP')
                            ->mask('99999-999')
                            ->help('Digite o CEP para buscar automaticamente o endereço.')
                            ->horizontal(),

                        Input::make('config.rua')
                            ->title('Rua / Avenida')
                            ->required(),

                        Input::make('config.numero')
                            ->title('Número')
                            ->required(),

                        Input::make('config.complemento')
                            ->title('Complemento')
                            ->placeholder('Apt, Bloco, etc.'),

                        Input::make('config.bairro')
                            ->title('Bairro')
                            ->required(),

                        Input::make('config.cidade')
                            ->title('Cidade')
                            ->required(),

                        Select::make('config.uf')
                            ->title('UF')
                            ->options([
                                'SP' => 'São Paulo',
                                'RJ' => 'Rio de Janeiro',
                                'MG' => 'Minas Gerais',
                                'PR' => 'Paraná',
                                'RS' => 'Rio Grande do Sul',
                            ])
                            ->empty('Selecione o estado')
                            ->required(),
                    ]),
                ],

                'Chaves de API' => [
                    Layout::rows([
                        Input::make('apiKeys.openai_api_key')
                            ->title('OpenAI API Key')
                            ->type('password')
                            ->placeholder($this->apiKeys['openai_api_key'] ? '••••••••••••••••' : 'sk-...')
                            ->help('Chave da OpenAI (ChatGPT, GPT-4, etc.)'),

                        Input::make('apiKeys.gemini_api_key')
                            ->title('Google Gemini API Key')
                            ->type('password')
                            ->placeholder($this->apiKeys['gemini_api_key'] ? '••••••••••••••••' : 'Insira a chave do Gemini'),

                        Input::make('apiKeys.anthropic_api_key')
                            ->title('Anthropic API Key (Claude)')
                            ->type('password')
                            ->placeholder($this->apiKeys['anthropic_api_key'] ? '••••••••••••••••' : 'Insira a chave do Claude'),

                        Input::make('apiKeys.grok_api_key')
                            ->title('Grok API Key (xAI)')
                            ->type('password')
                            ->placeholder($this->apiKeys['grok_api_key'] ? '••••••••••••••••' : 'Insira a chave do Grok'),
                    ]),
                ],

                'IA Avançado' => [
                    Layout::rows([
                        Input::make('apiKeys.openai_model')
                            ->title('Modelo Padrão da OpenAI')
                            ->placeholder('Ex: gpt-4o-mini, gpt-4o, gpt-4-turbo')
                            ->value($this->apiKeys['openai_model']),

                        Input::make('apiKeys.ai_temperature')
                            ->title('Temperatura (Criatividade)')
                            ->type('number')
                            ->min(0)
                            ->max(2)
                            ->step(0.1)
                            ->value($this->apiKeys['ai_temperature'])
                            ->help('0.0 = mais preciso | 1.0 = mais criativo'),
                    ]),
                ],
            ]),
        ];
    }

    public function save(Request $request)
    {
        $config = ImobiliariaConfig::firstOrNew(['id' => 1]);
        $configData = $request->get('config', []);

        // Validação
        $request->validate([
            'config.nome_fantasia' => 'required|string|max:255',
            'config.email'         => 'required|email',
            'config.telefone'      => 'required',
            'config.cep'           => 'required|regex:/^\d{5}-\d{3}$/',
            'config.uf'            => 'required|size:2',
        ]);

        $config->fill($configData);
        $config->save();

        // Salva chaves de API
        $apiKeys = $request->get('apiKeys', []);
        foreach ($apiKeys as $key => $value) {
            if (!empty($value) && !str_starts_with($value, '••••')) {
                GlobalSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'type' => 'password']
                );
            }
        }

        // Salva configurações de IA
        $aiSettings = [
            'openai_model'   => $apiKeys['openai_model'] ?? 'gpt-4o-mini',
            'ai_temperature' => $apiKeys['ai_temperature'] ?? '0.7',
        ];

        foreach ($aiSettings as $key => $value) {
            GlobalSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'string']
            );
        }

        Toast::success('Todas as configurações foram salvas com sucesso!');

        return back();
    }
}
