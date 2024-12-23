<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    /**
     * O token de redefinição de senha.
     *
     * @var string
     */
    protected $token;

    /**
     * Cria uma nova instância de notificação.
     *
     * @param string $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Define os canais de entrega da notificação.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Obtém a representação de e-mail da notificação.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // A URL para redefinir a senha, usando o token
        $resetUrl = url(route('password.reset', ['token' => $this->token], false));

        return (new MailMessage)
            ->subject(Lang::get('Redefinir Acesso'))
            ->markdown('emails.password_reset', [
                'resetUrl' => $resetUrl, // URL de redefinição
                'name' => $notifiable->name // Nome do usuário
            ]);
    }


    /**
     * Obtém a representação da notificação em formato de array.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [];
    }
}
