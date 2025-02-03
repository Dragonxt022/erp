<template>
  <div class="elemento-fixo">
    <!-- Tabela de Lotes -->
    <div v-if="!isEditMode">
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

              <!-- Novo seletor-->
              <select
                v-model="dados.cago"
                class="w-72 h-[44px] bg-[#F3F8F3] border-gray-100 rounded-lg border-2 border-[#d7d7db] p-2 text-base text-[#6DB631] font-bold focus:ring-2 focus:ring-green-500 mt-2"
              >
                <option value="" disabled>Selecione um cargo</option>
                <option
                  v-for="(cargo, index) in listaCargos"
                  :key="index"
                  :value="cargo"
                  class="text-base bg-[#F3F8F3] font-bold text-[#6DB631] border-gray-100"
                >
                  {{ cargo }}
                </option>
              </select>
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
                R$ 0,00
              </p>
            </div>
          </div>
          <div class="flex justify-between items-center mt-5 w-full p-1">
            <!-- Coluna 1 -->
            <div class="flex flex-col w-2/3">
              <LabelModel
                class="font-semibold text-gray-900"
                text="Permissões"
              />
            </div>
            <!-- Coluna 2 -->
            <div class="w-1/2 flex justify-end">
              <LabelModel class="font-semibold text-gray-900" text="*** PIN" />
            </div>
          </div>
        </div>
        <div
          class="bg-[#EFEFEF] p-3 px-8 w-full rounded-bl-[20px] rounded-br-[20px]"
        >
          <div
            v-for="chave in Object.keys(dados.user_permission).filter(
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
      </div>
    </div>
  </div>
  <ConfirmDialog
    :isVisible="isConfirmDialogVisible"
    :motivo="motivo"
    @confirm="handleConfirm"
    @cancel="handleCancel"
  />

  <ConfirmDialog
    :isVisible="isConfirmExlusaoDialogVisible"
    :motivo="motivo"
    @confirm="handleConfirmExlucao"
    @cancel="handleCancelExlusao"
  />
</template>

<script setup>
import { defineProps, ref } from 'vue';
import { defineEmits } from 'vue';
import LabelModel from '../Label/LabelModel.vue';
import { useToast } from 'vue-toastification';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';
import Deslizante from '../Button/Deslizante.vue';

const toast = useToast();

const props = defineProps({
  dados: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['voltar', 'atualiza']);

const isEditMode = ref(false);
const indexEditavel = ref(null);
const isConfirmDialogVisible = ref(false);
const isConfirmExlusaoDialogVisible = ref(false);
const motivo = ref('');
const listaCargos = ref(['função 1', 'Função 2', 'Função 3']);

// Sistema de permissão
// Lista de campos que devem ser ocultados
const ocultarCampos = ['id', 'user_id', 'created_at', 'updated_at'];

// Mapeia os nomes das permissões para exibição mais amigável
const formatarPermissao = (chave) => {
  const mapeamento = {
    controle_estoque: 'Controle de Estoque',
    controle_saida_estoque: 'Saída de Estoque',
    gestao_equipe: 'Gestão de Equipe',
    fluxo_caixa: 'Fluxo de Caixa',
    dre: 'DRE',
    contas_pagar: 'Contas a Pagar',
  };

  return mapeamento[chave] || chave;
};

// Atualiza a permissão no banco de dados
const atualizarMetodo = (chave, valor) => {
  console.log(`Alterando ${chave} para`, valor);
  // Aqui você pode fazer a requisição para atualizar no backend
};

// Configuração do diálogo de confirmação
const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmDialogVisible.value = true; // Exibe o diálogo de confirmação
};
const isConfirmExluirDialogVisible = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmExlusaoDialogVisible.value = true; // Exibe o diálogo de confirmação
};

const handleConfirm = () => {
  isConfirmDialogVisible.value = false;
  pagarConta(); // Agora acessa props.dados corretamente
};

const handleCancel = () => {
  isConfirmDialogVisible.value = false;
};

const handleConfirmExlucao = () => {
  isConfirmExlusaoDialogVisible.value = false;
  excluirConta(); // Agora acessa props.dados corretamente
};

const handleCancelExlusao = () => {
  isConfirmExlusaoDialogVisible.value = false;
};

const excluirConta = async (id) => {
  if (!props.dados || !props.dados.id) {
    toast.error('Erro: Dados da conta não encontrados.');
    return;
  }

  try {
    // Fazendo a requisição DELETE para excluir a conta
    const response = await axios.delete(
      `/api/cursto/contas-a-pagar/${props.dados.id}`
    );

    // Exibir uma notificação de sucesso
    toast.success('Conta excluída com sucesso');
    emit('voltar');
    // Aqui você pode recarregar a lista de contas ou redirecionar o usuário
  } catch (error) {
    // Em caso de erro, exiba uma notificação de erro
    console.error('Erro ao excluir a conta:', error);
  }
};
</script>

<style scoped>
/* Estilizando a tabela */

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
