<?php

namespace App\Jobs;

use App\Services\UserSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncUsuariosDaUnidade implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $unidadeId;
    protected string $token;

    /**
     * Cria uma nova instância do job.
     *
     * @param int $unidadeId
     * @param string $token
     */
    public function __construct(int $unidadeId, string $token)
    {
        $this->unidadeId = $unidadeId;
        $this->token = $token;
    }

    /**
     * Executa o job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            UserSyncService::syncUnidade($this->unidadeId, $this->token);
        } catch (\Throwable $e) {
            Log::error("Erro no job SyncUsuariosDaUnidade para unidade {$this->unidadeId}", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Caso o job falhe permanentemente.
     *
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job SyncUsuariosDaUnidade falhou para unidade {$this->unidadeId}", [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
