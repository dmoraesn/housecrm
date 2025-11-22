<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta #{{ $proposta->id }}</title>

    <style>
        @page { margin: 20px; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px; 
            color: #333; 
            line-height: 1.3; 
            margin: 0; 
            padding: 0; 
        }

        /* Utilitários de Layout */
        table.layout-grid { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        td.col-left { width: 49%; vertical-align: top; padding-right: 5px; }
        td.col-spacer { width: 2%; }
        td.col-right { width: 49%; vertical-align: top; padding-left: 5px; }

        /* Estilo dos Cards */
        .card { 
            border: 1px solid #e0e0e0; 
            border-radius: 6px; 
            padding: 10px; /* Reduzi levemente */
            margin-bottom: 12px; /* Reduzi levemente */
            background-color: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .card-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 6px;
            margin-bottom: 8px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            color: #007bff;
            display: flex;
            align-items: center;
        }

        .card-header .material-icons {
            font-size: 14px;
            margin-right: 5px;
            vertical-align: middle;
        }

        /* Tabelas de Dados Internas */
        .data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .data-table th { text-align: left; padding: 5px 4px; color: #555; border-bottom: 1px solid #f0f0f0; background-color: #f9f9f9; }
        .data-table td { text-align: right; padding: 5px 4px; font-weight: bold; color: #222; border-bottom: 1px solid #f0f0f0; }
        
        /* --- AJUSTES DOS CARDS SUPERIORES --- */
        .highlight-box {
            text-align: center;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 8px 4px; /* Mais compacto */
            height: 100%; 
            min-height: 55px; /* Reduzido drásticamente de 90px */
            box-sizing: border-box;
            display: block;
        }
        .highlight-label { 
            font-size: 8px; /* Reduzido */
            color: #666; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            display: block; 
            margin-bottom: 3px; /* Aproximou do valor */
        }
        .highlight-value { 
            font-size: 14px; /* Reduzido de 16px para ficar mais elegante */
            font-weight: bold; 
            color: #007bff; 
            display: block; 
            margin-bottom: 2px; /* Aproximou do subtitulo */
        }
        .highlight-sub { 
            font-size: 8px; /* Reduzido */
            color: #999; 
            display: block; 
        }
        /* ------------------------------------- */

        /* Badges */
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 9px; display: inline-block; font-weight: bold; }
        .badge-blue { background: #e3f2fd; color: #0d47a1; border: 1px solid #bbdefb; }
        .badge-green { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }

        .footer { 
            position: fixed; bottom: 0; left: 20px; right: 20px; 
            text-align: center; font-size: 8px; color: #aaa; 
            border-top: 1px solid #eee; padding-top: 6px; 
        }
        
        /* Estilos para texto jurídico */
        .legal-text { font-size: 9px; text-align: justify; color: #444; line-height: 1.3; }
        .legal-list { margin: 4px 0 8px 15px; padding: 0; list-style-type: none; }
        .legal-list li { margin-bottom: 2px; }

        .text-success { color: #28a745 !important; }
        .text-danger { color: #dc3545 !important; }
    </style>
</head>
<body>

    <table width="100%" style="border-bottom: 2px solid #007bff; margin-bottom: 15px; padding-bottom: 8px;">
        <tr>
            <td width="30%">
                @if($logo && file_exists($logo))
                    <img src="{{ $logo }}" style="max-height: 50px; max-width: 150px;">
                @else
                    <h1 style="color:#007bff; margin:0; font-size: 20px;">HouseCRM</h1>
                @endif
            </td>
            <td width="70%" align="right">
                <div style="font-size:16px; font-weight:bold; color:#2c3e50;">
                    PROPOSTA DE COMPRA E VENDA
                </div>
                <div style="margin-top:5px;">
                    <span class="badge badge-blue">Nº {{ $proposta->id }}</span>
                    <span class="badge badge-green">{{ $proposta->status ?? 'Em Análise' }}</span>
                    <span style="font-size: 9px; color: #666; margin-left: 8px;">
                        {{ now()->format('d/m/Y') }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="layout-grid" style="margin-bottom: 15px;">
        <tr style="vertical-align: stretch;">
            <td style="padding-right: 8px; height: 100%;">
                <div class="highlight-box">
                    <span class="highlight-label">Valor Total do Imóvel</span>
                    <span class="highlight-value" style="color:#333;">R$ {{ number_format($proposta->valor_real, 2, ',', '.') }}</span>
                    <span class="highlight-sub">Avaliação: R$ {{ number_format($proposta->valor_avaliacao, 2, ',', '.') }}</span>
                </div>
            </td>
            <td style="padding-right: 8px; padding-left: 8px; height: 100%;">
                <div class="highlight-box" style="background-color: #e3f2fd; border-color: #bbdefb;">
                    <span class="highlight-label" style="color: #0d47a1;">Valor da Entrada</span>
                    <span class="highlight-value" style="color: #0d47a1;">R$ {{ number_format($proposta->valor_entrada, 2, ',', '.') }}</span>
                    <span class="highlight-sub">Recursos Próprios</span>
                </div>
            </td>
            <td style="padding-left: 8px; height: 100%;">
                <div class="highlight-box">
                    <span class="highlight-label">Saldo a Financiar</span>
                    <span class="highlight-value">R$ {{ number_format($proposta->valor_financiado, 2, ',', '.') }}</span>
                    <span class="highlight-sub">Financiamento Bancário</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="card">
        <div class="card-header">
            <span class="material-icons">attach_money</span>
            Composição do Pagamento da Entrada
        </div>
        
        <table class="layout-grid" style="margin-bottom: 0;">
            <tr>
                <td class="col-left">
                    <table class="data-table">
                        <tr style="background-color: #e8f5e9;">
                            <th style="color: #2e7d32;">SINAL / ATO (Imediato)</th>
                            <td style="font-size: 11px; color: #2e7d32;">R$ {{ number_format($proposta->sinal, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Valor Pago na Assinatura</th>
                            <td>R$ {{ number_format($proposta->valor_assinatura, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Saldo Parcelado (Mensais)</th>
                            <td>R$ {{ number_format($proposta->total_parcelamento, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Balões / Intermediárias</th>
                            <td>
                                @php
                                    $totalBaloes = 0;
                                    $baloes = $proposta->baloes_json ?? null;
                                    if ($baloes && !is_array($baloes)) $baloes = json_decode($baloes, true);
                                    if ($baloes) {
                                        foreach($baloes as $b) $totalBaloes += $b['valor'];
                                    }
                                @endphp
                                R$ {{ number_format($totalBaloes, 2, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <th>Bônus / Descontos Concedidos</th>
                            <td class="text-danger">- R$ {{ number_format($proposta->valor_bonus_descontos + $proposta->descontos, 2, ',', '.') }}</td>
                        </tr>
                        <tr style="border-top: 2px solid #ccc;">
                            <th>TOTAL DA ENTRADA</th>
                            <td style="font-size: 11px;">R$ {{ number_format($proposta->valor_entrada, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>

                <td class="col-spacer" style="border-left: 1px dashed #ccc;"></td>

                <td class="col-right">
                    <table class="data-table">
                        <tr>
                            <th colspan="2" style="background-color: #eee; text-align: center;">CONDIÇÃO DO PARCELAMENTO</th>
                        </tr>
                        <tr>
                            <th>Quantidade de Parcelas</th>
                            <td>{{ $proposta->num_parcelas }}x</td>
                        </tr>
                        <tr>
                            <th>Valor da Parcela Mensal</th>
                            <td style="font-size: 11px; color: #007bff;">R$ {{ number_format($proposta->valor_parcela, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Valor Restante (A confirmar)</th>
                            <td>R$ {{ number_format($proposta->valor_restante, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    @if(!empty($baloes))
    <div class="card">
        <div class="card-header">
            <span class="material-icons">event_note</span>
            Cronograma de Balões / Intermediárias
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 10%;">Nº</th>
                    <th style="width: 40%;">Vencimento Previsto</th>
                    <th style="width: 50%; text-align: right;">Valor (R$)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($baloes as $b)
                    <tr>
                        <td style="text-align: center;"><span class="badge badge-blue">{{ $b['numero'] }}</span></td>
                        <td style="text-align: left;">{{ \Carbon\Carbon::parse($b['data'])->format('d/m/Y') }}</td>
                        <td style="font-size: 10px;">R$ {{ number_format($b['valor'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <table class="layout-grid">
        <tr>
            <td class="col-left">
                <div class="card" style="min-height: 85px;">
                    <div class="card-header">
                        <span class="material-icons">person</span>
                        Comprador (Cliente)
                    </div>
                    @if($proposta->lead)
                        <div style="font-weight:bold; font-size:10px; margin-bottom:4px;">{{ $proposta->lead->nome }}</div>
                        <div style="font-size:9px; color:#555; line-height: 1.4;">
                            <b>CPF:</b> {{ $proposta->lead->cpf ?? '--' }}<br>
                            <b>Tel:</b> {{ $proposta->lead->telefone ?? '--' }}<br>
                            <b>Email:</b> {{ $proposta->lead->email ?? '--' }}
                        </div>
                    @else
                        <div style="color:#999; font-style:italic;">Cliente não vinculado</div>
                    @endif
                </div>
            </td>

            <td class="col-spacer"></td>

            <td class="col-right">
                <div class="card" style="min-height: 85px;">
                    <div class="card-header">
                        <span class="material-icons">apartment</span>
                        Vendedor (Construtora)
                    </div>
                    @if($proposta->construtora)
                        <div style="font-weight:bold; font-size:10px; margin-bottom:4px;">{{ $proposta->construtora->nome }}</div>
                        <div style="font-size:9px; color:#555; line-height: 1.4;">
                            <b>CNPJ:</b> {{ $proposta->construtora->cnpj }}<br>
                            <b>Tel:</b> {{ $proposta->construtora->telefone }}<br>
                            <b>Email:</b> {{ $proposta->construtora->email }}
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="card">
        <div class="card-header">
            <span class="material-icons">gavel</span>
            Termos e Ciência de Custos Adicionais
        </div>
        <div class="legal-text">
            <p style="margin-top: 0;">
                <strong>3.1.</strong> O COMPRADOR declara ter plena ciência e concorda que o valor pago a título de entrada, tratado nesta proposta, não abrange, não substitui e não se confunde com quaisquer taxas, tributos, encargos, custos administrativos ou despesas acessórias relacionadas à aquisição do Imóvel, incluindo, mas não se limitando a:
            </p>
            <ul class="legal-list">
                <li>a) ITBI – Imposto de Transmissão de Bens Imóveis;</li>
                <li>b) Custos de matrícula, escritura, registro e demais despesas cartoriais;</li>
                <li>c) Tarifas bancárias associadas ao financiamento ou análise de crédito;</li>
                <li>d) Custas referentes a assessorias, intermediários, despachantes ou serviços correlatos.</li>
            </ul>
            <p style="margin-bottom: 0;">
                <strong>3.2.</strong> Todas as despesas mencionadas no item anterior, bem como quaisquer outras decorrentes da efetiva aquisição do Imóvel, são de responsabilidade única e exclusiva do COMPRADOR, exceto se o CONSTRUTOR, por liberalidade própria, optar expressamente por arcar, total ou parcialmente, com tais custas. O COMPRADOR declara estar plenamente informado e de acordo com esta condição.
            </p>
        </div>
    </div>

    <div style="margin-top: 25px; margin-bottom: 20px;">
        <table width="100%">
            <tr>
                <td width="45%" align="center">
                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px; font-size: 9px;">
                        <strong>{{ $proposta->construtora->nome ?? 'Vendedor / Construtora' }}</strong><br>
                        <span style="color: #666;">Vendedor</span>
                    </div>
                </td>
                
                <td width="10%"></td>

                <td width="45%" align="center">
                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px; font-size: 9px;">
                        <strong>{{ $proposta->lead->nome ?? 'Comprador (Cliente)' }}</strong><br>
                        <span style="color: #666;">Comprador</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="height: 35px;"></td>
            </tr>
            <tr>
                <td width="45%" align="center">
                    <div style="border-top: 1px solid #ccc; width: 85%; margin: 0 auto; padding-top: 4px; font-size: 9px;">
                        <strong>Testemunha 1</strong><br>
                        <span style="color: #999;">CPF:</span>
                    </div>
                </td>

                <td width="10%"></td>

                <td width="45%" align="center">
                    <div style="border-top: 1px solid #ccc; width: 85%; margin: 0 auto; padding-top: 4px; font-size: 9px;">
                        <strong>Testemunha 2</strong><br>
                        <span style="color: #999;">CPF:</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Documento gerado automaticamente pelo sistema HouseCRM em {{ now()->format('d/m/Y H:i') }}.<br>
        Esta proposta está sujeita a aprovação de crédito e disponibilidade da unidade.
    </div>

</body>
</html>