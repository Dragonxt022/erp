<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NovoPedidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;
    public $fileName;
    public $name;
    public $nomeUnidade;
    public $dataPedido;

    public function __construct($pedido, $fileName, $name, $nomeUnidade, $dataPedido)
    {
        $this->pedido = $pedido;
        $this->fileName = $fileName;
        $this->name = $name;
        $this->nomeUnidade = $nomeUnidade;
        $this->dataPedido = $dataPedido;
    }

    public function build()
    {
        $subject = 'Pedido #' . $this->pedido->id . ' - Taiksu | ' . $this->nomeUnidade . ' | ' . $this->dataPedido;

        $mail = $this->view('emails.novo-pedido')
            ->with([
                'pedido' => $this->pedido,
                'fileName' => $this->fileName,
                'name' => $this->name,
                'nomeUnidade' => $this->nomeUnidade,
                'dataPedido' => $this->dataPedido,
            ])
            ->subject($subject);

        if ($this->fileName) {
            $pdfPath = public_path("storage/pedidos/{$this->fileName}");

            if (file_exists($pdfPath)) {
                $mail->attach($pdfPath);
            }
        }

        return $mail;
    }
}
