<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta #{{ $proposta->id }}</title>
    <style>
        @page { margin: 20px; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 10px; 
            color: #333; 
            line-height: 1.2; 
            margin: 0; 
            padding: 0; 
            width: 100%;
        }
        
        /* LAYOUT EM TABELA (Essencial para DomPDF não quebrar páginas) */
        table.layout-grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
        td.col-left { width: 49%; vertical-align: top; padding-right: 5px; }
        td.col-spacer { width: 2%; }
        td.col-right { width: 49%; vertical-align: top; padding-left: 5px; }
        
        /* CARDS */
        .card { 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            padding: 10px; 
            margin-bottom: 10px;
            background-color: #fff;
            min-height: 80px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .card-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 8px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            color: #007bff;
            display: block;
        }
        
        /* ÍCONES (Via Imagem Base64 para compatibilidade total) */
        .icon-img {
            width: 12px;
            height: 12px;
            vertical-align: middle;
            margin-right: 5px;
            display: inline-block;
            opacity: 0.8;
        }
        
        /* TABELAS DE DADOS */
        .data-table { width: 100%; border-collapse: collapse; font-size: 9px; table-layout: fixed; }
        .data-table th { text-align: left; color: #666; padding: 4px 0; font-weight: normal; border-bottom: 1px solid #eee; width: 50%; }
        .data-table td { text-align: right; padding: 4px 0; font-weight: bold; color: #222; border-bottom: 1px solid #eee; width: 50%; }
        .data-table tr:last-child td, .data-table tr:last-child th { border-bottom: none; }

        /* BADGES */
        .badge { 
            padding: 3px 6px; 
            border-radius: 3px; 
            font-size: 9px; 
            font-weight: bold; 
            display: inline-block;
        }
        .badge-blue { background-color: #e3f2fd; color: #0d47a1; border: 1px solid #bbdefb; }
        .badge-green { background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; }
        .badge-yellow { background-color: #fffde7; color: #f57f17; border: 1px solid #fff9c4; }
        
        .h-title { font-size: 14px; font-weight: bold; margin: 0; color: #2c3e50; }
        
        /* RODAPÉ FIXO */
        .footer { 
            position: fixed; 
            bottom: 0; 
            left: 20px; 
            right: 20px; 
            text-align: center; 
            font-size: 8px; 
            color: #aaa; 
            border-top: 1px solid #eee; 
            padding-top: 5px; 
            background-color: #fff;
        }
        
        /* RESPONSIVIDADE */
        @media print { body { font-size: 9px; } .card { box-shadow: none; } .footer { position: static; } }
        @media (max-width: 600px) { 
            body { font-size: 9px; padding: 10px; } 
            .col-left, .col-right { width: 100%; display: block; } 
            .col-spacer { display: none; } 
        }
    </style>
</head>
<body>

    @php
        // Ícone Prédio (Cinza/Azulado)
        $iconBuilding = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAAZklEQVQ4y6XTQQqAQAxE0V+xV/CiF/Cid/CiXsWLF3HhSkFkmBTEZD3IpzQk0CXwJ6iIgR171BgxY8OOCXvOWDDiwIYFf9wy441L/wF+QokAGa2pEaEno4xW1Iiwk1FGM2pE+Mko4wJm1Q0i8176gQAAAABJRU5ErkJggg==";
        // Ícone Usuário
        $iconUser = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAAaUlEQVQ4y6XTwQnAIAxF0V+lq3gVr+JdvIpX8SpexY0dCn0IqEkh5CN5CReikbmJkYFII8pA5hFzIItIBtJExIGkIgRIFiEHsoksA9lFjgF/4K/S7wAH9Nf7DfQW+g/0F/oP9Bf6D/wF1e0LIy+M5KkAAAAASUVORK5CYII=";
        // Ícone Casa
        $iconHome = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAAaElEQVQ4y6XTwQmAMBBE0V+xV7CKV7GKd/EqXsWLG0sRjZCEPeS8ZJaF2f1swwz8CjViYMUaNUbM2LBiw54zFow4sGHBHzfMeOPSf4AfUSJARitqROjJKKMVNSLsZJTRjBoRfjLK+IDZ9AFj3Q0iT27/5gAAAABJRU5ErkJggg==";
        // Ícone Calendário
        $iconCal = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAAbUlEQVQ4y6XTwQmAMBCF4V+xV/CiV/CiXvSiXsWLF3HhSkE0QhL2kPPTTBaG+b9twgz8CjViYMcaNUbM2LBjw54zFow4sGHBH7fMeOPSf4AfUSJARitqROjJKKMVNSLsZJTRjBoRfjLK+IDZ9AF0/Q0ib1+96gAAAABJRU5ErkJggg==";
    @endphp

    <table width="100%" style="border-bottom: 2px solid #007bff; padding-bottom: 15px; margin-bottom: 20px;">
        <tr>
            <td width="30%" valign="middle">
                @if($logo && file_exists($logo))
                    <img src="{{ $logo }}" style="max-height: 50px; max-width: 150px;">
                @else
                    <h2 style="color:#007bff; margin:0;">HouseCRM</h2>
                @endif
            </td>
            <td width="70%" align="right" valign="middle">
                <div class="h-title">Proposta de Pagamento de Entrada</div>
                <div style="margin-top: 5px;">
                    <span class="badge badge-blue">Nº {{ $proposta->id ?? '000' }}</span>
                    <span class="badge badge-blue">Data: {{ $proposta->data_assinatura?->format('d/m/Y') ?? \Carbon\Carbon::now('America/Sao_Paulo')->format('d/m/Y') }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="layout-grid">
        <tr>
            <td class="col-left">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ $iconBuilding }}" class="icon-img"> Dados da Construtora
                    </div>
                    @if($proposta->construtora)
                        <div style="font-size:11px; font-weight:bold; margin-bottom:3px;">{{ $proposta->construtora->nome }} {{ $proposta->construtora->nome_fantasia ? '(' . $proposta->construtora->nome_fantasia . ')' : '' }}</div>
                        <div style="color:#555; font-size: 9px;">
                            CNPJ: {{ $proposta->construtora->cnpj ?? '--' }}<br>
                            {{ Str::limit($proposta->construtora->endereco ?? '', 50) }}<br>
                            Tel: {{ $proposta->construtora->telefone ?? '' }} | E-mail: {{ $proposta->construtora->email ?? '' }}
                        </div>
                    @else
                        <div style="color:#999; font-style:italic;">Não informada (ID: {{ $proposta->construtora_id ?? 'N/A' }})</div>
                    @endif
                </div>
            </td>
            
            <td class="col-spacer"></td>
            
            <td class="col-right">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ $iconUser }}" class="icon-img"> Dados do Cliente
                    </div>
                    @if($proposta->lead)
                        <div style="font-size:11px; font-weight:bold; margin-bottom:3px;">{{ $proposta->lead->nome }}</div>
                        <div style="color:#555; font-size: 9px;">
                            CPF: {{ $proposta->lead->cpf ?? '--' }}<br>
                            Tel: {{ $proposta->lead->telefone ?? '' }}<br>
                            E-mail: {{ $proposta->lead->email ?? '' }}<br>
                            {{ Str::limit($proposta->lead->endereco ?? '', 40) }}
                        </div>
                    @else
                        <div style="color:#999; font-style:italic;">Não informado (ID: {{ $proposta->lead_id ?? 'N/A' }})</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="layout-grid">
        <tr>
            <td class="col-left">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ $iconHome }}" class="icon-img"> Resumo do Imóvel
                    </div>
                    <table class="data-table">
                        <tr>
                            <th>Valor do Imóvel</th>
                            <td>R$ {{ number_format($proposta->valor_real ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Avaliação</th>
                            <td style="color:#777;">R$ {{ number_format($proposta->valor_avaliacao ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Financiamento</th>
                            <td>R$ {{ number_format($proposta->valor_financiado ?? 0, 2, ',', '.') }} ({{ number_format((($proposta->valor_real ?? 0) > 0 ? (($proposta->valor_financiado ?? 0) / $proposta->valor_real) * 100 : 0), 0) }}%)</td>
                        </tr>
                        <tr>
                            <th>Bônus/FGTS</th>
                            <td style="color: #d32f2f;">- R$ {{ number_format($proposta->valor_bonus_descontos ?? $proposta->descontos ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr style="border-top: 2px solid #eee;">
                            <th style="padding-top:8px; color:#007bff; font-weight:bold;">Entrada Mínima</th>
                            <td style="padding-top:8px;">
                                <span class="badge badge-blue">
                                    R$ {{ number_format(max(($proposta->valor_real ?? 0) - ($proposta->valor_financiado ?? 0) - ($proposta->valor_bonus_descontos ?? $proposta->descontos ?? 0), 0), 2, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>

            <td class="col-spacer"></td>

            <td class="col-right">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ $iconCal }}" class="icon-img"> Fluxo de Pagamento
                    </div>
                    
                    <table class="data-table">
                        @php $hasItems = false; @endphp
                        
                        @if(($proposta->valor_assinatura ?? $proposta->sinal ?? 0) > 0)
                            @php $hasItems = true; @endphp
                            <tr>
                                <th>Sinal (Ato)</th>
                                <td>{{ $proposta->data_assinatura?->format('d/m') ?? '-' }}</td>
                                <td>R$ {{ number_format($proposta->valor_assinatura ?? $proposta->sinal ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        @endif

                        @php $baloes = $proposta->baloes_json ?? []; @endphp
                        @if(is_array($baloes) && count($baloes) > 0)
                            @php $hasItems = true; @endphp
                            @foreach($baloes as $balao)
                            <tr>
                                <th>Balão</th>
                                <td>{{ isset($balao['data']) ? \Carbon\Carbon::parse($balao['data'])->format('d/m/y') : '-' }}</td>
                                <td>R$ {{ number_format($balao['valor'] ?? 0, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        @endif

                        @if(($proposta->num_parcelas ?? 0) > 0)
                            @php $hasItems = true; @endphp
                            <tr>
                                <th>{{ $proposta->num_parcelas }}x Mensais</th>
                                <td>Imediato</td>
                                <td>R$ {{ number_format($proposta->valor_parcela ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        @endif
                        
                        @if(!$hasItems)
                            <tr>
                                <td colspan="3" style="text-align:center; color:#999; font-style:italic; padding:10px;">Nenhum item configurado</td>
                            </tr>
                        @endif
                    </table>
                    
                    <div style="margin-top: 10px; padding-top: 5px; border-top: 1px dashed #ccc;">
                        <table width="100%">
                            <tr>
                                <td align="left"><span style="font-size:9px; color:#666; font-weight:bold;">Total Configurado</span></td>
                                <td align="right"><span class="badge badge-green">R$ {{ number_format($proposta->valor_entrada ?? 0, 2, ',', '.') }}</span></td>
                            </tr>
                            @if(($proposta->valor_restante ?? 0) > 0)
                            <tr>
                                <td align="left"><span style="font-size:9px; color:#f57f17; font-weight:bold;">Diferença</span></td>
                                <td align="right"><span class="badge badge-yellow">R$ {{ number_format($proposta->valor_restante ?? 0, 2, ',', '.') }}</span></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    @if($proposta->description ?? false)
    <div class="card" style="background-color: #f8f9fa; margin-top: 5px; min-height: auto;">
        <div style="font-weight:bold; font-size:9px; color:#666; margin-bottom:3px;">Observações</div>
        <div style="font-style: italic; color: #555; font-size: 9px;">
            {{ $proposta->description }}
        </div>
    </div>
    @endif

    <div class="footer">
        Esta proposta é válida por 30 dias a partir da data de emissão. Para dúvidas, contate a construtora.<br>
        Gerado pelo Sistema HouseCRM em {{ \Carbon\Carbon::now('America/Sao_Paulo')->format('d/m/Y H:i') }}.
    </div>

</body>
</html>