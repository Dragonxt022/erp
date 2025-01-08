<template>
  <div v-if="isVisible" class="sidebar-container">
    <!-- Animação de Carregamento -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
    </div>

    <div v-else class="w-full h-[400px] bg-white rounded-[20px] p-12">
      <form @submit.prevent="submitForm">
        <!-- Capos do formulario -->
        <LabelModel text="CNPJ" />

        <div
          class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        >
          {{ fornecedor.cnpj || 'N/A' }}
        </div>

        <LabelModel text="Razão Social" />
        <InputModel v-model="razao_social" placeholder="" />

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <LabelModel text="E-mail" />
        <InputModel v-model="email" placeholder="" />

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <LabelModel text="WhatsApp" />
        <InputModel v-model="whatsapp" placeholder="" />

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <LabelModel text="Estado" />
        <InputModel v-model="estado" placeholder="" />

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <!-- fim dos campos de formularios -->

        <div class="flex justify-start space-x-1 mt-[50px]">
          <ButtonCancelar text="Cancelar" @click="cancelForm" />
          <ButtonPrimaryMedio
            text="Atualizar"
            @click="showConfirmDialog('Atualizar esse Fornecedor?')"
          />
        </div>
      </form>
      <ConfirmDialog
        :isVisible="isConfirmDialogVisible"
        :motivo="motivo"
        @confirm="handleConfirm"
        @cancel="handleCancel"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { defineProps, defineEmits } from 'vue';
import InputModel from '../Inputs/InputModel.vue';
import LabelModel from '../Label/LabelModel.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import ButtonCancelar from '../Button/ButtonCancelar.vue';
import { useToast } from 'vue-toastification';
import ConfirmDialog from '../Laytout/ConfirmDialog.vue';

const toast = useToast();

const props = defineProps({
  isVisible: {
    type: Boolean,
    required: true,
  },
  fornecedor: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['cancelar']);
const razao_social = ref('');
const email = ref('');
const whatsapp = ref('');
const estado = ref('');

const errorMessage = ref('');
const isLoading = ref(false);

const isConfirmDialogVisible = ref(false);
const motivo = ref('');

watch(
  () => props.fornecedor,
  (atualizarFornecedor) => {
    if (atualizarFornecedor) {
      razao_social.value = atualizarFornecedor.razao_social || '';
      email.value = atualizarFornecedor.email || '';
      whatsapp.value = atualizarFornecedor.whatsapp || '';
      estado.value = atualizarFornecedor.estado || '';
    }
  },
  { immediate: true } // Executa imediatamente ao montar o componente
);

// Funções
import { Inertia } from '@inertiajs/inertia';

const submitForm = async () => {
  // Validações para os campos obrigatórios
  if (!razao_social.value || !email.value || !whatsapp.value || !estado.value) {
    toast.error('Preencha todos os campos obrigatórios.');
    return;
  }

  try {
    isLoading.value = true;

    // Criar uma nova instância de FormData
    const formData = new FormData();

    // Passando os dados do fornecedor (id, cnpj, razão social, e-mail, whatsapp, estado)
    formData.append('id', props.fornecedor.id);
    formData.append('cnpj', props.fornecedor.cnpj);
    formData.append('razao_social', razao_social.value);
    formData.append('email', email.value);
    formData.append('whatsapp', whatsapp.value);
    formData.append('estado', estado.value);

    // Enviar os dados para o backend
    const response = await axios.post(
      `/api/atualizar-fornecedores/`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }
    );

    // Atualizar a página sem perder o estado, mantendo os dados atualizados
    Inertia.replace(route('fornecedores'), {
      fornecedor: response.data.fornecedor, // Atualiza os dados do fornecedor com a resposta
      preserveState: true, // Preserva o estado atual da página
    });

    toast.success('Fornecedor atualizado com sucesso!');
  } catch (error) {
    toast.error('Erro ao atualizar o fornecedor.');
    errorMessage.value = error.response?.data?.message || 'Erro inesperado.';
  } finally {
    isLoading.value = false;
  }
};

const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmDialogVisible.value = true; // Exibe o diálogo de confirmação
};

const handleConfirm = () => {
  submitForm();
  isConfirmDialogVisible.value = false;
};

const handleCancel = () => {
  isConfirmDialogVisible.value = false;
};

const openFileSelector = () => {
  fileInput.value?.click();
};

const removeImage = () => {
  profilePhotoUrl.value = '';
  toast.info('Imagem removida.');
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
    const reader = new FileReader();
    reader.onload = () => {
      profilePhotoUrl.value = reader.result;
      toast.success('Imagem carregada com sucesso!');
    };
    reader.readAsDataURL(file);
  }
};

const cancelForm = () => {
  emit('cancelar');
};
</script>

<style scoped>
/* Estilos mantidos */
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

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #6db631;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
