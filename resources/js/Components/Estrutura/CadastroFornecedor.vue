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

          <!-- Novo seletor para unidades -->
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
              @click="showConfirmDialog('Criar novo Franqueado?')"
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
const fileInput = ref(null); // Ref para o input de arquivo

const selectedUnit = ref(null); // Unidade selecionada
const units = ref([]); // Lista de unidades

// const selectedCargo = ref(null); // Cargo selecionado
const selectedFile = ref(null);

const isLoading = ref(false);

const isConfirmDialogVisible = ref(false);
const motivo = ref('');

// Busca unidades da API
const fetchUnits = async () => {
  try {
    const response = await axios.get('/api/unidades'); // Chamada real à API
    units.value = response.data.map((item) => ({
      id: item.unidade.id,
      name: item.unidade.cidade || 'Unidade Sem Nome', // Nome da unidade
    }));
  } catch (error) {
    toast.error('Erro ao carregar unidades.');
  }
};

// Chama a função para buscar unidades e cargos
fetchUnits();
// fetchCargos();

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
    selectedFile.value = file; // Armazena o arquivo original
    const reader = new FileReader();
    reader.onload = () => {
      profilePhotoUrl.value = reader.result; // Armazena o caminho base64 (para exibição, se necessário)
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
  selectedUnit.value = null;
  //   selectedCargo.value = null;
  selectedFile.value = null;
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
    isLoading.value = true;

    // Cria o objeto FormData
    const formData = new FormData();
    formData.append('name', name.value);
    formData.append('email', email.value);
    formData.append('cpf', cpf.value);
    formData.append('unidade_id', selectedUnit.value);
    // formData.append('cargo_id', selectedCargo.value);

    // Inclua o arquivo de imagem apenas se ele for selecionado
    if (selectedFile.value) {
      formData.append('profile_photo', selectedFile.value); // Envia o arquivo real
    }

    const response = await axios.post('/api/usuarios', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    console.log('Dados cadastrados com sucesso:', response.data);
    toast.success('Cadastro realizado com sucesso!');
    Inertia.visit('/franqueados');
    resetForm();
  } catch (error) {
    toast.error('Erro ao realizar o cadastro.');
    errorMessage.value =
      error.response?.data?.message || 'Erro ao realizar o cadastro.';
  } finally {
    isLoading.value = false;
  }
};

// Aplica máscara ao CPF
const applyCpfMask = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length > 11) value = value.slice(0, 11);

  value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
  cpf.value = value;
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
