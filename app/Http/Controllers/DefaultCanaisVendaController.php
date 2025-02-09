<?php

namespace App\Http\Controllers;

use App\Models\DefaultCanaisVenda;
use App\Models\UnidadeCanaisVenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

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


    //  Pagina de administrador
    // Lista todos os métodos de canal de Vendas, independentemente do status
    public function listaIndex()
    {
        // Obtém todos os métodos de canal de Vendas, independentemente do status
        $canalVendas = DefaultCanaisVenda::all();

        // Retorna a resposta com todos os métodos de canal de Vendas
        return response()->json($canalVendas);
    }

    // Adiciona mais um canal de vendas
    public function store(Request $request)
    {
        // Validar os dados da requisição
        $validated = $request->validate([
            'nome' => 'required|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $profilePhotoPath = null;

        // Processar a foto de perfil, se fornecida
        if ($request->hasFile('profile_photo')) {
            // Caminho da pasta personalizada de fotos (armazenamento público)
            $folderPath = public_path('storage/images');

            // Verificar se a pasta existe, caso contrário, criar ela
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            // Salvar a imagem na pasta personalizada
            $profilePhoto = $request->file('profile_photo');
            $fileName = time() . '_' . $profilePhoto->getClientOriginalName();
            $profilePhoto->move($folderPath, $fileName);

            // Garantir que o arquivo tenha as permissões corretas
            chmod($folderPath . '/' . $fileName, 0644);

            // Caminho correto para salvar no banco de dados
            $profilePhotoPath = 'storage/images/' . $fileName;
        }

        // Criar um novo canal de vendas com o caminho da imagem
        $canalVendas = DefaultCanaisVenda::create(array_merge($validated, [
            'img_icon' => $profilePhotoPath,
            'tipo' => 'outros',
        ]));

        // Retornar o canal de vendas criado
        return response()->json($canalVendas);
    }

    // Atualizar um canal de vendas existente
    public function update(Request $request)
    {
        // Validar os dados da requisição
        $validated = $request->validate([
            'nome' => 'required|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Buscar o método de pagamento
        $canalVendas = DefaultCanaisVenda::findOrFail($request->id);

        // Processar a foto de perfil, se fornecida
        if ($request->hasFile('profile_photo')) {
            // Caminho da pasta personalizada de fotos (armazenamento público)
            $folderPath = public_path('storage/images'); // Incluindo o "storage" antes de "images"

            // Verificar se a pasta existe, caso contrário, cria ela
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true); // Cria o diretório se não existir
            }

            // Salvar a imagem na pasta personalizada
            $profilePhoto = $request->file('profile_photo');
            $fileName = time() . '_' . $profilePhoto->getClientOriginalName(); // Nome único para o arquivo
            $profilePhoto->move($folderPath, $fileName);

            // Garantir que o arquivo tenha as permissões corretas
            chmod($folderPath . '/' . $fileName, 0644); // Permissões para leitura e escrita para o proprietário e leitura para outros

            // Caminho correto para salvar no banco de dados
            // O caminho será 'storage/images/nome_da_imagem.jpg'
            $profilePhotoPath = 'storage/images/' . $fileName;
        } else {
            // Se não houver foto, mantém a imagem anterior
            $profilePhotoPath = $canalVendas->img_icon;
        }


        // Atualizar os dados
        $canalVendas->update([
            'nome' => $validated['nome'],
            'tipo' => 'outros',
            'img_icon' => $profilePhotoPath,
        ]);

        // Retornar o método de pagamento atualizado
        return response()->json($canalVendas);
    }

    // Alternar o status do canal de vendas
    public function toggleStatus(Request $request)
    {
        // Validar os dados da requisição
        $validated = $request->validate([
            'canal_de_vendas_id' => 'required|exists:default_payment_methods,id',
        ]);

        // Buscar o método de canal de Vendas pelo ID informado
        $canalVendas = DefaultCanaisVenda::find($validated['canal_de_vendas_id']);

        // Se não existir, retorna erro
        if (!$canalVendas) {
            return response()->json(['error' => 'Canal de Vendas não encontrado.'], 404);
        }

        // Alternar o status
        $canalVendas->status = !$canalVendas->status;
        $canalVendas->save();

        return response()->json([
            'message' => 'Status do Canal de Vendas atualizado com sucesso!',
            'status' => $canalVendas->status
        ]);
    }

    // Deletar canal de vendas
    public function destroy($id)
    {
        try {
            // Buscar e excluir o Canal de vendas
            $canalVendas = DefaultCanaisVenda::findOrFail($id);
            $canalVendas->delete();

            // Retornar resposta de sucesso
            return response()->json([
                'success' => true,
                'message' => 'Canal de vendas excluído com sucesso.',
                'id_excluido' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao excluir o Canal de vendas.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
