<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Cadastro de Conta a Pagar #{{ $conta->id }}</title>
</head>

<body style="margin: 0; padding: 0; background-color: #F8F8F8; font-family: 'Figtree', sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #F8F8F8; padding: 0; margin: 0;">
        <tr>
            <td align="center" style="padding: 50px 0;">
                <table role="presentation" width="500" cellpadding="0" cellspacing="0" style="background: white; border-radius: 15px; padding: 40px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Cabeçalho -->
                    <tr>
                        <td style="text-align: center;">
                            <img src="{{ config('app.url') }}/storage/images/logo_tipo_verde.png" alt="Logo Taiksu" style="width: 175px; margin-bottom: 30px;">
                        </td>
                    </tr>

                    <!-- Conteúdo -->
                    <tr>
                        <td style="color: #333;">
                            <h2 style="font-size: 22px; font-weight: bold; color: #6DB631; margin-bottom: 20px; text-align: center;">
                                Comprovante de Cadastro
                            </h2>
                            <p style="font-size: 16px; line-height: 1.5; margin-bottom: 25px; text-align: center;">
                                Uma nova conta a pagar foi registrada no sistema.
                            </p>

                            <div style="background-color: #F4F8F4; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding-bottom: 10px; font-weight: bold; color: #164110;">Detalhes da Conta:</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;">ID: <strong>#{{ $conta->id }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0 text-transform: capitalize;">Categoria: <strong>{{ $conta->nome }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;">Valor: <strong style="color: #6DB631; font-size: 18px;">R$ {{ number_format($conta->valor, 2, ',', '.') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;">Vencimento: <strong>{{ \Carbon\Carbon::parse($conta->vencimento)->format('d/m/Y') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;">Descrição: <strong>{{ $conta->descricao ?: 'Nenhuma' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 15px 0 5px 0; border-top: 1px solid #ddd; font-size: 13px; color: #666;">
                                            Cadastrado por: <strong>{{ $usuario->name }}</strong><br>
                                            Data/Hora: <strong>{{ $dataCadastro }}</strong><br>
                                            Unidade: <strong>{{ $nomeUnidade }}</strong>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <p style="font-size: 14px; color: #666; text-align: center; line-height: 1.5;">
                                Você pode visualizar e gerenciar esta conta através do Painel Administrativo.
                            </p>
                        </td>
                    </tr>

                    <!-- Rodapé -->
                    <tr>
                        <td style="text-align: center; font-size: 12px; color: #999; padding-top: 30px; border-top: 1px solid #eee; margin-top: 20px;">
                            <p style="margin: 5px 0;">Este é um e-mail automático, por favor não responda.</p>
                            <p style="margin: 5px 0;"><strong>Equipe Taiksu Franchising</strong></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>