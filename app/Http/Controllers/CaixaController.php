<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CanalVenda;
use App\Models\ContaAPagar;
use App\Models\FechamentoCaixa;
use App\Models\FluxoCaixa;
use App\Models\UnidadeCanaisVenda;
use App\Models\UnidadePaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Mail\ComprovanteContaAPagarMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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

        DB::beginTransaction();

        try {
            // Cria um novo registro de caixa
            $caixa = Caixa::create([
                'unidade_id' => $unidadeId,
                'responsavel_id' => $responsavelId,
                'valor_inicial' => $valorInicial,
                'valor_final' => $valorInicial,
                'status' => 1, // Marca o caixa como aberto
                'motivo' => 'Abertura inicial',
            ]);

            // Registro no histórico (fluxo de caixa)
            FluxoCaixa::create([
                'unidade_id' => $unidadeId,
                'responsavel_id' => $responsavelId,
                'caixa_id' => $caixa->id,
                'operacao' => 'abertura',
                'valor' => $valorInicial, // Valor inicial do caixa
                'hora' => now(),
                'motivo' => 'Abertura do caixa',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Caixa aberto com sucesso!',
                'caixa' => $caixa,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao abrir o caixa: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function fecharCaixa(Request $request)
    {
        // Identifica o usuário autenticado
        $usuario = Auth::user();
        $unidade_id = $usuario->unidade_id;

        // Busca o caixa aberto da unidade (com status 1)
        $caixa = Caixa::where('unidade_id', $unidade_id)
            ->where('status', 1)
            ->first();

        if (!$caixa || !$caixa->id) {
            return response()->json(['message' => 'Nenhum caixa aberto encontrado.'], 404);
        }

        // Processa métodos de pagamento
        $metodosLimpos = array_map(function ($metodo) use ($unidade_id) {
            $metodoPagamentoId = $metodo['default_payment_method']['id'] ?? null;
            $valorTotalVendas = (float) str_replace(['R$', '.', ','], ['', '', '.'], $metodo['total_vendas_metodos_pagamento'] ?? 0);

            // Busca a taxa do método de pagamento
            $taxaMetodo = UnidadePaymentMethod::where('unidade_id', $unidade_id)
                ->where('default_payment_method_id', $metodoPagamentoId)
                ->value('porcentagem') ?? 0;

            $valorTaxaMetodo = ($taxaMetodo / 100) * $valorTotalVendas;

            return [
                'metodo_pagamento_id' => $metodoPagamentoId,
                'valor_total_vendas' => $valorTotalVendas,
                'valor_taxa_metodo' => $valorTaxaMetodo,
            ];
        }, $request->metodos);

        // Processa canais de venda
        $canaisLimpos = array_map(function ($canal) use ($unidade_id) {
            $canalVendaId = $canal['default_canal_de_vendas']['id'] ?? null;
            $valorTotalVendas = (float) str_replace(['R$', '.', ','], ['', '', '.'], $canal['total_vendas_canais_vendas'] ?? 0);
            $quantidadeVendasFeitas = (int) ($canal['quantidade_vendas_canais_vendas'] ?? 0);

            // Busca a taxa do canal de venda
            $taxaCanal = UnidadeCanaisVenda::where('unidade_id', $unidade_id)
                ->where('canal_de_vendas_id', $canalVendaId)
                ->value('porcentagem') ?? 0;

            $valorTaxaCanal = ($taxaCanal / 100) * $valorTotalVendas;

            return [
                'canal_de_vendas_id' => $canalVendaId,
                'valor_total_vendas' => $valorTotalVendas,
                'quantidade_vendas_feitas' => $quantidadeVendasFeitas,
                'valor_taxa_canal' => $valorTaxaCanal,
            ];
        }, $request->canais);

        // Calcula o valor final
        $totalMetodosPagamento = array_sum(array_column($metodosLimpos, 'valor_total_vendas'));
        $valorFinal = $totalMetodosPagamento;

        DB::beginTransaction();

        try {
            // Salva os fechamentos de caixa por método de pagamento
            foreach ($metodosLimpos as $metodo) {
                if ($metodo['metodo_pagamento_id']) {
                    FechamentoCaixa::create([
                        'unidade_id' => $unidade_id,
                        'metodo_pagamento_id' => $metodo['metodo_pagamento_id'],
                        'caixa_id' => $caixa->id,
                        'valor_total_vendas' => $metodo['valor_total_vendas'],
                        'valor_taxa_metodo' => $metodo['valor_taxa_metodo'],
                    ]);
                }
            }

            // Salva os registros de canais de venda
            foreach ($canaisLimpos as $canal) {
                if ($canal['canal_de_vendas_id']) {
                    CanalVenda::create([
                        'unidade_id' => $unidade_id,
                        'canal_de_vendas_id' => $canal['canal_de_vendas_id'],
                        'caixa_id' => $caixa->id,
                        'valor_total_vendas' => $canal['valor_total_vendas'],
                        'quantidade_vendas_feitas' => $canal['quantidade_vendas_feitas'],
                        'valor_taxa_canal' => $canal['valor_taxa_canal'],
                    ]);
                }
            }

            // Registro no histórico (fechamento)
            FluxoCaixa::create([
                'unidade_id' => $unidade_id,
                'responsavel_id' => $usuario->id,
                'caixa_id' => $caixa->id,
                'operacao' => 'fechamento',
                'valor' => $valorFinal,
                'hora' => now(),
                'motivo' => $request->motivo ?? 'Fechamento de caixa',
            ]);

            // Atualiza o valor final e o status do caixa
            $caixa->valor_final = $valorFinal;
            $caixa->status = 0;
            $caixa->motivo = $request->motivo ?? 'Fechamento de caixa';
            $caixa->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Caixa fechado com sucesso!',
                'caixa' => $caixa,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
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
        $canaisVendas = UnidadeCanaisVenda::where('unidade_id', $unidadeId)
            ->where('status', 1) // Apenas canais ativos
            ->with('defaultCanalDeVendas') // Carrega os detalhes do canal de vendas padrão
            ->get();

        return response()->json([
            'status' => 'success',
            'metodosPagamento' => $metodosPagamento,
            'canaisVendas' => $canaisVendas,
        ], 200);
    }

    // Adiciona Suprimentos ao caixa
    public function adicionarSuprimento(Request $request)
    {
        // Validação dos dados de entrada
        $request->validate([
            'valor' => 'required|numeric|min:0.01',  // Valor deve ser numérico e maior que 0
            'motivo' => 'required|string|max:255',   // Motivo deve ser uma string com até 255 caracteres
        ]);

        try {
            // Obtém o ID da unidade do usuário autenticado
            $unidadeId = Auth::user()->unidade_id;

            // Obtém o primeiro caixa aberto da unidade do usuário logado
            $caixa = Caixa::where('unidade_id', $unidadeId)
                ->where('status', 1) // Verifica se o status é 1 (aberto)
                ->first(); // Obtém apenas um registro

            // Verifica se o caixa está aberto
            if (!$caixa) {
                return response()->json(['error' => 'Nenhum caixa aberto encontrado.'], 404);
            }

            $responsavelId = Auth::user() ? Auth::user()->id : null;

            if (!$responsavelId) {
                return response()->json(['error' => 'Responsável não encontrado ou usuário não autenticado.'], 400);
            }

            // Inicia a transação
            DB::transaction(function () use ($caixa, $responsavelId, $request) {
                // Atualiza o valor inicial do caixa
                $caixa->valor_inicial += $request->valor;
                $caixa->save(); // Salva a atualização do valor inicial

                // Criação direta do fluxo de caixa sem utilizar o relacionamento
                FluxoCaixa::create([
                    'unidade_id' => $caixa->unidade_id,
                    'responsavel_id' => $responsavelId,  // Passando o ID do responsável corretamente
                    'caixa_id' => $caixa->id,
                    'operacao' => 'suprimento',
                    'valor' => $request->valor,
                    'hora' => now(),
                    'motivo' => $request->motivo, // Motivo passado no request
                ]);
            });

            // Caso a transação seja bem-sucedida, retorna uma mensagem de sucesso
            return response()->json(['success' => 'Valor adicionado com sucesso.']);
        } catch (\Exception $e) {
            // Caso ocorra algum erro durante a transação, será revertida
            return response()->json(['error' => 'Erro ao processar a transação: ' . $e->getMessage()], 500);
        }
    }

    public function removeSuprimento(Request $request)
    {
        // Validação dos dados de entrada
        $request->validate([
            'valor' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        try {
            // Obtém o ID da unidade do usuário autenticado
            $unidadeId = Auth::user()->unidade_id;

            // Obtém o primeiro caixa aberto da unidade do usuário logado
            $caixa = Caixa::where('unidade_id', $unidadeId)
                ->where('status', 1)
                ->first();

            if (!$caixa) {
                return response()->json(['error' => 'Nenhum caixa aberto encontrado.'], 404);
            }

            if ($request->valor > $caixa->valor_inicial) {
                return response()->json(['error' => 'Valor de retirada superior ao disponível no caixa.'], 400);
            }

            $responsavelId = Auth::user() ? Auth::user()->id : null;

            if (!$responsavelId) {
                return response()->json(['error' => 'Responsável não encontrado ou usuário não autenticado.'], 400);
            }

            // Verificar se já existe uma conta a pagar com mesmo valor, categoria e data
            $dataAtual = Carbon::today();
            $contaExistente = ContaAPagar::where('categoria_id', $request->categoria_id)
                ->where('valor', $request->valor)
                ->whereDate('emitida_em', $dataAtual)
                ->where('status', 'pago')
                ->first();

            if ($contaExistente) {
                // Retornar uma mensagem de confirmação com detalhes da transação existente
                return response()->json([
                    'confirmation_required' => true,
                    'message' => 'Já existe uma transação com o mesmo valor e categoria hoje. Deseja prosseguir?',
                    'existing_transaction' => [
                        'id' => $contaExistente->id,
                        'valor' => $contaExistente->valor,
                        'categoria' => $contaExistente->nome,
                        'motivo' => $contaExistente->descricao,

                    ]
                ], 200);
            }

            // Se não houver duplicata, prosseguir com a transação
            DB::transaction(function () use ($caixa, $responsavelId, $request) {
                // Atualiza o valor inicial do caixa
                $caixa->valor_inicial -= $request->valor;
                $caixa->save();

                // Criação do fluxo de caixa
                FluxoCaixa::create([
                    'unidade_id' => $caixa->unidade_id,
                    'responsavel_id' => $responsavelId,
                    'caixa_id' => $caixa->id,
                    'operacao' => 'sangria',
                    'valor' => $request->valor,
                    'hora' => now(),
                    'motivo' => $request->motivo,
                ]);

                // Obter o nome da categoria selecionada
                $categoria = DB::table('categorias')->where('id', $request->categoria_id)->first();
                $nomeCategoria = $categoria->nome;

                // Criação da conta a pagar com status "pago"
                $conta = ContaAPagar::create([
                    'nome' => $nomeCategoria,
                    'valor' => $request->valor,
                    'emitida_em' => Carbon::today(),
                    'vencimento' => Carbon::today(),
                    'descricao' => $request->motivo,
                    'arquivo' => null,
                    'dias_lembrete' => 0,
                    'status' => 'pago',
                    'unidade_id' => $caixa->unidade_id,
                    'categoria_id' => $request->categoria_id,
                ]);

                $nomeUsuario = Auth::user()->name;
                $conta->registrarLog('criacao_via_sangria', 'pago', null, $nomeUsuario);

                // Notificações por E-mail
                try {
                    $nomeUnidade = Auth::user()->unidade ? Auth::user()->unidade->cidade . ' - ' . Auth::user()->unidade->bairro : 'Unidade ' . $caixa->unidade_id;

                    $destinatarios = [Auth::user()->email];

                    // Usuarios da Franqueadora da mesma Unidade
                    $usuariosFranqueadora = User::where('unidade_id', $caixa->unidade_id)
                        ->where('franqueadora', 1)
                        ->pluck('email')
                        ->toArray();

                    $destinatarios = array_unique(array_merge($destinatarios, $usuariosFranqueadora));

                    foreach ($destinatarios as $email) {
                        Mail::to($email)->send(new ComprovanteContaAPagarMail($conta, Auth::user(), $nomeUnidade));
                    }
                } catch (\Exception $mailEx) {
                    Log::error('Erro ao enviar e-mail de comprovante (Sangria): ' . $mailEx->getMessage());
                }
            });

            return response()->json(['success' => 'Valor retirado e conta registrada como paga com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao processar a transação: ' . $e->getMessage()], 500);
        }
    }
    // Retorna o valor disponível no caixa aberto
    public function valorDisponivel()
    {
        try {
            $unidadeId = Auth::user()->unidade_id;

            // Obtém o caixa aberto da unidade do usuário logado
            $caixa = Caixa::where('unidade_id', $unidadeId)
                ->where('status', 1) // Caixa aberto
                ->first();

            if (!$caixa) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nenhum caixa aberto encontrado.'
                ], 404);
            }

            $valorFormatado = 'R$ ' . number_format($caixa->valor_inicial, 2, ',', '.');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'valor_disponivel' => $valorFormatado
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao obter o valor do caixa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCaixas(Request $request)
    {
        // Obtém o ID da unidade do usuário autenticado
        $unidadeId = Auth::user()->unidade_id;

        if (!$unidadeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário não pertence a nenhuma unidade.',
            ], 403);
        }

        try {
            // Define o início e fim do mês corrente como padrão
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('d-m-Y'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('d-m-Y'));

            // Converte para Carbon e garante que as datas incluam todo o período do dia
            $startDateConverted = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay(); // 00:00:00
            $endDateConverted = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay(); // 23:59:59
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de data inválido. Use o formato DD-MM-YYYY.'], 400);
        }

        // Busca os caixas do mês corrente, filtrados por unidade_id e ordenados do mais recente para o mais antigo
        $caixas = Caixa::where('unidade_id', $unidadeId)
            ->whereBetween('created_at', [$startDateConverted, $endDateConverted]) // Filtra pelo mês corrente
            ->with('responsavel') // Carrega o relacionamento com o usuário responsável
            ->orderBy('id', 'desc') // Ordena pelo ID de forma decrescente (mais recente primeiro)
            ->get();

        // Mapeia os dados para o formato desejado
        $response = $caixas->map(function ($caixa) {
            // Formata o valor total de fechamento como Real Brasileiro
            $valorFormatado = number_format($caixa->valor_final ?? 0, 2, ',', '.');

            // Define o texto do status com base no valor numérico
            $statusTexto = $caixa->status == 0 ? 'Fechado' : ($caixa->status == 1 ? 'Aberto' : $caixa->status);

            // Calcula a diferença em segundos entre abertura e fechamento
            $horaAbertura = Carbon::parse($caixa->created_at);
            $horaFechamento = Carbon::parse($caixa->updated_at);
            $diferencaSegundos = $horaAbertura->diffInSeconds($horaFechamento);

            // Converte segundos totais em horas, minutos e segundos
            $horas = floor($diferencaSegundos / 3600); // Horas inteiras
            $minutos = floor(($diferencaSegundos % 3600) / 60); // Minutos restantes
            $segundos = $diferencaSegundos % 60; // Segundos restantes

            // Define o formato e sufixo com base no maior componente de tempo
            if ($horas > 0) {
                $horasFormatadas = sprintf('%02d:%02d:%02dh', $horas, $minutos, $segundos);
            } elseif ($minutos > 0) {
                $horasFormatadas = sprintf('00:%02d:%02dm', $minutos, $segundos);
            } else {
                $horasFormatadas = sprintf('00:00:%02ds', $segundos);
            }

            return [
                'id' => $caixa->id,
                'status' => $statusTexto,
                'valor_total_fechamento' => "R$ {$valorFormatado}",
                'hora_abertura' => $caixa->created_at->toDateTimeString(),
                'hora_fechamento' => $caixa->updated_at->toDateTimeString(),
                'usuario_fechou' => $caixa->responsavel ? $caixa->responsavel->name : 'N/A',
                'horas_aberto' => $horasFormatadas,
            ];
        });

        // Retorna os dados em formato JSON
        return response()->json([
            'success' => true,
            'data' => $response,
        ], 200);
    }
}
