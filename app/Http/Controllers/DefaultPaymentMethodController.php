<?php

namespace App\Http\Controllers;

use App\Models\DefaultPaymentMethod;
use App\Models\UnidadePaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;


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

    // Lista todos os métodos de pagamento, independentemente do status
    public function listaIndex()
    {
        // Obtém todos os métodos de pagamento, independentemente do status
        $paymentMethods = DefaultPaymentMethod::all();

        // Retorna a resposta com todos os métodos de pagamento
        return response()->json($paymentMethods);
    }

    // Alternar o status do método de pagamento
    public function toggleStatus(Request $request)
    {
        // Validar os dados da requisição
        $validated = $request->validate([
            'default_payment_method_id' => 'required|exists:default_payment_methods,id',
        ]);

        // Buscar o método de pagamento pelo ID informado
        $paymentMethod = DefaultPaymentMethod::find($validated['default_payment_method_id']);

        // Se não existir, retorna erro
        if (!$paymentMethod) {
            return response()->json(['error' => 'Método de pagamento não encontrado.'], 404);
        }

        // Alternar o status
        $paymentMethod->status = !$paymentMethod->status;
        $paymentMethod->save();

        return response()->json([
            'message' => 'Status do método de pagamento atualizado com sucesso!',
            'status' => $paymentMethod->status
        ]);
    }


    // Criar novo metodo de pagamento
    public function store(Request $request)
    {
        // Validar os dados da requisição
        $validated = $request->validate([
            'name' => 'required|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tipo' => 'required|string',

        ]);

        $profilePhotoPath = null;

        // Processar a foto de perfil, se fornecida
        if ($request->hasFile('profile_photo')) {
            // Caminho da pasta personalizada de fotos (armazenamento público)
            $folderPath = public_path('storage/images'); // A pasta dentro de "public/storage/images"

            // Verificar se a pasta existe, caso contrário, cria ela
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true); // Permissões para leitura, escrita e execução
            }

            // Salvar a imagem na pasta personalizada
            $profilePhoto = $request->file('profile_photo');
            $fileName = time() . '_' . $profilePhoto->getClientOriginalName(); // Nome único para o arquivo
            $profilePhoto->move($folderPath, $fileName);

            // Garantir que o arquivo tenha as permissões corretas
            chmod($folderPath . '/' . $fileName, 0644); // Permissões para leitura e escrita para o proprietário e leitura para outros

            // Caminho correto para salvar no banco de dados
            $profilePhotoPath = 'images/' . $fileName; // Corrigido para 'images/', sem duplicação de 'storage'
        }

        // Criar um novo método de pagamento
        $paymentMethod = DefaultPaymentMethod::create($validated);

        // Retornar o método de pagamento criado
        return response()->json($paymentMethod);
    }

    // editar metodo de pagamento
    public function update(Request $request)
    {
        // Validar os dados da requisição
        $validated = $request->validate([
            'nome' => 'required|string',
            'tipo' => 'required|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Buscar o método de pagamento
        $paymentMethod = DefaultPaymentMethod::findOrFail($request->id);

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
            $profilePhotoPath = $paymentMethod->img_icon;
        }


        // Atualizar os dados
        $paymentMethod->update([
            'nome' => $validated['nome'],
            'tipo' => $validated['tipo'],
            'img_icon' => $profilePhotoPath,
        ]);

        // Retornar o método de pagamento atualizado
        return response()->json($paymentMethod);
    }


    // Excluir metodo de pagamento
    public function destroy($id)
    {
        // Buscar o método de pagamento pelo ID
        $paymentMethod = DefaultPaymentMethod::find($id);

        // Verificar se o método de pagamento foi encontrado
        if (!$paymentMethod) {
            return response()->json(['error' => 'Método de pagamento não encontrado.'], 404);
        }

        // Excluir o método de pagamento
        $paymentMethod->delete();

        // Retornar a resposta de sucesso
        return response()->json(['success' => true]);
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
