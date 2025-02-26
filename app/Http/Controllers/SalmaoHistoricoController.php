<?php

namespace App\Http\Controllers;

use App\Models\ListaProduto;
use App\Models\MovimentacoesEstoque;
use App\Models\SalmaoCalibre;
use App\Models\SalmaoHistorico;
use App\Models\UnidadeEstoque;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalmaoHistoricoController extends Controller
{
    /**
     *
     * Pagina de Analytics de limpeza de residuos
     */
    public function getHistoricoSalmao(Request $request)
    {
        $user = Auth::user();
        $unidadeId = $user->unidade_id;

        // Obter as datas de início e fim
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // Aproveitamento médio
        $aproveitamentoMedio = SalmaoHistorico::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->avg('aproveitamento');

        // Colaboradores mais eficientes
        $colaboradoresEficientes = SalmaoHistorico::select('responsavel_id', DB::raw('AVG(aproveitamento) as media_aproveitamento'))
            ->where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->groupBy('responsavel_id')
            ->orderBy('media_aproveitamento', 'desc')
            ->with('responsavel')
            ->get()
            ->map(function ($item) {

                return [
                    'responsavel_id' => $item->responsavel_id,
                    'media_aproveitamento' => number_format($item->media_aproveitamento, 2, ',', '.'),
                    'responsavel' => [
                        'id' => $item->responsavel->id,
                        'name' => $item->responsavel->name,
                        'profile_photo_path' => $item->responsavel->profile_photo_path ? '/storage/' . $item->responsavel->profile_photo_path : null,
                        'email' => $item->responsavel->email,
                        'cpf' => $item->responsavel->cpf,
                    ],
                ];
            });

        // Histórico de movimentações
        $historico = SalmaoHistorico::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->with(['calibre', 'responsavel'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'responsavel_id' => $item->responsavel_id,
                    'calibre_id' => $item->calibre_id,
                    'valor_pago' => 'R$ ' . number_format($item->valor_pago, 2, ',', '.'),
                    'peso_bruto' => number_format($item->peso_bruto, 3, ',', '.'),
                    'peso_limpo' => number_format($item->peso_limpo, 3, ',', '.'),
                    'aproveitamento' => number_format($item->aproveitamento, 2, ',', '.'),
                    'desperdicio' => number_format($item->desperdicio, 3, ',', '.'),
                    'unidade_id' => $item->unidade_id,
                    'created_at' => Carbon::parse($item->created_at)->format('d/m/Y H:i'),
                    'updated_at' => Carbon::parse($item->updated_at)->format('d/m/Y H:i'),
                    'calibre' => [
                        'id' => $item->calibre->id,
                        'nome' => $item->calibre->nome,
                        'tipo' => $item->calibre->tipo,
                    ],
                    'responsavel' => [
                        'id' => $item->responsavel->id,
                        'name' => $item->responsavel->name,
                        'email' => $item->responsavel->email,
                        'cpf' => $item->responsavel->cpf,
                    ],
                ];
            });

        return response()->json([
            'aproveitamento_medio' => number_format($aproveitamentoMedio, 2, ',', '.'),
            'colaboradores_eficientes' => $colaboradoresEficientes,
            'historico' => $historico,
        ]);
    }

    // Pagina onde realizamos a incerção dos dados da pagina de limeza de residuos.
    public function index()
    {
        // Usuário autenticado
        $user = Auth::user();

        // Verifica se o usuário está autenticado
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado.'], 401);
        }

        // Buscar todos os usuários da mesma unidade_id (sem excluir o usuário autenticado)
        $colaboradores = User::where('unidade_id', $user->unidade_id)
            ->get()
            ->map(function ($colaborador) {
                // Retorna apenas os campos desejados
                return [
                    'id' => $colaborador->id,
                    'name' => $colaborador->name,
                    'email' => $colaborador->email,
                    'pin' => $colaborador->pin,
                ];
            });

        // Listar todos os calibres disponíveis
        $calibres = SalmaoCalibre::select('id', 'nome', 'tipo')->get();

        // Retornar os dados como JSON
        return response()->json([
            'calibres' => $calibres,
            'colaboradores' => $colaboradores,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado.'], 401);
        }

        // Validação inicial incluindo o PIN
        $request->validate([
            'pin' => 'required|numeric', // Movido para cá para validar antes
        ]);

        // Verificação segura do PIN
        if (!hash_equals((string) $user->pin, (string) $request->pin)) {
            return response()->json(['error' => 'Credenciais inválidas'], 403);
        }

        // Restante da validação dos dados
        $validated = $request->validate([
            'responsavel_id' => 'required|exists:users,id',
            'calibre_id' => 'required|exists:salmao_calibres,id',
            'valor_pago' => 'required|numeric|min:0',
            'peso_bruto' => 'required|numeric|min:0',
            'peso_limpo' => 'required|numeric|min:0',
            'aproveitamento' => 'required|numeric|between:0,100',
            'desperdicio' => 'required|numeric|min:0',
        ]);

        // Iniciar transação
        DB::beginTransaction();

        try {
            // 1. Registrar no SalmaoHistorico
            $historico = SalmaoHistorico::create([
                'responsavel_id' => $validated['responsavel_id'],
                'calibre_id' => $validated['calibre_id'],
                'valor_pago' => $validated['valor_pago'],
                'peso_bruto' => $validated['peso_bruto'],
                'peso_limpo' => $validated['peso_limpo'],
                'aproveitamento' => $validated['aproveitamento'],
                'desperdicio' => $validated['desperdicio'],
                'unidade_id' => $user->unidade_id,
            ]);

            // 2. Buscar "Salmão Limpo" na ListaProduto
            $salmaoLimpo = ListaProduto::firstOrCreate(
                ['id' => 84], // Busca por nome
                [ // Valores padrão apenas se criar um novo registro
                    'categoria_id' => 13,
                    'unidadeDeMedida' => 'a_granel',
                    'prioridade' => 1,
                ]
            );

            // Se não existir, lançar uma exceção
            if (!$salmaoLimpo) {
                throw new \Exception('O produto "Salmão Limpo" não foi encontrado na lista de produtos. Por favor, cadastre-o antes de continuar.');
            }

            $precoPorQuilo = $validated['peso_limpo'] > 0 ? $validated['valor_pago'] / $validated['peso_limpo'] : 0;


            // 3. Adicionar ao estoque (UnidadeEstoque)
            $estoque = UnidadeEstoque::create([
                'insumo_id' => $salmaoLimpo->id,
                'fornecedor_id' => 6,
                'usuario_id' => Auth::id(),
                'unidade_id' => $user->unidade_id,
                'quantidade' => $validated['peso_limpo'],
                'preco_insumo' => $precoPorQuilo,
                'categoria_id' => $salmaoLimpo->categoria_id,
                'operacao' => 'Entrada',
                'unidade' => 'kg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Registrar movimentação no histórico de estoques
            MovimentacoesEstoque::create([
                'insumo_id' => $salmaoLimpo->id,
                'fornecedor_id' => 6,
                'usuario_id' => Auth::id(),
                'quantidade' => $validated['peso_limpo'],
                'preco_insumo' => $precoPorQuilo,
                'operacao' => 'Entrada',
                'unidade' => 'kg',
                'unidade_id' => $user->unidade_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            // Dados principais do painel
            $estoque = UnidadeEstoque::where('unidade_id', $user->unidade_id)->where('quantidade', '>', 0)->get();
            $valorInsumos = $estoque->reduce(function ($total, $item) {
                $preco = $item->preco_insumo;
                $quantidade = $item->quantidade;
                return $item->unidade === 'kg' ? $total + $preco : $total + ($preco * $quantidade);
            }, 0);

            $saldoAtual = $valorInsumos;


            DB::table('controle_saldo_estoques')->insert([
                'ajuste_saldo' => $saldoAtual,
                'data_ajuste' => now(),
                'motivo_ajuste' => 'Atualização após entrada',
                'unidade_id' => $user->unidade_id,
                'responsavel_id' => $validated['responsavel_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Confirmar transação
            DB::commit();

            return response()->json([
                'message' => 'Registro salvo e estoque atualizado com sucesso!',
                'historico' => $historico,
                'estoque' => $estoque,
            ], 201);
        } catch (\Exception $e) {
            // Reverter transação em caso de erro
            DB::rollBack();
            return response()->json(['error' => 'Erro ao salvar: ' . $e->getMessage()], 500);
        }
    }
}
