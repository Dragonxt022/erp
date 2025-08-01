<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notificacao;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificacaoController extends Controller
{
    public function minhasNotificacoes()
    {
        $user = Auth::user();

        $notificacoes = Notificacao::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere(function ($subQuery) {
                    $subQuery->where('global', true)
                        ->whereNull('user_id');
                });
        })
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $notificacoes->map(function ($notificacao) {
            $notificacao->tempo = $this->tempoDecorrido($notificacao->created_at);
            return $notificacao;
        });

        $notificacoes->load('setor');

        return response()->json(['data' => $notificacoes]);
    }




    public function enviarParaSetor(Request $request)
    {
        $request->validate([
            'mensagem' => 'required|string|max:255',
            'setor_id' => 'required|exists:operacionais,id',
        ]);

        $usuarios = User::where('setor_id', $request->setor_id)->get();

        foreach ($usuarios as $usuario) {
            Notificacao::create([
                'user_id'   => $usuario->id,
                'titulo'    => 'Comunicado ao setor',
                'mensagem'  => $request->mensagem,
                'tipo'      => 'alerta',
                'global'    => false,
                'setor_id'  => $request->setor_id,
            ]);
        }

        return response()->json(['message' => 'Notificações enviadas para todos os usuários do setor.'], 201);
    }

    /**
     * Marca uma notificação específica como lida
     *
     * @param int $id ID da notificação
     * @return \Illuminate\Http\Response
     */
    public function marcarComoLida($id)
    {
        $user = Auth::user();

        $notificacao = Notificacao::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notificacao) {
            return response()->json([
                'message' => 'Notificação não encontrada'
            ], 404);
        }

        // Só atualiza se ainda não estiver lida
        if ($notificacao->lida == 0) {
            $notificacao->lida = 1;
            $notificacao->lida_em = now();
            $notificacao->save();
        }

        return response()->json([
            'message' => 'Notificação marcada como lida',
            'data' => $notificacao
        ]);
    }

    /**
     * Marca todas as notificações do usuário como lidas
     *
     * @return \Illuminate\Http\Response
     */
    public function marcarTodasComoLidas()
    {
        $user = Auth::user();

        // Atualiza apenas as notificações não lidas
        $count = Notificacao::where('user_id', $user->id)
            ->where('lida', 0)
            ->update([
                'lida' => 1,
                'lida_em' => now()
            ]);

        return response()->json([
            'message' => 'Todas as notificações foram marcadas como lidas',
            'count' => $count
        ]);
    }

    /**
     * Retorna o total de notificações não lidas
     *
     * @return \Illuminate\Http\Response
     */
    public function totalNaoLidas()
    {
        $user = Auth::user();

        $count = Notificacao::where('user_id', $user->id)
            ->where('lida', 0)
            ->count();

        return response()->json([
            'total_nao_lidas' => $count
        ]);
    }

    /**
     * Formata o tempo decorrido de uma data em formato amigável
     *
     * @param string $data Data a ser formatada
     * @return string
     */
    private function tempoDecorrido($data)
    {
        $now = Carbon::now();
        $data = Carbon::parse($data);

        $diffInSeconds = $now->diffInSeconds($data);

        if ($diffInSeconds < 60) {
            return "agora mesmo";
        }

        if ($diffInSeconds < 3600) {
            $minutes = floor($diffInSeconds / 60);
            return $minutes . " " . ($minutes == 1 ? "minuto" : "minutos") . " atrás";
        }

        if ($diffInSeconds < 86400) {
            $hours = floor($diffInSeconds / 3600);
            return $hours . " " . ($hours == 1 ? "hora" : "horas") . " atrás";
        }

        if ($diffInSeconds < 604800) {
            $days = floor($diffInSeconds / 86400);
            return $days . " " . ($days == 1 ? "dia" : "dias") . " atrás";
        }

        if ($diffInSeconds < 2592000) {
            $weeks = floor($diffInSeconds / 604800);
            return $weeks . " " . ($weeks == 1 ? "semana" : "semanas") . " atrás";
        }

        if ($diffInSeconds < 31536000) {
            $months = floor($diffInSeconds / 2592000);
            return $months . " " . ($months == 1 ? "mês" : "meses") . " atrás";
        }

        $years = floor($diffInSeconds / 31536000);
        return $years . " " . ($years == 1 ? "ano" : "anos") . " atrás";
    }
}
