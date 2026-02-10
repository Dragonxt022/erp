<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\SalmaoHistoricoService;

class SalmaoHistoricoApiController extends Controller
{
    /**
     * Store a newly created resource in storage via External API.
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

        // 2. Validação dos Dados Recebidos
        $validator = Validator::make($request->all(), [
            'responsavel_id' => 'required|exists:users,id',
            'calibre_id' => 'required|exists:salmao_calibres,id',
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'valor_pago' => 'required|numeric|min:0',
            'peso_bruto' => 'required|numeric|min:0',
            'peso_limpo' => 'required|numeric|min:0',
            'aproveitamento' => 'required|numeric|between:0,100',
            'desperdicio' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        // 3. Processamento via Service
        try {
            $service = new SalmaoHistoricoService();
            $result = $service->registrarHistorico($validator->validated(), $user);

            return response()->json([
                'status' => 'success',
                'message' => 'Registro salvo e estoque atualizado com sucesso!',
                'data' => [
                    'historico' => $result['historico'],
                    'estoque' => $result['estoque'],
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar SalmaoHistorico via API', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar solicitação.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
