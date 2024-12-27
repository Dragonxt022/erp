<template>
  <!-- Verifica a visibilidade do elemento lateral -->
  <div v-if="isVisible" class="sidebar-container">
    <!-- Título principal -->
    <div class="painel-title">Dados da nova unidade</div>
    <div class="painel-subtitle">
      <p>Informações básicas sobre a operação</p>
    </div>
    <!-- Subtítulo da página -->
    <div class="w-full h-[525px] bg-white rounded-[20px] p-12">
      <form @submit.prevent="submitForm">
        <LabelModel text="Nome" />
        <InputModel v-model="nome" placeholder="Nome da Empresa" />

        <LabelModel text="CNPJ" />
        <InputModel v-model="cnpj" placeholder="CNPJ" />

        <LabelModel text="Cidade" />
        <InputModel v-model="cidade" placeholder="Cidade" />

        <LabelModel text="CEP" />
        <InputModel v-model="cep" placeholder="CEP" />

        <LabelModel text="Número" />
        <InputModel v-model="numero" placeholder="Número" />

        <LabelModel text="Rua" />
        <InputModel v-model="rua" placeholder="Rua" />

        <LabelModel text="Bairro" />
        <InputModel v-model="bairro" placeholder="Bairro" />

        <!-- Exibição de mensagens de erro -->
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <div class="form-buttons">
          <ButtonCancelar @click="cancelForm">Cancelar</ButtonCancelar>
          <!-- Adicionando a prop 'text' ao botão -->
          <ButtonPrimaryMedio @click="submitForm" text="Cadastrar" />
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, toRefs } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import axios from 'axios';
import { defineProps } from 'vue';
import InputModel from '../Inputs/InputModel.vue';
import LabelModel from '../Label/LabelModel.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import ButtonCancelar from '../Button/ButtonCancelar.vue';

const props = defineProps({
  isVisible: {
    type: Boolean,
    required: true,
  },
});

// Variáveis do formulário
const nome = ref('');
const cep = ref('');
const cidade = ref('');
const bairro = ref('');
const rua = ref('');
const numero = ref('');
const cnpj = ref('');
const errorMessage = ref('');

// Definindo o evento "cancelar" que será emitido para o componente pai
const emit = defineEmits(['cancelar']);

// Função para cancelar e esconder o formulário
const cancelForm = () => {
  resetForm(); // Limpa o formulário
  $emit('cancelar');
};

// Função para resetar os campos do formulário
const resetForm = () => {
  nome.value = '';
  cep.value = '';
  cidade.value = '';
  bairro.value = '';
  rua.value = '';
  numero.value = '';
  cnpj.value = '';
  errorMessage.value = '';
};

// Função para validar os campos do formulário
const validateForm = () => {
  if (!nome.value || !cnpj.value || !cidade.value || !cep.value || !rua.value) {
    errorMessage.value = 'Por favor, preencha todos os campos obrigatórios.';
    return false;
  }
  errorMessage.value = '';
  return true;
};

// Função para enviar o formulário
const submitForm = async () => {
  if (!validateForm()) return;

  try {
    const response = await axios.post('/api/unidades', {
      nome: nome.value,
      cep: cep.value,
      cidade: cidade.value,
      bairro: bairro.value,
      rua: rua.value,
      numero: numero.value,
      cnpj: cnpj.value,
    });

    console.log('Empresa cadastrada com sucesso:', response.data);

    // Redireciona para a página de unidades utilizando o Inertia
    Inertia.visit('/unidades'); // Navegação sem recarregar a página

    resetForm(); // Reseta o formulário após sucesso
    isVisible.value = false; // Oculta o formulário após sucesso
  } catch (error) {
    console.error('Erro ao cadastrar unidade:', error);
    errorMessage.value =
      error.response?.data?.message || 'Erro ao cadastrar unidade.';
  }
};
</script>

<style scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
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
  justify-content: flex-end;
  margin-top: 20px;
}

.btn-submit {
  background-color: #007bff;
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  border: none;
  cursor: pointer;
}

.btn-submit:hover {
  background-color: #0056b3;
}

.btn-cancel {
  background-color: #dc3545;
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  border: none;
  cursor: pointer;
  margin-right: 10px;
}

.btn-cancel:hover {
  background-color: #c82333;
}

.error-message {
  color: red;
  font-size: 14px;
  margin-top: 10px;
}
</style>
