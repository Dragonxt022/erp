@component('mail::message')
# Olá, {{ $name }}

Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.

@component('mail::button', ['url' => $resetUrl])
Redefinir Senha
@endcomponent

Se você não solicitou a redefinição de senha, nenhuma ação adicional é necessária.

Obrigado,
{{ config('app.name') }}
@endcomponent
