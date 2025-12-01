<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token; // Adicionei a propriedade para armazenar o token

    /**
     * Construtor para inicializar o e-mail.
     *
     * @param $user
     * @param $token
     */
    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token; // Inicializa o token

    }

    /**
     * Constrói o e-mail.
     *
     * @return $this
     */
    public function build()
    {
        // Gera a URL completa para redefinição de senha
        $resetPasswordUrl = route('password.reset', ['token' => $this->token]);

        return $this->subject('Sua senha foi criada!')
                    ->view('emails.reset-password')
                    ->with([
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'resetPasswordUrl' => $resetPasswordUrl,
                    ]);

        Log::info('E-mail de redefinição de senha enviado para ' . $this->user->email); // Adicionei um log para registrar o envio do e-mail
    }
}
