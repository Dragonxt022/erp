<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | Define o canal padrão de log baseado no ambiente.
    | Recomendado: "stack" para combinar múltiplos canais e ter flexibilidade.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'), // "stack" como padrão é uma boa prática para combinar canais

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | Canal específico para logs de depreciação (PHP ou bibliotecas).
    | Mantido como "null" por padrão para evitar logs desnecessários.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Configuração dos canais de log disponíveis.
    | Cada canal é ajustado para atender cenários específicos.
    |
    */

    'channels' => [

        /*
         * Canal "stack": Combina múltiplos canais (ex.: "daily" e "stderr").
         * Útil para enviar logs para mais de um destino simultaneamente.
         */
        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', env('LOG_STACK', 'daily,stderr')), // Combina "daily" e "stderr"
            'ignore_exceptions' => false, // Mantém exceções para debugging
        ],

        /*
         * Canal "daily": Logs rotativos por dia.
         * Melhor prática para produção e desenvolvimento.
         */
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'info'), // "info" é suficiente para produção
            'days' => env('LOG_DAILY_DAYS', 14), // Mantém logs por 14 dias
            'formatter' => LineFormatter::class, // Formato simplificado
            'formatter_with' => [
                'format' => "[%datetime%] %level_name%: %message%\n", // Apenas data, nível e mensagem
                'dateFormat' => 'Y-m-d H:i:s', // Formato de data padrão
            ],
            'replace_placeholders' => true, // Suporte a placeholders do Laravel
        ],

        /*
         * Canal "single": Arquivo único para cenários específicos.
         * Pode ser usado para debugging ou testes rápidos.
         */
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'), // Mais verboso para desenvolvimento
            'formatter' => LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %level_name%: %message%\n",
                'dateFormat' => 'Y-m-d H:i:s',
            ],
            'replace_placeholders' => true,
        ],

        /*
         * Canal "stderr": Envia logs para o stderr, útil para containers (Docker).
         * Boa prática para ambientes de produção em cloud.
         */
        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'error'), // Apenas erros para evitar verbosidade
            'handler' => StreamHandler::class,
            'formatter' => LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %level_name%: %message%\n",
                'dateFormat' => 'Y-m-d H:i:s',
            ],
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class], // Processador para compatibilidade PSR
        ],

        /*
         * Canal "slack": Notificações para erros críticos.
         * Boa prática para alertas em produção.
         */
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'), // Apenas para erros graves
            'replace_placeholders' => true,
        ],

        /*
         * Canal "papertrail": Integração com Papertrail para monitoramento externo.
         * Útil para equipes que precisam de logs centralizados.
         */
        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        /*
         * Canal "syslog": Logs para o sistema operacional.
         * Útil para integração com ferramentas de monitoramento do SO.
         */
        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'error'), // Apenas erros para evitar verbosidade
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        /*
         * Canal "errorlog": Envia logs para o error_log do PHP.
         * Útil para debugging em servidores sem acesso direto aos arquivos.
         */
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'error'),
            'replace_placeholders' => true,
        ],

        /*
         * Canal "null": Desativa logs para depreciações ou cenários específicos.
         */
        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        /*
         * Canal "emergency": Arquivo para logs de emergência.
         * Mantido como fallback para situações críticas.
         */
        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],
];