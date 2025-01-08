<template>
  <transition name="fade">
    <div v-if="isVisible" class="sidebar-container">
      <!-- Animação de Carregamento -->
      <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
      </div>

      <div v-else class="w-full h-[525px] bg-white rounded-[20px] p-12">
        <div class="painel-title">
          <p>Novo fornecedor</p>
        </div>
        <form @submit.prevent="submitForm">
          <LabelModel text="CNPJ" />
          <InputModel
            v-model="cnpj"
            @input="applyCpfMask"
            placeholder="000.000.000/0000-00"
          />

          <LabelModel text="Razão Social" />
          <InputModel
            v-model="razao_social"
            placeholder="Silva Moura Pescados"
          />

          <LabelModel text="E-mail" />
          <InputModel
            v-model="email"
            placeholder="contato@nobrezasdomar.com.br"
          />

          <LabelModel text="WhatsApp" />
          <InputModel v-model="whatsapp" placeholder="(00) 00000-0000" />

          <LabelModel text="Estado" />
          <InputModel v-model="estado" placeholder="Mato Grosso" />

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
import { Inertia } from '@inertiajs/inertia';

import { defineProps, defineEmits } from 'vue';
import InputModel from '../Inputs/InputModel.vue';
import LabelModel from '../Label/LabelModel.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import ButtonCancelar from '../Button/ButtonCancelar.vue';
import { useToast } from 'vue-toastification';
import ConfirmDialog from '../Laytout/ConfirmDialog.vue';
import { watch } from 'vue';

const toast = useToast();

const props = defineProps({
  isVisible: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['cancelar']);

const cnpj = ref('');
const razao_social = ref('');
const email = ref('');
const whatsapp = ref('');
const estado = ref('');
const errorMessage = ref('');
const isLoading = ref(false);
const isConfirmDialogVisible = ref(false);
const motivo = ref('');

// Watcher para o campo de CNPJ
watch(cnpj, async (newCnpj) => {
  const cnpjLimpo = newCnpj.replace(/\D/g, '');

  // Verifica se o CNPJ tem 14 dígitos
  if (cnpjLimpo.length === 14) {
    await consultarCNPJ(cnpjLimpo);
  }
});

// Cancela e reseta o formulário
const cancelForm = () => {
  resetForm();
  emit('cancelar');
};

// Reseta os valores do formulário
const resetForm = () => {
  cnpj.value = '';
  razao_social.value = '';
  email.value = '';
  whatsapp.value = '';
  estado.value = '';
  errorMessage.value = '';
};

// Valida os campos do formulário
const validateForm = () => {
  if (!cnpj.value) {
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
      razao_social: razao_social.value,
      cnpj: cnpj.value,
      email: email.value,
      whatsapp: whatsapp.value,
      estado: estado.value,
    });

    Inertia.replace(route('fornecedores'), {
      fornecedor: response.data.fornecedor, // Atualiza os dados do fornecedor com a resposta
      preserveState: true, // Preserva o estado atual da página
    });

    toast.success('Fornecedor cadastrado com sucesso!');
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

// Função para consultar dados de CNPJ
const consultarCNPJ = async () => {
  if (!cnpj.value || cnpj.value.length < 14) {
    toast.error('Por favor, insira um CNPJ válido.');
    return;
  }

  try {
    isLoading.value = true;

    // Remove os caracteres especiais do CNPJ
    const cnpjLimpo = cnpj.value.replace(/\D/g, '');

    // Consulta a API pública de CNPJ
    const response = await axios.get(
      `https://publica.cnpj.ws/cnpj/${cnpjLimpo}`
    );
    const empresa = response.data;

    // Preenche os campos automaticamente
    razao_social.value = empresa.razao_social || '';
    estado.value = empresa.estabelecimento.estado.nome || '';
    whatsapp.value = empresa.estabelecimento.telefone1 || '';
    email.value = empresa.estabelecimento.email || '';
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Erro ao consultar o CNPJ.';
  } finally {
    isLoading.value = false;
  }
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
