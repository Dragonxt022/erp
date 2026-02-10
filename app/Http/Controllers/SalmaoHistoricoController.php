<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
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
use App\Services\SalmaoHistoricoService;

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
        $dadosAproveitamento = SalmaoHistorico::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->selectRaw('SUM(peso_limpo) as total_limpo, SUM(peso_bruto) as total_bruto')
            ->first();

        $aproveitamentoMedio = 0;

        if ($dadosAproveitamento && $dadosAproveitamento->total_bruto > 0) {
            $aproveitamentoMedio = ($dadosAproveitamento->total_limpo / $dadosAproveitamento->total_bruto) * 100;
        }


        // Colaboradores mais eficientes
        $colaboradoresEficientes = SalmaoHistorico::select(
            'responsavel_id',
            DB::raw('SUM(peso_limpo) as total_limpo'),
            DB::raw('SUM(peso_bruto) as total_bruto')
        )
            ->where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
            ->groupBy('responsavel_id')
            ->with('responsavel')
            ->get()
            ->map(function ($item) {

                $mediaAproveitamento = 0;

                if ($item->total_bruto > 0) {
                    $mediaAproveitamento = ($item->total_limpo / $item->total_bruto) * 100;
                }

                return [
                    'responsavel_id' => $item->responsavel_id,
                    'media_aproveitamento' => round($mediaAproveitamento, 2), // número, não string
                    'responsavel' => [
                        'id' => $item->responsavel->id,
                        'name' => $item->responsavel->name,
                        'profile_photo_url' => $item->responsavel->profile_photo_url,
                        'email' => $item->responsavel->email,
                        'cpf' => $item->responsavel->cpf,
                    ],
                ];
            })
            ->sortByDesc('media_aproveitamento')
            ->values();

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

        $fornecedores = Fornecedor::select('id', 'razao_social')->get();

        // Retornar os dados como JSON
        return response()->json([
            'calibres' => $calibres,
            'colaboradores' => $colaboradores,
            'fornecedores' => $fornecedores,
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
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'valor_pago' => 'required|numeric|min:0',
            'peso_bruto' => 'required|numeric|min:0',
            'peso_limpo' => 'required|numeric|min:0',
            'aproveitamento' => 'required|numeric|between:0,100',
            'desperdicio' => 'required|numeric|min:0',
        ]);

        try {
            $service = new SalmaoHistoricoService();
            $result = $service->registrarHistorico($validated, $user);

            return response()->json([
                'message' => 'Registro salvo e estoque atualizado com sucesso!',
                'historico' => $result['historico'],
                'estoque' => $result['estoque'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao salvar: ' . $e->getMessage()], 500);
        }
    }
}
