<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class TestSmtpConnection extends Command
{
    protected $signature = 'mail:test-smtp';
    protected $description = 'Testa a conexão com o servidor SMTP';

    public function handle()
    {
        $this->info('Testando conexão com o servidor SMTP...');

        try {
            // Pega as configurações do .env
            $host = config('mail.mailers.smtp.host');
            $port = config('mail.mailers.smtp.port');
            $username = config('mail.mailers.smtp.username');
            $password = config('mail.mailers.smtp.password');
            $encryption = config('mail.mailers.smtp.encryption');

            // Cria a conexão com o servidor SMTP
            $transport = new EsmtpTransport($host, $port, $encryption === 'ssl');
            $transport->setUsername($username);
            $transport->setPassword($password);

            // Inicia a conexão
            $transport->start();

            $this->info('Conexão com o servidor SMTP bem-sucedida!');

            // Fecha a conexão
            $transport->stop();
        } catch (\Exception $e) {
            $this->error('Falha ao conectar ao servidor SMTP.');
            $this->error('Erro: ' . $e->getMessage());
        }
    }
}