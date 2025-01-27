<template>
  <div v-if="isVisible" class="sidebar-container elemento-fixo">
    <!-- Animação de Carregamento -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
    </div>
    <div v-else class="w-full h-[80%] bg-white rounded-[20px] p-12">
      <form @submit.prevent="submitForm">
        <div class="flex justify-center mb-6 relative group">
          <div
            class="w-[110px] h-[110px] bg-[#f3f8f3] rounded-xl flex items-center justify-center overflow-hidden relative"
          >
            <img src="/storage/images/categoria.svg" alt="Ícone de upload" />
          </div>
        </div>
        <!-- Campo Nome -->
        <LabelModel text="Nome" />
        <InputModel v-model="nome" placeholder="ex. Gás, Energia, Aluguel..." />

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <div class="flex justify-start space-x-1 mt-[15%]">
          <ButtonCancelar text="Cancelar" @click="cancelForm" class="w-full" />
          <ButtonPrimaryMedio
            text="Cadastrar"
            class="w-full"
            @click="submitForm"
          />
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';
import { defineProps, defineEmits } from 'vue';
import InputModel from '../Inputs/InputModel.vue';
import LabelModel from '../Label/LabelModel.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import ButtonCancelar from '../Button/ButtonCancelar.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
  isVisible: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['cancelar', 'atualizar']);

const nome = ref(''); // Nome da categoria
const errorMessage = ref('');
const isLoading = ref(false); // Estado de carregamento

const cancelForm = () => {
  resetForm();
  emit('cancelar');
};

const resetForm = () => {
  nome.value = '';
  errorMessage.value = '';
};

const validateForm = () => {
  if (!nome.value) {
    toast.error('Por favor, preencha todos os campos obrigatórios.');
    errorMessage.value = 'Por favor, preencha todos os campos obrigatórios.';
    return false;
  }
  return true;
};

const submitForm = async () => {
  if (!validateForm()) return;

  try {
    isLoading.value = true;
    const formData = new FormData();
    formData.append('nome', nome.value); // Apenas o nome da categoria será enviado

    const response = await axios.post(
      '/api/categorias/cadastrar-categoria-custo', // URL da API para cadastro
      formData
    );

    toast.success('Categoria cadastrada com sucesso!');
    emit('atualizar');
    resetForm(); // Reseta o formulário após o envio
  } catch (error) {
    toast.error('Erro ao cadastrar a categoria.');
    errorMessage.value = error.response?.data?.message || 'Erro inesperado.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.elemento-fixo {
  max-height: 600px; /* Ajuste conforme necessário */
  overflow-y: auto; /* Adiciona a rolagem vertical */
  scrollbar-width: none; /* Remove a barra de rolagem no Firefox */
}

.elemento-fixo::-webkit-scrollbar {
  width: 0; /* Torna a barra de rolagem invisível */
  height: 0;
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

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #6db631;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 2s linear infinite;
}
/* Customiza as opções de rádio */
.w-5 {
  width: 1.25rem;
}
.h-5 {
  height: 1.25rem;
}
.rounded-full {
  border-radius: 50%;
}
.border-2 {
  border-width: 2px;
}
.bg-green-500 {
  background-color: #22c55e; /* Verde */
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
