<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Pedido #{{ $pedido->id }}</title>
</head>

<body style="margin: 0; padding: 0; background-color: #F8F8F8; font-family: 'Figtree', sans-serif; height: 100vh; display: flex; justify-content: center; align-items: center;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #F8F8F8; padding: 0; margin: 0;">
        <tr>
            <td align="center" style="padding: 50px 0;">
                <table role="presentation" width="450" cellpadding="0" cellspacing="0" style="background: white; border-radius: 15px; padding: 40px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Cabeçalho -->
                    <tr>
                        <td style="text-align: center; margin-bottom: 30px;">
                            <img src="{{ asset('storage/images/logo_tipo_verde.png') }}" alt="Logo" style="width: 175px; margin-bottom: 40px;">
                            <h1 style="color: #6DB631; font-size: 24px; margin: 0;">Olá!</h1>
                        </td>
                    </tr>

                    <!-- Conteúdo -->
                    <tr>
                        <td style="font-size: 16px; color: #333; text-align: center;">
                            <p style="margin: 10px 0;">Um novo pedido foi realizado no sistema.</p>
                            <p style="font-size: 20px; font-weight: bold; color: #6DB631; margin: 10px 0;">Pedido realizado na plataforma Taiksu</p>
                            <p style="margin: 10px 0;">Confira todos os detalhes do pedido no documento em PDF anexado.</p>
                            <p style="margin: 10px 0;">Número do Pedido: <strong>#{{ $pedido->id }}</strong></p>

                            <p style="margin: 10px 0;">Data do Pedido: <strong>{{ $dataPedido }}</strong></p>
                            <p style="margin: 10px 0;">Unidade: <strong>{{ $nomeUnidade }}</strong></p>
                        </td>
                    </tr>

                    <!-- Rodapé -->
                    <tr>
                        <td style="text-align: center; font-size: 14px; color: #999;">
                            <p style="margin: 5px; margin-top: 15px;">Se você não reconhece este e-mail, ignore esta mensagem.</p>
                            <p style="margin: 5px; margin-top: 15px;">Atenciosamente,</p>
                            <p style="margin: 5px; margin-top: 15px;"><strong>Equipe Taiksu Franchising</strong></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>