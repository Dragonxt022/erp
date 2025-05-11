<template>
  <div v-if="isVisible" class="sidebar-container">
    <div class="w-full h-[525px] bg-white rounded-[20px] p-12">
      <form>
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
          <ButtonCancelar class="w-full mr-2" text="Cancelar" @click="cancelForm" />
          <ButtonPrimaryMedio class="w-full" text="Atualizar" @click="showConfirmDialog" />
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { defineProps, defineEmits } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

import InputModel from '../Inputs/InputModel.vue';
import LabelModel from '../Label/LabelModel.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import ButtonCancelar from '../Button/ButtonCancelar.vue';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';

const toast = useToast();

const props = defineProps({
  isVisible: {
    type: Boolean,
    required: true,
  },
  informacoes: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['cancelar']);

const id = ref('');
const name = ref('');
const profilePhotoUrl = ref('');
const selectedFile = ref(null);
const fileInput = ref(null);
const errorMessage = ref('');
const isConfirmDialogVisible = ref(false);
const motivo = ref('');

watch(
  () => props.informacoes,
  (newVal) => {
    if (newVal) {
      id.value = newVal.id || '';
      name.value = newVal.name || '';
      profilePhotoUrl.value = newVal.profile_photo_url || '';
    }
  },
  { immediate: true }
);

// Função para exibir o diálogo de confirmação 
const showConfirmDialog = () => {
  isConfirmDialogVisible.value = true;
};
const handleConfirm = () => {
  isConfirmDialogVisible.value = false;
  submitForm();
};
const handleCancel = () => {
  isConfirmDialogVisible.value = false;
};

const openFileSelector = () => {
  fileInput.value.click();
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
    profilePhotoUrl.value = URL.createObjectURL(file);
  }
};

const removeImage = () => {
  selectedFile.value = null;
  profilePhotoUrl.value = '';
};

const cancelForm = () => {
  emit('cancelar');
};

const submitForm = async () => {
  if (!id.value || !name.value) {
    errorMessage.value = 'Por favor, preencha todos os campos obrigatórios.';
    return;
  }

  try {
    const formData = new FormData();
    formData.append('id', id.value);
    formData.append('name', name.value);
    if (selectedFile.value) {
      formData.append('profile_photo', selectedFile.value);
    }

    formData.append('_method', 'PUT');
    await axios.post(`/api/admin-operacionais/${id.value}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    toast.success('Informações atualizadas com sucesso!');
    emit('cancelar');
  } catch (error) {
    toast.error('Erro ao atualizar as informações.');
    errorMessage.value = error.response?.data?.message || 'Erro ao atualizar as informações.';
    console.error('Erro na atualização:', error);
  }
};
</script>


<style scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
  /* Cor escura para título */
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e;
  /* Cor secundária */
  max-width: 600px;
  /* Limita a largura do subtítulo */
}

.form-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.form-buttons {
  display: flex;
  justify-content: flex-start;
  margin-top: 20px;
}

.error-message {
  color: red;
  font-size: 14px;
  margin-top: 10px;
}
</style>
