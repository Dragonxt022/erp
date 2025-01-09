<template>
  <div class="elemento-fixo">
    <div
      v-if="!isEditMode"
      class="w-full h-[200px] bg-white rounded-[20px] p-12"
    >
      <div class="relative w-full h-full">
        <!-- Container das colunas -->
        <div class="flex items-center">
          <!-- Coluna da Imagem -->
          <div class="w-1/1 flex justify-center">
            <img
              :src="produto.profile_photo || '/storage/images/no_imagem.svg'"
              alt="Foto do Usuário"
              class="w-20 h-20 rounded-md shadow-lg"
            />
          </div>

          <!-- Coluna do Nome -->
          <div class="w-2/3 pl-5">
            <div
              class="text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[30px] tracking-tight"
            >
              {{ produto.nome || 'N/A' }}

              <div class="owner">
                {{ produto.categoria }} / {{ produto.unidadeDeMedida }}
              </div>
            </div>
          </div>

          <div class="w-1/1">
            <ConfirmDialog
              :isVisible="isConfirmDialogVisible"
              :motivo="motivo"
              @confirm="handleConfirm"
              @cancel="handleCancel"
            />
            <div
              class="absolute top-4 right-4 cursor-pointer"
              @click="showConfirmDialog('Excluir esse produto?')"
            >
              <img
                src="/storage/images/delete.svg"
                alt="Deletar Usuário"
                class="w-6 h-6"
              />
            </div>
          </div>
        </div>
        <!-- Exibe o botão de edição apenas se uma unidade for selecionada -->
      </div>
    </div>
    <div v-if="produto.id && !isEditMode" class="mt-4">
      <ButtonEditeMedio
        text="Editar insumos"
        icon-path="/storage/images/border_color.svg"
        @click="toggleEditMode"
        class="px-4 py-2 bg-[#F8F8F8] text-white rounded-lg"
      />
    </div>
    <EditarProduto
      v-if="isEditMode"
      ref="dadosProduto"
      :isVisible="isEditMode"
      :produto="produto"
      @dadosProduto="fetchProdutos"
      @cancelar="cancelEdit"
    />
  </div>
</template>

<script setup>
import { defineProps, ref } from 'vue';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';
import ConfirmDialog from '../Laytout/ConfirmDialog.vue';
import { useToast } from 'vue-toastification';
import ButtonEditeMedio from '../Button/ButtonEditeMedio.vue';
import EditarProduto from './EditarProduto.vue';

const toast = useToast();

const props = defineProps({
  produto: {
    type: Object,
    required: true,
  },
});

const isConfirmDialogVisible = ref(false);
const motivo = ref('');
const showCadastroProduto = ref(false);

const isEditMode = ref(false);
const isLoading = ref(false);

const fetchProdutos = () => {
  const dadosProduto = ref.dadosProduto;
  dadosProduto.fetchProduto();
};

const deleteProduto = async () => {
  try {
    isLoading.value = true;
    const response = await axios.delete(
      `/api/excluir-produto/${props.produto.id}`
    ); // URL para deletar o produto
    console.log(response.data.message); // Mensagem de sucesso do backend
    toast.success('Produto deletado com sucesso!'); // Exibe um toast de sucesso
    Inertia.visit('/insumos');
  } catch (error) {
    toast.error(
      'Erro ao deletar o produto:',
      error.response?.data?.error || error.message
    );
    console.error(
      'Erro ao deletar o produto:',
      error.response?.data?.error || error.message
    );
  } finally {
    isLoading.value = false;
  }
};

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value;
  showCadastroProduto.value = false;
};

const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmDialogVisible.value = true; // Exibe o diálogo de confirmação
};

const handleConfirm = () => {
  deleteProduto();
  isConfirmDialogVisible.value = false;
};

const handleCancel = () => {
  isConfirmDialogVisible.value = false;
};

const cancelEdit = () => {
  isEditMode.value = false;
  showCadastroProduto.value = true;
};
</script>

<style scoped>
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
</style>
