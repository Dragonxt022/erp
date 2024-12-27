<template>
  <div v-if="isVisible" class="sidebar-container">
    <div class="painel-title">Dados da nova unidade</div>
    <div class="painel-subtitle">
      <p>Informações básicas sobre a operação</p>
    </div>
    <div class="w-full h-[525px] bg-white rounded-[20px] p-12">
      <form @submit.prevent="submitForm">
        <LabelModel text="CNPJ" />
        <InputModel v-model="cnpj" @input="applyCnpjMask" placeholder="CNPJ" />

        <LabelModel text="Cidade" />
        <InputModel v-model="cidade" placeholder="Cidade" />

        <LabelModel text="CEP" />
        <InputModel v-model="cep" @input="applyCepMask" placeholder="CEP" />

        <div class="flex space-x-4">
          <!-- flex para organizar os itens em linha, space-x-4 para espaçamento -->
          <div class="flex flex-col w-1/2">
            <!-- Flexbox dentro do div para empilhar os elementos -->
            <LabelModel text="Rua" />
            <InputModel v-model="rua" placeholder="Rua" />
          </div>
          <div class="flex flex-col w-1/2">
            <!-- Outro flex para o lado direito -->
            <LabelModel text="Número" />
            <InputModel v-model="numero" placeholder="Número" />
          </div>
        </div>
        <LabelModel text="Bairro" />
        <InputModel v-model="bairro" placeholder="Bairro" />

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <div class="form-buttons">
          <ButtonCancelar text="Cancelar" @click="cancelForm" />
          <ButtonPrimaryMedio text="Cadastrar" @click="submitForm" />
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

import { useToast } from 'vue-toastification'; // Importa o hook useToast

const toast = useToast(); // Cria a instância do toast

const props = defineProps({
  isVisible: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['cancelar']);

const cnpj = ref('');
const cep = ref('');
const cidade = ref('');
const bairro = ref('');
const rua = ref('');
const numero = ref('');
const errorMessage = ref('');

// Cancela e reseta o formulário
const cancelForm = () => {
  resetForm();
  emit('cancelar');
};

// Reseta os valores do formulário
const resetForm = () => {
  cnpj.value = '';
  cep.value = '';
  cidade.value = '';
  bairro.value = '';
  rua.value = '';
  numero.value = '';
  errorMessage.value = '';
};

// Valida os campos do formulário
const validateForm = () => {
  if (!cnpj.value || !cep.value || !bairro.value) {
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
    const response = await axios.post('/api/unidades', {
      cnpj: cnpj.value,
      cep: cep.value,
      cidade: cidade.value,
      bairro: bairro.value,
      rua: rua.value,
      numero: numero.value,
    });

    console.log('Unidade cadastrada com sucesso:', response.data);
    toast.success('Unidade cadastrada com sucesso!');
    Inertia.visit('/unidades');
    resetForm();
  } catch (error) {
    toast.error('Erro ao cadastrar unidade.');
    errorMessage.value =
      error.response?.data?.message || 'Erro ao cadastrar unidade.';
  }
};

// Aplica máscara ao CNPJ
const applyCnpjMask = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length > 14) value = value.slice(0, 14);

  value = value.replace(/^(\d{2})(\d)/, '$1.$2');
  value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
  value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
  value = value.replace(/(\d{4})(\d)/, '$1-$2');

  cnpj.value = value;
};

// Aplica máscara ao CEP
const applyCepMask = (event) => {
  let value = event.target.value.replace(/\D/g, ''); // Remove caracteres não numéricos
  if (value.length > 8) value = value.slice(0, 8); // Limita o valor a 8 dígitos

  // Aplica a máscara de CEP
  value = value.replace(/(\d{5})(\d)/, '$1-$2');

  cep.value = value; // Atualiza o valor do CEP no formulário
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
</style>
