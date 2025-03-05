<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use App\Models\Cargo;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function index()
    {
        // Carrega usuários junto com as informações das unidades (empresas)
        $users = User::with('unidade')->get();

        // Formata a resposta para incluir as unidades relacionadas e as permissões
        $data = $users->map(function ($user) {
            // Obtém as permissões do usuário atual e converte 0/1 para booleanos
            $permissions = array_map('boolval', $user->getPermissions());

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'cpf' => $user->cpf,
                'unidade_id' => $user->unidade_id,
                'cargo_id' => $user->cargo_id,
                'pin' => $user->pin,
                'profile_photo_url' => $user->profile_photo_url,
                'colaborador' => $user->colaborador,
                'permissions' => $permissions, // Adiciona as permissões convertidas
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
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // Validação da imagem
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
                'colaborador' => false,
                'franqueado' => true,
                'franqueadora' => false,
                'pin' => $pin, // Armazenar o PIN gerado
                'profile_photo_path' => $profilePhotoPath, // Caminho da foto
            ]);

            // Criar permissões padrão para o novo usuário
            UserPermission::create([
                'user_id' => $user->id,
                'controle_estoque' => true,
                'controle_saida_estoque' => true,
                'gestao_equipe' => true,
                'fluxo_caixa' => true,
                'dre' => true,
                'contas_pagar' => true,
                'gestao_salmao' => true,
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


    // Delete user
    public function destroy($id)
    {
        DB::beginTransaction(); // Inicia a transação

        try {
            // Obter o usuário autenticado usando Auth
            $authenticatedUser = Auth::user();

            // Encontrar o usuário pelo ID
            $user = User::findOrFail($id);

            // Verificar se o usuário autenticado está tentando excluir a si mesmo
            if ($authenticatedUser->id === $user->id) {
                return response()->json(['error' => 'Você não pode excluir a si mesmo.'], 403);
            }

            // Verificar se existe uma imagem de perfil associada e deletá-la
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Excluir todas as permissões associadas ao usuário
            UserPermission::where('user_id', $user->id)->delete();

            // Excluir o usuário
            $user->delete();

            DB::commit(); // Confirma a transação

            return response()->json(['message' => 'Usuário deletado com sucesso.'], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Reverte a transação em caso de erro

            return response()->json(['error' => 'Erro ao deletar o usuário.', 'details' => $e->getMessage()], 500);
        }
    }

    // Lista os colaboradores
    public function listColaboradores()
    {
        $user = Auth::user(); // Usuário autenticado

        // Verifica se o usuário está autenticado
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado.'], 401);
        }

        // Buscar todos os usuários da mesma unidade_id, excluir o próprio usuário e incluir o cargo e permissões
        $colaboradores = User::where('unidade_id', $user->unidade_id)
            ->with(['userPermission', 'cargo']) // Carrega as permissões e cargos dos usuários
            ->get();

        // Retornar a lista de colaboradores com cargos e permissões em formato JSON
        return response()->json($colaboradores);
    }


    // Atualiza as permissões de um usuário específico
    public function upsertPermissions(Request $request)
    {
        // Validar os dados da requisição
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // Garante que o usuário existe
            'controle_estoque' => 'required|boolean',
            'controle_saida_estoque' => 'required|boolean',
            'gestao_equipe' => 'required|boolean',
            'fluxo_caixa' => 'required|boolean',
            'dre' => 'required|boolean',
            'contas_pagar' => 'required|boolean',
            'gestao_salmao' => 'required|boolean',
        ]);

        // Buscar o usuário a ser atualizado
        $user = User::findOrFail($validated['user_id']);

        // Verificar se já existem permissões registradas para este usuário
        $userPermission = UserPermission::firstOrCreate(
            ['user_id' => $user->id], // Garante que está alterando o usuário correto
            $validated // Se não existir, cria com os dados validados
        );

        // Se já existir, apenas atualiza
        if (!$userPermission->wasRecentlyCreated) {
            $userPermission->update($validated);
        }

        return response()->json($userPermission);
    }

    public function listarCargos()
    {
        // Busca todos os cargos no banco de dados
        $cargos = Cargo::all();

        // Retorna os cargos em formato JSON
        return response()->json($cargos);
    }

    /**
     * Gerar um PIN único de 4 números.
     */
    private function generateUniquePin()
    {
        do {
            $pin = rand(1000, 9999); // Gera um PIN aleatório de 4 números
        } while (User::where('pin', $pin)->exists()); // Verifica se o PIN já existe

        return $pin;
    }

    /**
     * Atualiza o PIN de um usuário específico.
     */
    public function atualizarPin(Request $request)
    {
        try {
            // Encontrar o usuário
            $user = User::findOrFail($request->input('user_id'));

            // Gerar novo PIN único
            $novoPin = $this->generateUniquePin();

            // Atualizar o PIN
            $user->update(['pin' => $novoPin]);

            return response()->json([
                'message' => 'PIN atualizado com sucesso',
                'novo_pin' => $novoPin
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Usuário não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao atualizar o PIN',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adiciona um novo Colaborador.
     */
    public function novoColaborador(Request $request)
    {
        // dd($request->all());

        // Validação dos dados
        $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|unique:users,cpf',
            'salario' => 'required|numeric',
            'cargo_id' => 'required|exists:cargos,id',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Gerar PIN único de 5 números
        $pin = $this->generateUniquePin();

        // Senha padrão
        $password = 'taiksu-123456';
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

        // Criar o colaborador
        try {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($password), // Hash da senha padrão
                'cpf' => $request->input('cpf'),
                'salario' => $request->input('salario'), // Salvar o salário
                'cargo_id' => $request->input('cargo_id'), // Associar o cargo
                'unidade_id' => Auth::user()->unidade_id, // Unidade do usuário autenticado
                'colaborador' => true,
                'franqueado' => true,
                'franqueadora' => false,
                'pin' => $pin, // Armazenar o PIN gerado
                'profile_photo_path' => $profilePhotoPath, // Caminho da foto
            ]);

            // Criar permissões padrão para o novo usuário
            UserPermission::create([
                'user_id' => $user->id,
                'controle_estoque' => false,
                'controle_saida_estoque' => false,
                'gestao_equipe' => false,
                'fluxo_caixa' => false,
                'dre' => false,
                'contas_pagar' => false,
                'gestao_salmao' => false,
            ]);

            // Gerar o token de redefinição de senha
            $token = Password::createToken($user);

            // Enviar o e-mail com o link para redefinir a senha
            Mail::to($user->email)->send(new ResetPasswordMail($user, $token));

            return response()->json([
                'message' => 'Usuário criado com sucesso. Um e-mail para redefinição de senha foi enviado.',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao criar o usuário.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // Atualiza um colaborador
    public function atualizarColaborador(Request $request)
    {
        try {
            $user = User::findOrFail($request->input('user_id'));
            $userId = $user->id;


            // Validação
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $userId,
                'cpf' => 'required|string|unique:users,cpf,' . $userId,
                'salario' => 'required|numeric',
                'cargo_id' => 'required|exists:cargos,id',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

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

            // Atualizar os dados
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->cpf = $request->input('cpf');
            $user->salario = $request->input('salario');
            $user->cargo_id = $request->input('cargo_id');
            $user->profile_photo_path = $profilePhotoPath ?? $user->profile_photo_path; // Manter a foto antiga se não for fornecida


            $user->save();

            return response()->json([
                'message' => 'Usuário atualizado com sucesso',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao atualizar usuário',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // atualiza senha de usuario
        public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Senha atualizada com sucesso!');
    }
}
