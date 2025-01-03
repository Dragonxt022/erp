<template>
  <transition name="fade">
    <div v-if="isVisible" class="sidebar-container">
      <!-- Animação de Carregamento -->
      <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
      </div>
      <div v-else class="w-full h-[525px] bg-white rounded-[20px] p-12">
        <form @submit.prevent="submitForm">
          <LabelModel text="Nome Completo" />
          <InputModel v-model="name" placeholder="João Silva Souza" />

          <LabelModel text="E-mail" />
          <InputModel v-model="email" placeholder="usuario@email.com" />

          <LabelModel text="CNPJ" />
          <InputModel
            v-model="cnpj"
            @input="applyCpfMask"
            placeholder="000.000.000/0000-00"
          />

          <LabelModel text="WhatsApp" />
          <InputModel v-model="whatsapp" placeholder="(00) 00000-0000" />

          <LabelModel text="Estado" />
          <InputModel v-model="estado" placeholder="ex.São Paulo" />

          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>

          <ConfirmDialog
            :isVisible="isConfirmDialogVisible"
            :motivo="motivo"
            @confirm="handleConfirm"
            @cancel="handleCancel"
          />

          <div class="form-buttons">
            <ButtonCancelar text="Cancelar" @click="cancelForm" />
            <ButtonPrimaryMedio
              text="Cadastrar"
              @click="showConfirmDialog('Criar novo Fornecedor?')"
            />
          </div>
        </form>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref } from 'vue';
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
});

const emit = defineEmits(['cancelar']);

const name = ref('');
const email = ref('');
const cnpj = ref('');
const whatsapp = ref('');
const estado = ref('');
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
  email.value = '';
  cnpj.value = '';
  whatsapp.value = '';
  estado.value = '';
  errorMessage.value = '';
};

// Valida os campos do formulário
const validateForm = () => {
  if (!name.value || !email.value || !cnpj.value) {
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

    const response = await axios.post('/api/fornecedores', {
      nome_completo: name.value,
      email: email.value,
      cnpj: cnpj.value,
      whatsapp: whatsapp.value,
      estado: estado.value,
    });

    console.log('Fornecedor cadastrado com sucesso:', response.data);
    toast.success('Fornecedor cadastrado com sucesso!');
    resetForm();
    emit('cancelar');
  } catch (error) {
    toast.error('Erro ao realizar o cadastro.');
    errorMessage.value =
      error.response?.data?.message || 'Erro ao realizar o cadastro.';
  } finally {
    isLoading.value = false;
  }
};

// Aplica máscara ao CNPJ
const applyCpfMask = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length > 14) value = value.slice(0, 14);

  value = value.replace(
    /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
    '$1.$2.$3/$4-$5'
  );
  cnpj.value = value;
};

// Exibe o diálogo de confirmação
const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam;
  isConfirmDialogVisible.value = true;
};

// Manipula a confirmação
const handleConfirm = () => {
  submitForm();
  isConfirmDialogVisible.value = false;
};

// Manipula o cancelamento do diálogo
const handleCancel = () => {
  isConfirmDialogVisible.value = false;
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

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
  opacity: 0;
}
</style>
