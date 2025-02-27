<template>
  <div class="elemento-fixo">
    <!-- Tabela de Lotes -->
    <div v-if="!isEditMode">
      <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
      </div>
      <div>
        <div
          class="w-full h-[300px] bg-white rounded-tl-[20px] rounded-tr-[20px] p-7 relative"
        >
          <div class="flex justify-between items-center">
            <div class="w-1/1 flex justify-center">
              <img
                :src="
                  dados.profile_photo_url ||
                  '/storage/images/default-profile.png'
                "
                alt="Foto do Usuário"
                class="w-20 h-20 rounded-md shadow-sm"
              />
            </div>

            <!-- Coluna do Nome e CPF (Agora com Select) -->
            <div class="w-2/3">
              <div
                class="text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[30px] tracking-tight"
              >
                {{ dados.name || 'N/A' }}
              </div>

              <!-- Seletor desabilitado para exibição do nome do cargo -->
              <!-- Div com o nome do cargo, sem a seta do seletor -->
              <div
                class="w-72 h-[44px] bg-[#F3F8F3] border-gray-100 rounded-lg border-2 border-[#d7d7db] p-2 text-base text-[#6DB631] font-bold focus:ring-2 focus:ring-green-500 mt-2 flex items-center"
              >
                <span>
                  {{ dados.cargo?.nome || 'Cargo não atribuído' }}
                </span>
              </div>
            </div>
            <button
              class="text-gray-500 hover:text-red-600"
              @click="isConfirmExluirDialogVisible('Excluir essa conta?')"
            >
              <img
                src="/storage/images/delete.svg"
                alt="Excluir"
                class="w-6 h-6"
              />
            </button>
          </div>

          <div class="grid grid-cols-2 gap-4 mt-8">
            <!-- E-mail -->
            <div>
              <LabelModel text="E-mail" />
              <p
                class="w-full h-[44px] bg-white border-gray-300 rounded-lg border-2 border-[#d7d7db] p-2 text-base font-bold text-gray-800 focus:ring-2 focus:ring-green-500"
              >
                {{ dados.email }}
              </p>
            </div>

            <!-- Salário -->
            <div>
              <LabelModel text="Salário" />
              <p
                class="w-full h-[44px] bg-white border-gray-300 rounded-lg border-2 border-[#d7d7db] p-2 text-base font-bold text-gray-800 focus:ring-2 focus:ring-green-500"
              >
                {{ salarioFormatado }}
              </p>
            </div>
          </div>
          <div class="flex justify-between items-center mt-5 w-full p-1">
            <!-- Coluna 1 -->
            <div class="flex items-center w-2/3">
              <img
                class="w-5 h-5 mr-2"
                src="/storage/images/security_bleck.svg"
                alt="icone svg"
              />
              <LabelModel
                class="font-semibold text-gray-900"
                text="Permissões"
              />
            </div>
            <!-- Coluna 2 -->
            <div class="w-1/2 flex justify-end">
              <p
                class="font-bold text-[#6DB631] cursor-pointer"
                @click="alternarVisao"
              >
                <span class="underline">***</span>
                PIN
              </p>
            </div>
          </div>
        </div>
        <!-- Tela das permissões -->
        <div
          v-if="mostrarPermissoes"
          class="bg-[#EFEFEF] p-3 px-8 w-full rounded-bl-[20px] rounded-br-[20px]"
        >
          <div
            v-for="chave in Object.keys(dados.user_permission || {}).filter(
              (k) => !ocultarCampos.includes(k)
            )"
            :key="chave"
            class="flex justify-between items-center"
          >
            <!-- Coluna 1 -->
            <div class="flex flex-col w-2/3">
              <LabelModel
                class="font-semibold text-gray-900"
                :text="formatarPermissao(chave)"
              />
            </div>
            <!-- Coluna 2 -->
            <div class="w-1/2 flex justify-end">
              <Deslizante
                v-model:status="dados.user_permission[chave]"
                @click="atualizarMetodo(chave, dados.user_permission[chave])"
              />
            </div>
          </div>
        </div>
        <!-- Exibir PIN no centro -->
        <div
          v-else
          class="flex flex-col h-[270px] items-center justify-center bg-[#EFEFEF] rounded-lg"
        >
          <p class="text-[100px] font-bold text-[#6DB631]">
            {{ props.dados.pin }}
          </p>
          <button
            @click="isConfirmAtualizarPinDialogVisible('Redefinir PIN?')"
            class="text-[#FF2D55] font-semibold rounded-lg underline"
          >
            Redefinir PIN
          </button>
        </div>
      </div>
    </div>
  </div>
  <div v-if="dados.id && !isEditMode" class="mt-4">
    <ButtonEditeMedio
      text="Editar Colaborador"
      icon-path="/storage/images/border_color.svg"
      @click="toggleEditMode"
      class="px-4 py-2 bg-[#F8F8F8] text-white rounded-lg"
    />
  </div>
  <!-- Componete de editar colaborador! -->
  <EditarColaborador
    v-if="isEditMode"
    :user="dados"
    :isVisible="isEditMode"
    @voltar="cancelEdit"
    @atualizado="handleAtualizacao"
  />

  <ConfirmDialog
    :isVisible="isConfirmExlusaoDialogVisible"
    :motivo="motivo"
    @confirm="handleConfirmExlucao"
    @cancel="handleCancelExlusao"
  />

  <ConfirmDialog
    :isVisible="isConfirmAtualizarPinDialogVisibl"
    :motivo="motivo"
    @confirm="atualizarPin"
    @cancel="handleCancelar"
  />
</template>

<script setup>
import { defineProps, ref } from 'vue';
import { defineEmits } from 'vue';
import LabelModel from '../Label/LabelModel.vue';
import { useToast } from 'vue-toastification';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';
import Deslizante from '../Button/Deslizante.vue';
import { computed } from 'vue';
import ButtonEditeMedio from '../Button/ButtonEditeMedio.vue';
import EditarColaborador from './EditarColaborador.vue';

const toast = useToast();

const props = defineProps({
  dados: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['voltar', 'atualiza']);

const isEditMode = ref(false);
const isConfirmExlusaoDialogVisible = ref(false);
const isConfirmAtualizarPinDialogVisibl = ref(false);

const isLoading = ref(false);
const showCadastroProduto = ref(false);

const motivo = ref('');

const mostrarPermissoes = ref(true); // Inicia exibindo permissões

// Lista de campos que devem ser ocultados
const ocultarCampos = ['id', 'user_id', 'created_at', 'updated_at'];

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value;
  showCadastroProduto.value = false;
};

const cancelEdit = () => {
  emit('atualizar');
  isEditMode.value = false;
  showCadastroProduto.value = true;
};

// Alterna entre exibir permissões e exibir PIN
const alternarVisao = () => {
  mostrarPermissoes.value = !mostrarPermissoes.value;
};

// Mapeia os nomes das permissões para exibição mais amigável
const formatarPermissao = (chave) => {
  const mapeamento = {
    controle_estoque: 'Gestão de Estoque',
    controle_saida_estoque: 'Saída de Estoque',
    gestao_equipe: 'Gestão de Equipe',
    fluxo_caixa: 'Fluxo de Caixa',
    dre: 'DRE',
    contas_pagar: 'Contas a Pagar',
    gestao_salmao: 'Gestão de Salmão',
  };

  return mapeamento[chave] || chave;
};

const salarioFormatado = computed(() => {
  if (!props.dados.salario) return 'R$ 0,00'; // Caso esteja vazio ou undefined

  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(props.dados.salario);
});

// Atualiza a permissão no banco de dados
const atualizarMetodo = async (chave, valor) => {
  // Garantir que dados.user_permission seja um objeto
  if (!props.dados.user_permission) {
    props.dados.user_permission = {};
  }

  // Certifique-se de que o valor seja um booleano
  props.dados.user_permission[chave] = Boolean(valor);

  console.log(`Alterando ${chave} para`, props.dados.user_permission[chave]);

  // Monta o objeto com as permissões alteradas e adiciona o user_id correto
  const permissaoAtualizada = {
    user_id: props.dados.id, // Usar o ID do usuário diretamente
    controle_estoque: props.dados.user_permission.controle_estoque || false,
    controle_saida_estoque: props.dados.user_permission.controle_saida_estoque || false,
    gestao_equipe: props.dados.user_permission.gestao_equipe || false,
    fluxo_caixa: props.dados.user_permission.fluxo_caixa || false,
    dre: props.dados.user_permission.dre || false,
    contas_pagar: props.dados.user_permission.contas_pagar || false,
    gestao_salmao: props.dados.user_permission.gestao_salmao || false,
  };

  try {
    isLoading.value = true;

    // Envia a requisição para atualizar as permissões
    const response = await axios.post(
      '/api/usuarios/upsert-permissions',
      permissaoAtualizada
    );

    toast.success('Permissões atualizadas com sucesso');
    emit('atualiza'); // Emitir evento para atualizar o estado pai, se necessário
  } catch (error) {
    console.error('Erro ao atualizar permissões:', error);
    toast.error('Erro ao atualizar permissões');
  } finally {
    isLoading.value = false;
  }
};

// Atualiza o PIN do usuario atualizarPin
const atualizarPin = async () => {
  try {
    isConfirmAtualizarPinDialogVisibl.value = false;

    isLoading.value = true;

    // Envia a requisição para atualizar o PIN
    const response = await axios.post('/api/usuarios/regenera-pin', {
      user_id: props.dados.id,
    });

    toast.success('PIN atualizado com sucesso');
    emit('voltar');
  } catch (error) {
    console.error('Erro ao atualizar o PIN:', error);
    toast.error('Erro ao atualizar o PIN');
  } finally {
    isLoading.value = false;
  }
};

const isConfirmExluirDialogVisible = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmExlusaoDialogVisible.value = true; // Exibe o diálogo de confirmação
};

const isConfirmAtualizarPinDialogVisible = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmAtualizarPinDialogVisibl.value = true; // Exibe o diálogo de confirmação
};

const handleCancelar = () => {
  isConfirmAtualizarPinDialogVisibl.value = false;
};

const handleConfirmExlucao = () => {
  isConfirmExlusaoDialogVisible.value = false;
  excluirConta(); // Agora acessa props.dados corretamente
};

const handleCancelExlusao = () => {
  isConfirmExlusaoDialogVisible.value = false;
};

const excluirConta = async () => {
  if (!props.dados || !props.dados.id) {
    toast.error('Erro: Dados da conta não encontrados.');
    return;
  }

  try {
    // Fazendo a requisição DELETE para excluir a conta
    const response = await axios.delete(
      `/api/usuarios/delete/${props.dados.id}`
    );

    // Exibir uma notificação de sucesso
    toast.success('Conta excluída com sucesso');
    emit('voltar');
    // Aqui você pode recarregar a lista de contas ou redirecionar o usuário
  } catch (error) {
    // Em caso de erro, exiba uma notificação de erro
    toast.error('Erro ao excluir a conta');
    console.error('Erro ao excluir a conta:', error);
  }
};
</script>

<style scoped>
/* Estilizando a tabela */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

/* Estilos para o spinner */
.spinner {
  border: 4px solid #f3f3f3; /* Cor de fundo */
  border-top: 4px solid #6db631; /* Cor do topo */
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 2s linear infinite;
}

/* Animação do spinner */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.elemento-fixo {
  position: -webkit-sticky; /* Para navegadores que exigem o prefixo */
  position: sticky;
  top: 0; /* Defina o valor para o topo de onde o elemento ficará fixo */
  z-index: 10; /* Para garantir que o elemento fique sobre outros */
}
/* Tornando a lista rolável com barra de rolagem invisível */
.scrollbar-hidden::-webkit-scrollbar {
  display: none;
}

.scrollbar-hidden {
  -ms-overflow-style: none; /* Para o IE e Edge */
  scrollbar-width: none; /* Para o Firefox */
}
.owner {
  font-size: 13px;
  font-family: Figtree;
  font-weight: 500;
  line-height: 18px;
  color: #6d6d6e;
}
</style>
