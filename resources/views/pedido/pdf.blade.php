<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            margin: 20px;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            background-color: #e4f6de;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        /* Estilizando a tabela */
        table {
            width: 100%;
            margin-top: 20px;

            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
        }

        th {
            background-color: #164110;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .TrRedonEsquerda {
            border-radius: 20px 0px 0px 0px;
        }

        .TrRedonDireita {
            border-radius: 0px 20px 0px 0px;
        }

        tr:nth-child(even) {
            background-color: #f4f5f3;
        }

        tr:hover {
            background-color: #dededea9;
            cursor: pointer;
        }

        h2 {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 15px;
            text-align: left;
        }

        p {
            font-size: 14px;
            color: rgb(50, 56, 68);
            line-height: 1.6;
            margin: 5px 0;
        }

        p strong {
            font-weight: bold;
        }

        .info-header {
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
        }

        .info-detail {
            margin-top: 5px;
        }

        /* Estilo para a imagem no canto direito */
        .logo {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 100px;
            /* Tamanho ajustado da imagem */
        }
    </style>
    <title>Novo Pedido #{{ $pedido->id }}</title>
</head>

<body>
    <img src="{{ asset('storage/images/logo_tipo_verde.png')}}" alt="Taiksu" class="logo">

    <h2>Novo Pedido #{{ $pedido->id }}</h2>

    <div class="info-header">
        <p><strong>Unidade:</strong> {{ $nomeUnidade }}</p>
        <p><strong>Data:</strong> {{ $dataAtual }}</p>
        <p><strong>Fornecedor:</strong> {{ $nomeFornecedor }}</p>
    </div>


    <h3>Itens do Pedido:</h3>
    <table>
        <thead>
            <tr>
                <th class="text-left TrRedonEsquerda">Item</th>
                <th class="text-center">QTD.</th>
                <th class="text-left">V. UN/KG</th>
                <th class="text-center TrRedonDireita">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtosParaPdf as $item)
            <tr>
                <!-- Nome do Item -->
                <td class="text-left">{{ $item['nome'] }}</td>

                <!-- Quantidade -->
                <td class="text-center">
                    {{ $item['quantidade'] }} {{ $item['unidade_de_medida'] }}
                </td>

                <!-- Valor Unitário -->
                <td class="text-left">R$ {{ $item['valor_unitario'] }}</td>

                <!-- Valor Total -->
                <td class="text-center">R$ {{ $item['valor_total_item'] }}</td>
            </tr>
            @endforeach

            <!-- Linha do Total -->
            <tr class="total-row">
                <td class="text-left" colspan="3">Total do Pedido</td>
                <td class="text-center">R$ {{ number_format($pedido->valor_total_carrinho, 2, ',', '.') }}</td>
                <td class="text-right"></td>
            </tr>
        </tbody>
    </table>
    <div class="info-header">
        <p><strong>Pedido realizado</strong> {{ $nomeUsuario }} em {{ $dataAtual }}.</p>

    </div>

</body>

</html>