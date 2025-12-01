<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo!</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F8F8F8; font-family: Figtree, sans-serif; height: 100vh; display: flex; justify-content: center; align-items: center;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #F8F8F8; padding: 0; margin: 0;">
        <tr>
            <td align="center" style="padding: 50px 0;">
                <table role="presentation" width="450" cellpadding="0" cellspacing="0" style="background: white; border-radius: 15px; padding: 40px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Cabeçalho -->
                    <tr>
                        <td style="text-align: center; margin-bottom: 30px;">
                            <img src="{{ asset('storage/images/logo_tipo_verde.png') }}" alt="Logo" style="width: 175px;margin-bottom: 40px;">
                            <h1 style="color: #6DB631; font-size: 24px; margin: 0;">Olá, {{ $name }}!</h1>
                        </td>
                    </tr>
                    <!-- Conteúdo -->
                    <tr>
                        <td style="font-size: 16px; color: #333; text-align: center;">
                            <p>A sua conta foi criada com a senha padrão:</p>
                            <p style="font-size: 30px; font-weight: bold; color: #6DB631;">taiksu-123456</p>
                            <p>Por favor, clique no botão abaixo para redefinir sua senha:</p>
                        </td>
                    </tr>
                    <!-- Botão -->
                    <tr>
                        <td style="text-align: center; margin: 30px 0;">
                            <a href="{{ $resetPasswordUrl }}" style="width: 100%; text-decoration: none; padding: 12px 0; background: #6DB631; color: white; font-weight: bold; border-radius: 10px; font-size: 16px; display: block; text-align: center;">Alterar senha</a>
                        </td>
                    </tr>
                    <!-- Rodapé -->
                    <tr>
                        <td style="text-align: center; font-size: 14px; color: #999;">
                            <p style="margin: 5px;margin-top: 15px;">Se você não solicitou este e-mail, ignore esta mensagem.</p>
                            <p style="margin: 5px;margin-top: 15px;">Atenciosamente,</p>
                            <p style="margin: 5px;margin-top: 15px;"><strong>Equipe Taiksu Franchising</strong></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
