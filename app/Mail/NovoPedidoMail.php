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

    public function __construct($pedido, $fileName, $name)
    {
        $this->pedido = $pedido;
        $this->fileName = $fileName;
        $this->name = $name;
    }

    public function build()
    {
        return $this->view('emails.novo-pedido')
            ->with([
                'pedido' => $this->pedido,
                'fileName' => $this->fileName,
                'name' => $this->name,
            ])
            ->subject('Novo Pedido Recebido')
            ->attach(storage_path("app/public/pedidos/{$this->fileName}"));
    }
}
