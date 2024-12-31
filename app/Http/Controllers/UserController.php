<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    // Método que retorna todos os usuários e as empresas relacionadas
    public function index()
    {
        // Carrega usuários junto com as informações das unidades (empresas)
        $users = User::with('unidade')->get();

        // Formata a resposta para incluir as unidades relacionadas
        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'cpf' => $user->cpf,
                'unidade_id' => $user->unidade_id,
                'cargo_id' => $user->cargo_id,
                'pin' => $user->pin,
                'profile_photo_url' => $user->profile_photo_url,
                'unidade' => $user->unidade ? [
                    'id' => $user->unidade->id,
                    'cep' => $user->unidade->cep,
                    'rua' => $user->unidade->rua,
                    'numero' => $user->unidade->numero,
                    'cidade' => $user->unidade->cidade,
                    'bairro' => $user->unidade->bairro,
                    'cnpj' => $user->unidade->cnpj,
                ] : null,
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {

        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|string|size:14|unique:users,cpf',
            'unidade_id' => 'required|exists:infor_unidade,id',
            'cargo_id' => 'nullable|exists:cargos,id',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validação da imagem
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Gerar PIN único de 5 números
        $pin = $this->generateUniquePin();

        // Senha padrão
        $password = 'taiksu-123456';
        $profilePhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            // Caminho da pasta personalizada de fotos (armazenamento público)
            $folderPath = public_path('storage/images'); // A pasta dentro de "public/storage/images"

            // Verificar se a pasta existe, caso contrário, cria ela
            if (!File::exists($folderPath)) {
                // Criar a pasta com permissões adequadas
                File::makeDirectory($folderPath, 0755, true);  // Permissões para leitura, escrita e execução
            }

            // Agora podemos salvar a imagem na pasta personalizada
            $profilePhoto = $request->file('profile_photo');
            $fileName = time() . '_' . $profilePhoto->getClientOriginalName(); // Definir um nome único para o arquivo

            // Mover a imagem para a pasta 'public/storage/images'
            $profilePhoto->move($folderPath, $fileName);

            // Garantir que o arquivo tenha as permissões corretas
            chmod($folderPath . '/' . $fileName, 0644);  // Permissões para leitura e escrita para o proprietário e leitura para outros

            // Caminho correto para salvar no banco de dados
            $profilePhotoPath = 'images/' . $fileName;  // Corrigido para 'images/', sem duplicação de 'storage'
        }



        // Criação do usuário
        try {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($password), // Hash da senha padrão
                'cpf' => $request->input('cpf'),
                'unidade_id' => $request->input('unidade_id'),
                'cargo_id' => $request->input('cargo_id'),
                'pin' => $pin, // Armazenar o PIN gerado
                'profile_photo_path' => $profilePhotoPath, // Caminho da foto
            ]);

            // Gerar o token de redefinição de senha
            $token = Password::createToken($user);

            // Enviar o e-mail com o link para redefinir a senha
            Mail::to($user->email)->send(new ResetPasswordMail($user, $token));

            return response()->json(['message' => 'Usuário criado com sucesso. Um e-mail para redefinição de senha foi enviado.', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao criar o usuário.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Gerar um PIN único de 5 números.
     */
    private function generateUniquePin()
    {
        do {
            $pin = rand(10000, 99999); // Gera um PIN aleatório de 5 números
        } while (User::where('pin', $pin)->exists()); // Verifica se o PIN já existe

        return $pin;
    }

    public function destroy($id)
    {
        try {
            // Obter o usuário autenticado usando Auth
            $authenticatedUser = Auth::user();

            // Encontrar o usuário pelo ID
            $user = User::findOrFail($id); // Retorna 404 se não encontrar o usuário

            // Verificar se o usuário autenticado está tentando excluir a si mesmo
            if ($authenticatedUser->id === $user->id) {
                return response()->json(['error' => 'Você não pode excluir a si mesmo.'], 403);
            }

            // Verificar se existe uma imagem de perfil associada e deletá-la
            if ($user->profile_photo_path) {
                // Remove a imagem da pasta storage, caso exista
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Excluir o usuário
            $user->delete();

            return response()->json(['message' => 'Usuário deletado com sucesso.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao deletar o usuário.', 'details' => $e->getMessage()], 500);
        }
    }




}
