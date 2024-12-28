<template>
  <div v-if="isVisible" class="sidebar-container">
    <div class="w-full h-[525px] bg-white rounded-[20px] p-12">
      <form @submit.prevent="submitForm">
        <div class="flex justify-center mb-6 relative group">
          <!-- Quadrado com a imagem ou ícone -->
          <div
            class="w-[110px] h-[110px] bg-[#f3f8f3] rounded-xl flex items-center justify-center cursor-pointer overflow-hidden relative"
            @click="openFileSelector"
          >
            <template v-if="profilePhotoUrl">
              <!-- Exibe a imagem selecionada -->
              <img
                :src="profilePhotoUrl"
                alt="Imagem selecionada"
                class="w-full h-full object-cover"
              />
              <!-- Fundo escuro e botão de remoção ao passar o mouse -->
              <div
                class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                @click.stop
              >
                <button
                  @click.stop="removeImage"
                  class="w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-sm"
                >
                  X
                </button>
              </div>
            </template>
            <template v-else>
              <!-- Exibe o ícone se nenhuma imagem foi selecionada -->
              <img
                src="/storage/images/arrow_upload_ready.svg"
                alt="Ícone de upload"
              />
            </template>
          </div>
          <input
            type="file"
            ref="fileInput"
            accept="image/*"
            class="hidden"
            @change="handleImageUpload"
          />
        </div>

        <LabelModel text="Nome Completo" />
        <InputModel v-model="name" placeholder="João Silva Souza" />

        <LabelModel text="E-mail" />
        <InputModel v-model="email" placeholder="usuario@email.com" />

        <LabelModel text="CPF" />
        <InputModel
          v-model="cpf"
          @input="applyCpfMask"
          placeholder="000.000.000-00"
        />

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
import { useToast } from 'vue-toastification';

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
const cpf = ref('');
const profilePhotoUrl = ref(''); // Campo para armazenar o caminho da imagem
const errorMessage = ref('');
const fileInput = ref(null); // Ref para o input de arquivo

// Manipula a seleção de arquivos
const openFileSelector = () => {
  fileInput.value?.click(); // Garante que fileInput seja o input de arquivo
};

const removeImage = () => {
  profilePhotoUrl.value = ''; // Remove a imagem selecionada
  toast.info('Imagem removida.'); // Mensagem de confirmação
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = () => {
      profilePhotoUrl.value = reader.result; // Armazena o caminho da imagem
      toast.success('Imagem carregada com sucesso!');
    };
    reader.readAsDataURL(file);
  }
};

// Cancela e reseta o formulário
const cancelForm = () => {
  resetForm();
  emit('cancelar');
};

// Reseta os valores do formulário
const resetForm = () => {
  name.value = '';
  email.value = '';
  cpf.value = '';
  profilePhotoUrl.value = '';
  errorMessage.value = '';
};

// Valida os campos do formulário
const validateForm = () => {
  if (!name.value || !email.value || !cpf.value) {
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
      name: name.value,
      email: email.value,
      cpf: cpf.value,
      profile_photo_url: profilePhotoUrl.value, // Inclui o caminho da imagem no payload
    });

    console.log('Dados cadastrados com sucesso:', response.data);
    toast.success('Cadastro realizado com sucesso!');
    Inertia.visit('/unidades');
    resetForm();
  } catch (error) {
    toast.error('Erro ao realizar o cadastro.');
    errorMessage.value =
      error.response?.data?.message || 'Erro ao realizar o cadastro.';
  }
};

// Aplica máscara ao CPF
const applyCpfMask = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length > 11) value = value.slice(0, 11);

  value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
  cpf.value = value;
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
