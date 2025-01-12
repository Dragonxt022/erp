<?php

namespace App\Http\Controllers;

use App\Models\UnidadeEstoque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnidadeEstoqueController extends Controller
{
    public function index()
    {
        // Obtém a unidade do usuário autenticado
        $unidadeId = Auth::user()->unidade_id;

        // Filtra os estoques pela unidade e formata os dados
        $estoques = UnidadeEstoque::with(['insumo', 'fornecedor', 'usuario', 'unidade'])
            ->where('unidade_id', $unidadeId)
            ->get()
            ->map(function ($estoque) {
                return [
                    'id' => $estoque->id,
                    'insumo' => [
                        'id' => $estoque->insumo->id,
                        'nome' => $estoque->insumo->nome,
                        'profile_photo' => $estoque->insumo->profile_photo,
                        'categoria' => $estoque->insumo->categoria,
                        'unidadeDeMedida' => $estoque->insumo->unidadeDeMedida,
                    ],
                    'fornecedor' => [
                        'id' => $estoque->fornecedor->id,
                        'razao_social' => $estoque->fornecedor->razao_social,
                    ],
                    'usuario' => [
                        'id' => $estoque->usuario->id,
                        'nome' => $estoque->usuario->name,
                    ],
                    'unidade' => [
                        'id' => $estoque->unidade->id,
                        'cidade' => $estoque->unidade->cidade,
                    ],
                    'quantidade' => $estoque->quantidade,
                    'preco_insumo' => $estoque->preco_insumo,
                    'operacao' => $estoque->operacao,
                    'data_criacao' => $estoque->created_at->format('d/m/Y H:i'),
                ];
            });

        // Retorna os dados no formato JSON
        return response()->json($estoques);
    }

    public function create()
    {
        return view('unidade_estoque.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'insumo_id' => 'required|exists:lista_produtos,id',
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'usuario_id' => 'required|exists:users,id',
            'quantidade' => 'required|integer',
            'preco_insumo' => 'required|numeric',
            'operacao' => 'required|in:Retirada,Entrada,Saida',
            'unidade_id' => 'required|exists:infor_unidade,id',
        ]);

        UnidadeEstoque::create($validated);

        return redirect()->route('unidade_estoque.index')->with('success', 'Entrada de estoque registrada com sucesso!');
    }

    public function show(UnidadeEstoque $unidadeEstoque)
    {
        return view('unidade_estoque.show', compact('unidadeEstoque'));
    }

    public function edit(UnidadeEstoque $unidadeEstoque)
    {
        return view('unidade_estoque.edit', compact('unidadeEstoque'));
    }

    public function update(Request $request, UnidadeEstoque $unidadeEstoque)
    {
        $validated = $request->validate([
            'insumo_id' => 'required|exists:lista_produtos,id',
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'usuario_id' => 'required|exists:users,id',
            'quantidade' => 'required|integer',
            'preco_insumo' => 'required|numeric',
            'operacao' => 'required|in:Retirada,Entrada,Saida',
            'unidade_id' => 'required|exists:infor_unidade,id',
        ]);

        $unidadeEstoque->update($validated);

        return redirect()->route('unidade_estoque.index')->with('success', 'Entrada de estoque atualizada com sucesso!');
    }

    public function destroy(UnidadeEstoque $unidadeEstoque)
    {
        $unidadeEstoque->delete();

        return redirect()->route('unidade_estoque.index')->with('success', 'Entrada de estoque excluída com sucesso!');
    }
}
