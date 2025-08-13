<?php

namespace App\Http\Controllers;

use App\Models\ContaAPagar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ContaAPagarController extends Controller
{

    // Pagina principal para Listagem das contas
     public function index()
    {
        // Identifica o usuário autenticado e sua unidade
        $user = Auth::user();
        $unidade_id = $user->unidade_id;

        // 1. Obtém as contas PENDENTES da unidade, ordenadas por vencimento
        $contasPendentes = ContaAPagar::where('unidade_id', $unidade_id)
            ->where('status', 'pendente')
            ->orderBy('vencimento', 'asc')
            ->get();

        // 2. Obtém as contas PAGAS do mês vigente da unidade, filtradas pelo VENCIMENTO
        // Define o início e o fim do mês atual
        $primeiroDiaDoMes = Carbon::now()->startOfMonth();
        $ultimoDiaDoMes = Carbon::now()->endOfMonth();

        $contasPagasMesVigente = ContaAPagar::where('unidade_id', $unidade_id)
            ->where('status', 'pago')
            // Filtra as contas PAGAS que venceram (e foram pagas) dentro do mês atual
            ->whereBetween('vencimento', [$primeiroDiaDoMes, $ultimoDiaDoMes])
            ->orderBy('vencimento', 'asc')
            ->get();

        // 3. Combina as duas coleções (Pendentes primeiro, depois Pagas do Mês)
        $contas = $contasPendentes->concat($contasPagasMesVigente);

        // 4. Formata o valor para o formato de moeda brasileira
        $contas = $contas->map(function ($conta) {
            $conta->valor_formatado = 'R$ ' . number_format($conta->valor, 2, ',', '.');
            return $conta;
        });

        // Retorna a resposta com as contas
        return response()->json(['data' => $contas], 200);
    }

    //Pagina para Listagem de contas a pagar para pagina de Historico
    public function historicoPagas(Request $request) // Adicionamos Request para pegar o parametro da pagina
    {
        // Identifica o usuário autenticado e sua unidade
        $user = Auth::user();
        $unidade_id = $user->unidade_id;

        // Número de itens por página
        $perPage = 7; // Você pode tornar isso configurável ou passar via request também

        // Obtém TODAS as contas PAGAS da unidade com paginação
        // Ordena por vencimento (do mais recente para o mais antigo, para histórico)
        $contasPagasHistorico = ContaAPagar::where('unidade_id', $unidade_id)
            ->where('status', 'pago')
            ->orderBy('vencimento', 'desc') // Ordenar do mais recente para o mais antigo para histórico
            ->paginate($perPage); // Usamos paginate aqui!

        // Mapear os itens para formatar o valor.
        // É importante fazer isso APÓS a paginação, mas ANTES de retornar a resposta.
        $contasPagasHistorico->getCollection()->transform(function ($conta) {
            // Formatar o valor para o formato de moeda brasileira
            $conta->valor_formatado = 'R$ ' . number_format($conta->valor, 2, ',', '.');
            return $conta;
        });

        // Retorna a resposta com os dados paginados
        return response()->json($contasPagasHistorico, 200);
    }

    public function marcarComoPago($id)
    {
        // Busca a conta pelo ID e unidade do usuário autenticado
        $user = Auth::user();
        $conta = ContaAPagar::where('id', $id)
            ->where('unidade_id', $user->unidade_id)
            ->first();

        if (!$conta) {
            return response()->json(['message' => 'Conta não encontrada'], 404);
        }

        // Atualiza o status para "pago"
        $conta->status = 'pago';
        $conta->save();

        return response()->json(['message' => 'Conta marcada como paga com sucesso!', 'data' => $conta], 200);
    }

    // Seletor para mudar o status da conta a pagar
    public function atualizarStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pendente,pago,atrasado',
        ]);

        $conta = ContaAPagar::where('id', $id)
            ->where('unidade_id', Auth::user()->unidade_id)
            ->first();

        if (!$conta) {
            return response()->json(['message' => 'Conta não encontrada'], 404);
        }

        $conta->status = $request->status;
        $conta->save();

        return response()->json(['message' => 'Status atualizado com sucesso', 'data' => $conta]);
    }


    // Método para excluir a conta
    public function destroy($id)
    {
        try {
            // Obtém o usuário autenticado
            $user = Auth::user();

            // Tenta localizar a conta pelo ID e pelo unidade_id do usuário autenticado
            $conta = ContaAPagar::where('id', $id)
                ->where('unidade_id', $user->unidade_id)
                ->first();

            // Verifica se existe o arquivo associado à conta
            $arquivo = $conta->arquivo; // Supondo que 'arquivo' contém o nome do arquivo, sem o caminho completo
            $diretorio = public_path('storage/arquivos/' . $arquivo); // Diretório do arquivo

            // Se o arquivo existir, tenta apagá-lo
            if (File::exists($diretorio)) {
                File::delete($diretorio); // Apaga o arquivo
            }

            // Exclui o registro da conta
            $conta->delete();

            // Retorna uma resposta de sucesso
            return response()->json(['message' => 'Conta excluída com sucesso.'], 201);
        } catch (\Exception $e) {
            // Em caso de erro, retorna uma mensagem de erro
            return response()->json(['message' => 'Erro ao excluir a conta: ' . $e->getMessage()], 500);
        }
    }




    public function store(Request $request)
    {
        $validated = $request->validate([
            'valor' => 'required|numeric',
            'emitida_em' => 'required|date',
            'vencimento' => 'required|date|after_or_equal:emitida_em',
            'descricao' => 'nullable|string',
            'arquivo' => 'nullable|max:4048',
            'dias_lembrete' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        // Iniciar uma transação
        DB::beginTransaction();

        try {
            // Identifica o usuário autenticado
            $user = Auth::user();

            // Definindo o unidade_id do usuário autenticado
            $unidade_id = $user->unidade_id;

            // Obter o nome da categoria selecionada
            $categoria = DB::table('categorias')->where('id', $validated['categoria_id'])->first();
            $nomeCategoria = $categoria->nome;

            // Processamento do arquivo (se houver)
            $arquivoPath = null;
            if ($request->hasFile('arquivo')) {
                // Obter o ano e mês atuais
                $anoMes = Carbon::now()->format('Y/m');

                // Definir o diretório para armazenar o arquivo
                $diretorio = public_path('storage/arquivos/' . $anoMes);

                // Criar o diretório se não existir
                if (!File::exists($diretorio)) {
                    File::makeDirectory($diretorio, 0775, true); // Permissões 0775 e criação recursiva
                }

                // Gerar um nome único para o arquivo
                $nomeArquivo = uniqid() . '.' . $request->file('arquivo')->getClientOriginalExtension();

                // Mover o arquivo para o diretório desejado
                $request->file('arquivo')->move($diretorio, $nomeArquivo);

                // Caminho final do arquivo
                $arquivoPath = 'storage/arquivos/' . $anoMes . '/' . $nomeArquivo;
            }

            // Criação da conta a pagar com o id da unidade do usuário
            $conta = ContaAPagar::create([
                'nome' => $nomeCategoria,  // Nome é o mesmo da categoria
                'valor' => $validated['valor'],
                'emitida_em' => $validated['emitida_em'],
                'vencimento' => $validated['vencimento'],
                'descricao' => $validated['descricao'],
                'arquivo' => $arquivoPath, // Armazena o caminho do arquivo
                'dias_lembrete' => $validated['dias_lembrete'],
                'status' => 'pendente', // Status sempre será 'pendente'
                'unidade_id' => $unidade_id,
                'categoria_id' => $validated['categoria_id'],
            ]);

            // Commit da transação
            DB::commit();

            return response()->json(['message' => 'Conta cadastrada com sucesso!', 'data' => $conta], 201);
        } catch (\Exception $e) {
            // Caso ocorra erro, faz o rollback da transação
            DB::rollback();
            return response()->json(['message' => 'Erro ao cadastrar a conta', 'error' => $e->getMessage()], 500);
        }
    }

    // Controle de vencietno
    public function verificarContasAtrasadas()
    {
        $contas = ContaAPagar::where('status', 'pendente')
            ->whereDate('vencimento', '<', Carbon::today())
            ->get();

        foreach ($contas as $conta) {
            $conta->status = 'atrasado';
            $conta->save();
        }
    }
}
