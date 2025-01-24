<?php

namespace App\Http\Controllers;

use App\Models\DefaultPaymentMethod;
use App\Models\UnidadePaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DefaultPaymentMethodController extends Controller
{
    // Lista todos os métodos de pagamento
    public function index()
    {
        // Obtém todos os métodos de pagamento com status ativo
        $paymentMethods = DefaultPaymentMethod::where('status', true)->get();

        // Retorna a resposta com os métodos de pagamento ativos (pode ser em formato JSON ou uma view)
        return response()->json($paymentMethods);
    }

    public function show($id)
    {
        // Obter o usuário autenticado
        $user = Auth::user();

        // Verificar se o usuário pertence a uma unidade
        if (!$user || !$user->unidade_id) {
            return response()->json(['error' => 'Usuário não associado a uma unidade.'], 403);
        }


        // Obter a unidade do usuário
        $unidadeId = $user->unidade_id;

        // Buscar o método de pagamento associado à unidade e ao ID
        $paymentMethod = UnidadePaymentMethod::where('default_payment_method_id', $id)
            ->where('unidade_id', $unidadeId)
            ->first();

        if (!$paymentMethod) {
            return response()->json(['error' => 'Método de pagamento não encontrado para esta unidade.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $paymentMethod,
        ]);
    }

    // Métod usado para atualizar a forma de pagamento ou criar um novo
    public function upsert(Request $request)
    {
        // Obter o usuário autenticado
        $user = Auth::user();

        // Verificar se o usuário pertence a uma unidade
        if (!$user || !$user->unidade_id) {
            return response()->json(['error' => 'Usuário não associado a uma unidade.'], 403);
        }

        // Validar os dados da requisição
        $validated = $request->validate([
            'default_payment_method_id' => 'required|exists:default_payment_methods,id',
            'porcentagem' => 'required|numeric|min:0|max:100',
            'status' => 'required|boolean',
        ]);

        // Adicionar o unidade_id do usuário logado ao array de dados
        $validated['unidade_id'] = $user->unidade_id;

        // Criar ou atualizar o método de pagamento
        $paymentMethod = UnidadePaymentMethod::updateOrCreate(
            [
                'unidade_id' => $validated['unidade_id'],
                'default_payment_method_id' => $validated['default_payment_method_id'],
            ],
            [
                'porcentagem' => $validated['porcentagem'],
                'status' => $validated['status'],
            ]
        );

        // Retornar o método de pagamento atualizado/criado
        return response()->json($paymentMethod);
    }
}
