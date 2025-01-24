<?php

namespace App\Http\Controllers;

use App\Models\DefaultCanaisVenda;
use App\Models\UnidadeCanaisVenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DefaultCanaisVendaController extends Controller
{
    // Lista todos os canais de vendas
    public function index()
    {
        // Obtém todos os canais de vendas com status ativo
        $canaisVendas = DefaultCanaisVenda::where('status', true)->get();

        // Retorna a resposta com os canais de vendas ativos (pode ser em formato JSON ou uma view)
        return response()->json($canaisVendas);
    }

    // Exibe um canal de venda específico
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

        // Buscar o canal de venda associado à unidade e ao ID
        $canalVenda = UnidadeCanaisVenda::where('canal_de_vendas_id', $id)
            ->where('unidade_id', $unidadeId)
            ->first();

        if (!$canalVenda) {
            return response()->json(['error' => 'Canal de venda não encontrado para esta unidade.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $canalVenda,
        ]);
    }

    // Método usado para atualizar o canal de vendas ou criar um novo
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
            'canal_de_vendas_id' => 'required|exists:default_canais_vendas,id',
            'porcentagem' => 'required|numeric|min:0|max:100',
            'status' => 'required|boolean',
        ]);

        // Adicionar o unidade_id do usuário logado ao array de dados
        $validated['unidade_id'] = $user->unidade_id;

        // Criar ou atualizar o canal de venda
        $canalVenda = UnidadeCanaisVenda::updateOrCreate(
            [
                'unidade_id' => $validated['unidade_id'],
                'canal_de_vendas_id' => $validated['canal_de_vendas_id'],
            ],
            [
                'porcentagem' => $validated['porcentagem'],
                'status' => $validated['status'],
            ]
        );

        // Retornar o canal de venda atualizado/criado
        return response()->json($canalVenda);
    }
}
