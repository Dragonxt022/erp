<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContaAPagar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ContaAPagarApiController extends Controller
{
    /**
     * Cria uma nova conta a pagar via API externa.
     * 
     * Requer autenticação JWT no header: Authorization: Bearer {token}
     * O token será validado contra https://login.taiksu.com.br/api/user/me
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 1. Validação do Token JWT
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token de autenticação não fornecido.',
                'error' => 'Token JWT é obrigatório no header Authorization: Bearer {token}'
            ], 401);
        }

        // Valida o token contra o SSO
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get('https://login.taiksu.com.br/api/user/me');

            if ($response->failed()) {
                Log::warning('Tentativa de acesso à API com token inválido', [
                    'ip' => $request->ip(),
                    'status_code' => $response->status()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Token de autenticação inválido.',
                    'error' => 'Não foi possível validar o token JWT fornecido.'
                ], 401);
            }

            $userData = $response->json();

            // Log de acesso bem-sucedido
            Log::info('Acesso à API de Contas a Pagar', [
                'user_id' => $userData['id'] ?? null,
                'user_name' => $userData['name'] ?? null,
                'ip' => $request->ip()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao validar token JWT', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao validar autenticação.',
                'error' => 'Não foi possível conectar ao servidor de autenticação.'
            ], 500);
        }

        // 2. Validação dos Dados Recebidos
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'emitida_em' => 'required|date|date_format:Y-m-d',
            'vencimento' => 'required|date|date_format:Y-m-d|after_or_equal:emitida_em',
            'dias_lembrete' => 'required|integer|min:0',
            'unidade_id' => 'required|integer|exists:infor_unidade,id',
            'categoria_id' => 'required|integer|exists:categorias,id',
            'descricao' => 'nullable|string',
            'arquivo' => 'nullable|string',
            'status' => 'nullable|in:pendente,pago,atrasado,agendada'
        ], [
            // Mensagens personalizadas em português
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'valor.required' => 'O campo valor é obrigatório.',
            'valor.numeric' => 'O valor deve ser um número.',
            'valor.min' => 'O valor deve ser maior ou igual a zero.',
            'emitida_em.required' => 'O campo emitida_em é obrigatório.',
            'emitida_em.date' => 'O campo emitida_em deve ser uma data válida.',
            'emitida_em.date_format' => 'O campo emitida_em deve estar no formato YYYY-MM-DD.',
            'vencimento.required' => 'O campo vencimento é obrigatório.',
            'vencimento.date' => 'O campo vencimento deve ser uma data válida.',
            'vencimento.date_format' => 'O campo vencimento deve estar no formato YYYY-MM-DD.',
            'vencimento.after_or_equal' => 'A data de vencimento deve ser igual ou posterior à data de emissão.',
            'dias_lembrete.required' => 'O campo dias_lembrete é obrigatório.',
            'dias_lembrete.integer' => 'O campo dias_lembrete deve ser um número inteiro.',
            'dias_lembrete.min' => 'O campo dias_lembrete deve ser maior ou igual a zero.',
            'unidade_id.required' => 'O campo unidade_id é obrigatório.',
            'unidade_id.integer' => 'O campo unidade_id deve ser um número inteiro.',
            'unidade_id.exists' => 'A unidade informada não existe.',
            'categoria_id.required' => 'O campo categoria_id é obrigatório.',
            'categoria_id.integer' => 'O campo categoria_id deve ser um número inteiro.',
            'categoria_id.exists' => 'A categoria informada não existe.',
            'status.in' => 'O status deve ser: pendente, pago, atrasado ou agendada.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        // 3. Criação da Conta a Pagar
        try {
            $contaAPagar = ContaAPagar::create([
                'nome' => $request->nome,
                'valor' => $request->valor,
                'emitida_em' => $request->emitida_em,
                'vencimento' => $request->vencimento,
                'descricao' => $request->descricao,
                'arquivo' => $request->arquivo,
                'dias_lembrete' => $request->dias_lembrete,
                'status' => $request->status ?? 'agendada',
                'unidade_id' => $request->unidade_id,
                'categoria_id' => $request->categoria_id
            ]);

            Log::info('Conta a pagar criada via API', [
                'conta_id' => $contaAPagar->id,
                'user_id' => $userData['id'] ?? null,
                'unidade_id' => $request->unidade_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Conta a pagar criada com sucesso.',
                'data' => [
                    'id' => $contaAPagar->id,
                    'nome' => $contaAPagar->nome,
                    'valor' => $contaAPagar->valor,
                    'emitida_em' => $contaAPagar->emitida_em,
                    'vencimento' => $contaAPagar->vencimento,
                    'status' => $contaAPagar->status,
                    'unidade_id' => $contaAPagar->unidade_id,
                    'categoria_id' => $contaAPagar->categoria_id,
                    'created_at' => $contaAPagar->created_at
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao criar conta a pagar via API', [
                'error' => $e->getMessage(),
                'user_id' => $userData['id'] ?? null,
                'data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao criar conta a pagar.',
                'error' => 'Ocorreu um erro interno ao processar a solicitação.'
            ], 500);
        }
    }
}
