<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CanalVenda;
use App\Models\UnidadePaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaixaController extends Controller
{
    // Método para abrir o caixa
    public function abrirCaixa(Request $request)
    {
        // Validação do valor inicial
        $request->validate([
            'valor_inicial' => 'required|numeric|min:1',
        ]);

        // Obtém o ID da unidade e o usuário logado
        $unidadeId = Auth::user()->unidade_id;
        $responsavelId = Auth::id();
        $valorInicial = $request->valor_inicial;

        // Cria um novo registro de caixa
        $caixa = Caixa::create([
            'unidade_id' => $unidadeId,
            'responsavel_id' => $responsavelId,
            'valor_inicial' => $valorInicial,
            'valor_final' => $valorInicial,
            'status' => 1, // Marca o caixa como aberto
            'motivo' => 'Abertura inicial',
        ]);

        return response()->json([
            'message' => 'Caixa aberto com sucesso!',
            'caixa' => $caixa,
        ]);
    }

    // Método para fechar o caixa
    public function fecharCaixa(Request $request, $id)
    {
        // Validação do valor final
        $request->validate([
            'valor_final' => 'required|numeric|min:1',
        ]);

        // Encontra o caixa com base no ID
        $caixa = Caixa::findOrFail($id);

        // Verifica se o caixa já foi fechado
        if ($caixa->status === 'fechado') {
            return response()->json(['message' => 'Este caixa já foi fechado.'], 400);
        }

        // Atualiza o valor final e o status do caixa
        $caixa->valor_final = $request->valor_final;
        $caixa->status = 0;
        $caixa->motivo = $request->motivo ?? 'Fechamento de caixa';
        $caixa->save();

        return response()->json([
            'message' => 'Caixa fechado com sucesso!',
            'caixa' => $caixa,
        ]);
    }

    // Método para exibir os detalhes do caixa
    public function detalhesCaixa($id)
    {
        // Busca o caixa pelo ID e carrega os relacionamentos necessários
        $caixa = Caixa::with([
            'unidade',
            'responsavel',
            'fluxosCaixa',
            'fechamentoCaixas',
            'canaisVendas',
            'historicoMetodosPagamento',
            'historicoCanaisVendas'
        ])->findOrFail($id);

        return response()->json([
            'caixa' => $caixa,
        ]);
    }

    // Método para listar todos os caixas abertos
    public function listarCaixasAbertos()
    {
        // Obtém todos os caixas abertos da unidade do usuário logado
        $caixas = Caixa::where('unidade_id', Auth::user()->unidade_id)
            ->where('status', 1) // Verifica se o status é 1 (aberto)
            ->get();

        return response()->json([
            'aberto' => !$caixas->isEmpty(), // Retorna true se houver caixas abertos
            'caixas' => $caixas,
        ]);
    }


    // Lista os métodos de pagamentos e canais de vendas ativos da unidade do usuário autenticado
    public function listarMetodosEcanaisAtivos()
    {
        // Obtém o ID da unidade do usuário autenticado
        $unidadeId = Auth::user()->unidade_id;

        if (!$unidadeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário não pertence a nenhuma unidade.',
            ], 403);
        }

        // Filtra os métodos de pagamento pela unidade do usuário e status ativo
        $metodosPagamento = UnidadePaymentMethod::where('unidade_id', $unidadeId)
            ->where('status', 1) // Apenas métodos ativos
            ->with('defaultPaymentMethod') // Carrega os detalhes do método de pagamento padrão
            ->get();

        // Filtra os canais de vendas pela unidade do usuário e status ativo
        $canaisVendas = CanalVenda::where('unidade_id', $unidadeId)
            ->whereHas('canalDeVendas', function ($query) {
                $query->where('status', 1); // Apenas canais de vendas ativos
            })
            ->with('canalDeVendas') // Carrega os detalhes do canal de vendas padrão
            ->get();

        return response()->json([
            'status' => 'success',
            'metodosPagamento' => $metodosPagamento,
            'canaisVendas' => $canaisVendas,
        ]);
    }
}
