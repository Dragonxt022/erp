<template>
  <div>
    <!-- Botão para abrir a modal -->
    <button @click="openModal" class="btn-open-modal">Cadastrar Unidade</button>

    <!-- Modal -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3>Cadastro de Unidade</h3>
        <form @submit.prevent="submitForm">
          <div>
            <label for="cep">CEP:</label>
            <input
              v-model="cep"
              type="text"
              id="cep"
              placeholder="Digite o CEP"
              required
            />
          </div>
          <div>
            <label for="cidade">Cidade:</label>
            <input
              v-model="cidade"
              type="text"
              id="cidade"
              placeholder="Digite a cidade"
              required
            />
          </div>
          <div>
            <label for="bairro">Bairro:</label>
            <input
              v-model="bairro"
              type="text"
              id="bairro"
              placeholder="Digite o bairro"
              required
            />
          </div>
          <div>
            <label for="rua">Rua:</label>
            <input
              v-model="rua"
              type="text"
              id="rua"
              placeholder="Digite a rua"
              required
            />
          </div>
          <div>
            <label for="numero">Número:</label>
            <input
              v-model="numero"
              type="text"
              id="numero"
              placeholder="Digite o número"
              required
            />
          </div>
          <div>
            <label for="cnpj">CNPJ:</label>
            <input
              v-model="cnpj"
              type="text"
              id="cnpj"
              placeholder="Digite o CNPJ"
              required
            />
          </div>
          <div class="modal-buttons">
            <button type="button" @click="closeModal" class="btn-cancel">
              Cancelar
            </button>
            <button type="submit" class="btn-submit">Cadastrar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';

import axios from 'axios';

// Variáveis para o formulário
const isModalOpen = ref(false); // Controle de abertura da modal
const cep = ref('');
const cidade = ref('');
const bairro = ref('');
const rua = ref('');
const numero = ref('');
const cnpj = ref('');

// Função para abrir a modal
const openModal = () => {
  isModalOpen.value = true;
};

// Função para fechar a modal
const closeModal = () => {
  isModalOpen.value = false;
  resetForm(); // Reseta o formulário
};

// Função para resetar o formulário
const resetForm = () => {
  cep.value = '';
  cidade.value = '';
  bairro.value = '';
  rua.value = '';
  numero.value = '';
  cnpj.value = '';
};

// Função para enviar os dados do formulário via API
const submitForm = async () => {
  try {
    const response = await axios.post('/api/unidades', {
      cep: cep.value,
      cidade: cidade.value,
      bairro: bairro.value,
      rua: rua.value,
      numero: numero.value,
      cnpj: cnpj.value,
    });

    console.log('Unidade cadastrada com sucesso:', response.data);

    // Redireciona para a página de unidades utilizando o Inertia
    Inertia.visit('/unidades'); // Isso fará com que a navegação ocorra sem recarregar a página

    closeModal(); // Fecha a modal após o sucesso
  } catch (error) {
    console.error('Erro ao cadastrar unidade:', error);
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  width: 400px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.modal-buttons {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}

.btn-open-modal {
  background-color: #28a745;
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  border: none;
  cursor: pointer;
}

.btn-open-modal:hover {
  background-color: #218838;
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
}

.btn-cancel:hover {
  background-color: #c82333;
}

input {
  width: 100%;
  padding: 8px;
  margin: 10px 0;
  border-radius: 4px;
  border: 1px solid #ccc;
}

label {
  font-weight: bold;
}
</style>
