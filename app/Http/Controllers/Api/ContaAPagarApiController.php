<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContaAPagar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Mail\ComprovanteContaAPagarMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\InforUnidade;
use App\Services\EmailApiService;
use Illuminate\Support\Facades\View;

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
        // 1. Obtém o usuário autenticado via Middleware SSO
        $user = auth()->user();

        if (!$user) {
             return response()->json([
                'status' => 'error',
                'message' => 'Usuário não autenticado.',
                'error' => 'Falha na autenticação via SSO.'
            ], 401);
        }

        // Dados do usuário para log e e-mail
        // Como o middleware já sincronizou, usamos os dados do model User
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];

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
            'status' => 'nullable|in:pendente,pago,atrasado,agendada',
            'ignora_email' => 'nullable'
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
            'status.in' => 'O status deve ser: pendente, pago, atrasado ou agendada.',
            'ignora_email.in' => 'O campo ignora_email deve ser true ou false.'
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
                'categoria_id' => $request->categoria_id,
                'ignora_email' => $request->ignora_email ?? false
            ]);

            $nomeUsuario = $userData['name'] ?? 'API';
            $contaAPagar->registrarLog('criacao', $contaAPagar->status, null, $nomeUsuario);


            // Notificações por E-mail
            if (!$request->ignora_email) {
                try {
                    $unidade = InforUnidade::find($request->unidade_id);
                    $nomeUnidade = $unidade ? $unidade->cidade . ' - ' . $unidade->bairro : 'Unidade ' . $request->unidade_id;

                    // Criar um objeto de usuário genérico para o e-mail (já que o usuário vem de SSO)
                    $usuarioFake = (object)[
                        'name' => $userData['name'] ?? 'Usuário API',
                        'email' => $userData['email'] ?? null
                    ];

                    $destinatarios = [];

                    // 1. Quem criou a conta
                    if ($usuarioFake->email) {
                        $destinatarios[] = $usuarioFake->email;
                    }

                    // 2. Franqueados da mesma unidade (REMOCAO de colaboradores)
                    // "se ele for franqueado e colaborador, não envie o email, somente se ele for somente franqueado!"
                    $franqueadosUnidade = User::where('unidade_id', $request->unidade_id)
                        ->where('franqueado', 1)
                        ->where('colaborador', 0) // Exclui quem também é colaborador
                        ->where('status', 'ativo')
                        ->pluck('email')
                        ->toArray();

                    // 3. Franqueadoras (Global)
                    $franqueadorasGlobal = User::where('franqueadora', 1)
                        ->where('status', 'ativo')
                        ->pluck('email')
                        ->toArray();

                    $destinatarios = array_unique(array_merge($destinatarios, $franqueadosUnidade, $franqueadorasGlobal));

                    // Preparar anexos se houver arquivo
                    $attachments = [];
                    if ($contaAPagar->arquivo && file_exists(public_path($contaAPagar->arquivo))) {
                        $path = public_path($contaAPagar->arquivo);
                        $attachments[] = [
                            'filename' => basename($path),
                            'content' => base64_encode(file_get_contents($path))
                        ];
                    }

                    $emailService = new EmailApiService();
                    $subject = 'Comprovante de Cadastro de Conta a Pagar - #' . $contaAPagar->id . ' | ' . $nomeUnidade;
                    
                    $dataCadastro = now()->format('d/m/Y H:i:s');
                    $body = View::make('emails.comprovante-conta-a-pagar', [
                        'conta' => $contaAPagar,
                        'usuario' => $usuarioFake,
                        'nomeUnidade' => $nomeUnidade,
                        'dataCadastro' => $dataCadastro,
                    ])->render();

                    foreach ($destinatarios as $email) {
                        $emailService->send($email, $subject, $body, $attachments);
                    }
                } catch (\Exception $mailEx) {
                    Log::error('Erro ao enviar e-mail de comprovante (API): ' . $mailEx->getMessage());
                }
            }

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
