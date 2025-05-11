<template>
  <div v-if="isVisible" class="sidebar-container">
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
    </div>
    <div class="w-full h-[525px] bg-white rounded-[20px] p-12">
      <form >
        <div class="flex justify-center mb-6 relative group">
          <!-- Quadrado com a imagem ou ícone -->
          <div
            class="w-[110px] h-[110px] bg-[#f3f8f3] rounded-xl flex items-center justify-center cursor-pointer overflow-hidden relative"
            @click="openFileSelector">
            <template v-if="profilePhotoUrl">
              <!-- Exibe a imagem selecionada -->
              <img :src="profilePhotoUrl" alt="Imagem selecionada" class="w-full h-full object-cover" />
              <!-- Fundo escuro e botão de remoção ao passar o mouse -->
              <div
                class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                @click.stop>
                <button @click.stop="removeImage"
                  class="w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                  X
                </button>
              </div>
            </template>
            <template v-else>
              <!-- Exibe o ícone se nenhuma imagem foi selecionada -->
              <img src="/storage/images/arrow_upload_ready.svg" alt="Ícone de upload" />
            </template>
          </div>
          <input type="file" ref="fileInput" accept="image/*" class="hidden" @change="handleImageUpload" />
        </div>
        <LabelModel text="Nome do setor operacional" />
        <InputModel v-model="name" placeholder="Entregas" />

        <!-- Componente de confirmação -->
        <ConfirmDialog :isVisible="isConfirmDialogVisible" :motivo="motivo" @confirm="handleConfirm"
          @cancel="handleCancel" />
        <div class="form-buttons">
          <ButtonCancelar  class="w-full mr-2" text="Cancelar" @click="cancelForm" />
          <ButtonPrimaryMedio class="w-full" text="Cadastrar" @click="showConfirmDialog('Criar novo setor?')" />
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { defineProps, defineEmits } from 'vue';
import InputModel from '../Inputs/InputModel.vue';
import LabelModel from '../Label/LabelModel.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import ButtonCancelar from '../Button/ButtonCancelar.vue';

import { useToast } from 'vue-toastification'; // Importa o hook useToast
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';

const toast = useToast(); // Cria a instância do toast

const props = defineProps({
  isVisible: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['cancelar']);

const name = ref('');
const profilePhotoUrl = ref('');
const selectedFile = ref(null);
const fileInput = ref(null);
const errorMessage = ref('');

const isLoading = ref(false);

const isConfirmDialogVisible = ref(false);
const motivo = ref('');

// Cancela e reseta o formulário
const cancelForm = () => {
  resetForm();
  emit('cancelar');
};

// Reseta os valores do formulário
const resetForm = () => {
  name.value = '';
  errorMessage.value = '';
};

// Valida os campos do formulário
const validateForm = () => {
  if (!name.value) {
    toast.error('Por favor, preencha todos os campos obrigatórios.');
    errorMessage.value = 'Por favor, preencha todos os campos obrigatórios.';
    return false;
  }
  return true;
};

// Envia os dados do formulário
const submitForm = async () => {
  if (!validateForm()) return;

  try {
    isLoading.value = true;

    const formData = new FormData();
    formData.append('name', name.value);
      
    // Inclua o arquivo de imagem apenas se ele for selecionado
    if (selectedFile.value) {
      formData.append('profile_photo', selectedFile.value); // Envia o arquivo real
    }


    const response = await axios.post('/api/admin-operacionais', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    toast.success('setor cadastrada com sucesso!');

    resetForm();
  } catch (error) {
    toast.error('Erro ao cadastrar setor.');
    errorMessage.value =
      error.response?.data?.message || 'Erro ao cadastrar setor.';
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

// Funções para upload de imagem
const openFileSelector = () => {
  fileInput.value?.click();
};

const removeImage = () => {
  profilePhotoUrl.value = '';
  selectedFile.value = null;
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
</script>

<style scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e;
  max-width: 600px;
}

.form-buttons {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.error-message {
  color: red;
  font-size: 14px;
  margin-top: 10px;
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
  border: 4px solid #f3f3f3;
  /* Cor de fundo */
  border-top: 4px solid #6db631;
  /* Cor do topo */
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
