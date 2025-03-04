<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class TestSmtpConnection extends Command
{
    // Definindo o signature com a opção --send-test-email
    protected $signature = 'mail:test-smtp {--send-test-email}';

    protected $description = 'Testa a conexão com o servidor SMTP e, opcionalmente, envia um e-mail de teste';

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

            // Se for TLS, ajusta o transporte
            if ($encryption === 'tls') {
                $transport->setStreamOptions([
                    'ssl' => [
                        'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT,
                    ],
                ]);
            }

            $transport->setUsername($username);
            $transport->setPassword($password);

            // Inicia a conexão
            $transport->start();

            $this->info('Conexão com o servidor SMTP bem-sucedida!');

            // Se a opção --send-test-email foi passada, envia um e-mail de teste
            if ($this->option('send-test-email')) {
                $this->info('Enviando e-mail de teste...');
                try {
                    $mailer = new Mailer($transport);
                    $email = (new Email())
                        ->from(config('mail.from.address'))
                        ->to('pissinatti2019@gmail.com') // Substitua pelo e-mail real para teste
                        ->subject('Teste de E-mail')
                        ->text('Este é um e-mail de teste enviado pelo comando Artisan.');
                    $mailer->send($email);
                    $this->info('E-mail de teste enviado com sucesso!');
                } catch (\Exception $e) {
                    $this->error('Falha ao enviar o e-mail de teste.');
                    $this->error('Erro: ' . $e->getMessage());
                }
            }

            // Fecha a conexão
            $transport->stop();
        } catch (\Exception $e) {
            $this->error('Falha ao conectar ao servidor SMTP.');
            $this->error('Erro: ' . $e->getMessage());
        }
    }
}