<?php

namespace App\Http\Controllers;

use App\Models\HistoricoPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HistoricoPedidoController extends Controller
{
    // Função para exibir todos os pedidos históricos
    public function index(Request $request)
    {
        // Verificar se foi fornecido um filtro de unidade
        $unidadeId = $request->query('unidade_id');

        // Inicia a consulta ao modelo, incluindo os relacionamentos necessários
        $query = HistoricoPedido::with(['usuario', 'unidade', 'fornecedor']);

        // Aplica o filtro por unidade, se especificado
        if ($unidadeId) {
            $query->where('unidade_id', $unidadeId);
        }

        // Ordena os pedidos em ordem decrescente (por exemplo, pelo created_at)
        $query->orderBy('created_at', 'desc');

        // Obtem os resultados
        $pedidos = $query->get();

        // Processa os dados antes de retorná-los
        $pedidos->transform(function ($pedido) {
            // Substituindo os IDs pelos nomes dos relacionamentos
            $pedido->usuario_responsavel = $pedido->usuario ? $pedido->usuario->name : null;
            $pedido->unidade_nome = $pedido->unidade ? $pedido->unidade->nome : null;
            $pedido->fornecedor_nome = $pedido->fornecedor ? $pedido->fornecedor->razao_social : null;

            // Verificando se os campos são strings antes de fazer o json_decode
            if (is_string($pedido->itens_id)) {
                $pedido->itens_id = json_decode($pedido->itens_id, true);
            }

            // Criando uma nova variável para armazenar os itens processados
            $itensProcessados = [];
            foreach ($pedido->itens_id as $item) {
                // Adicionando o nome do fornecedor a cada item
                $item['nome_fornecedor'] = $pedido->fornecedor_nome;
                $itensProcessados[] = $item; // Adicionando o item processado à lista
            }

            // Atribuindo os itens processados de volta ao pedido
            $pedido->itens_id = $itensProcessados;

            // Verificando se o campo valor_total_item é uma string e decodificando
            if (is_string($pedido->valor_total_item)) {
                $pedido->valor_total_item = json_decode($pedido->valor_total_item, true);
            }

            // Formatando as datas no formato 'd-m-Y'
            if ($pedido->created_at) {
                $pedido->created_at = Carbon::parse($pedido->created_at)->format('d-m-Y');
            }

            if ($pedido->updated_at) {
                $pedido->updated_at = Carbon::parse($pedido->updated_at)->format('d-m-Y');
            }

            // Agrupando informações de forma organizada
            $pedido->usuario_responsavel = [
                'id' => $pedido->usuario ? $pedido->usuario->id : null,
                'nome' => $pedido->usuario_responsavel,
            ];
            $pedido->unidade = [
                'id' => $pedido->unidade ? $pedido->unidade->id : null,
                'nome' => $pedido->unidade_nome,
            ];
            $pedido->fornecedor = [
                'id' => $pedido->fornecedor ? $pedido->fornecedor->id : null,
                'nome' => $pedido->fornecedor_nome,
            ];

            return $pedido;
        });

        // Retornando a resposta com os dados processados
        return response()->json($pedidos);
    }
}
