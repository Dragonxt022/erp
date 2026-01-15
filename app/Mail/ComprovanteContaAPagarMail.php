<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ComprovanteContaAPagarMail extends Mailable
{
    use Queueable, SerializesModels;

    public $conta;
    public $usuario;
    public $nomeUnidade;
    public $dataCadastro;

    public function __construct($conta, $usuario, $nomeUnidade)
    {
        $this->conta = $conta;
        $this->usuario = $usuario;
        $this->nomeUnidade = $nomeUnidade;
        $this->dataCadastro = now()->format('d/m/Y H:i:s');
    }

    public function build()
    {
        $subject = 'Comprovante de Cadastro de Conta a Pagar - #' . $this->conta->id . ' | ' . $this->nomeUnidade;

        return $this->view('emails.comprovante-conta-a-pagar')
            ->with([
                'conta' => $this->conta,
                'usuario' => $this->usuario,
                'nomeUnidade' => $this->nomeUnidade,
                'dataCadastro' => $this->dataCadastro,
            ])
            ->subject($subject);
    }
}
