<template>
  <div v-if="isLoading" class="loading-overlay">
    <div class="spinner"></div>
  </div>
  <div class="mt-5">
    <!-- Formulário de cadastro -->
    <div class="bg-white rounded-lg p-12">
      <div class="flex gap-8 mb-8">
        <!-- Coluna 1: Upload de Imagem -->
        <div class="flex flex-col items-center">
          <div class="relative group">
            <div
              class="w-[110px] h-[110px] bg-[#f3f8f3] rounded-xl flex items-center justify-center cursor-pointer overflow-hidden"
              @click="openFileSelector"
            >
              <template v-if="profilePhotoUrl">
                <img
                  :src="getProfilePhotoUrl(profilePhotoUrl)"
                  alt="Imagem selecionada"
                  class="w-full h-full object-cover"
                />
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
        </div>

        <!-- Coluna 2: Seletor de tipo -->
        <div class="flex flex-col flex-1">
          <LabelModel text="Tipo" class="mb-2" />
          <select
            v-model="selectedTipo"
            class="w-72 h-[44px] bg-[#F3F8F3] border-gray-100 rounded-lg border-2 border-[#d7d7db] p-2 text-base text-[#6DB631] font-bold focus:ring-2 focus:ring-green-500"
          >
            <option value="" disabled selected>Selecione um tipo</option>
            <option
              v-for="tipo in tipos"
              :key="tipo"
              :value="tipo"
              class="text-base font-semibold"
            >
              {{ tipo }}
            </option>
          </select>
        </div>
      </div>

      <!-- Campos do formulário -->
      <LabelModel text="Nome do método" />
      <input
        id="nome"
        v-model="nome"
        class="w-full py-3 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        placeholder="nome do método"
      />
      <span v-if="errors.nome" class="text-red-500 text-sm">
        {{ errors.nome }}
      </span>

      <!-- Botões do formulário -->
      <div class="form-buttons">
        <ButtonCancelar class="w-full" text="Cancelar" @click="cancelForm" />
        <ButtonPrimaryMedio
          text="Atualizar"
          class="w-full"
          iconPath="/storage/images/arrow_left_alt.svg"
          @click="showConfirmDialog('Atualizar método de pagamento?')"
        />
      </div>
    </div>
  </div>

  <!-- Diálogo de confirmação -->
  <ConfirmDialog
    :isVisible="isConfirmDialogVisible"
    :motivo="motivo"
    @confirm="handleConfirm"
    @cancel="handleCancel"
  />
</template>

<script setup>
import { ref, onMounted, defineEmits } from 'vue';
import LabelModel from '@/Components/Label/LabelModel.vue';
import axios from 'axios';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import ButtonCancelar from '../Button/ButtonCancelar.vue';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';
import { useToast } from 'vue-toastification';

// Adicione o prop metodo
const props = defineProps({
  metodo: {
    type: Object,
    default: null,
  },
});

const toast = useToast();
const emit = defineEmits(['voltar']);

// Dados do formulário
const metodo_id = ref(props.metodo.id);
const nome = ref(props.metodo.nome);
const selectedTipo = ref(props.metodo.tipo);
const profilePhotoUrl = ref(props.metodo.img_icon || '');
const selectedFile = ref(null);
const motivo = ref('');

const fileInput = ref(null);
const isLoading = ref(false);
const isConfirmDialogVisible = ref(false);
const errors = ref({});

const tipos = [
  'credito',
  'debito',
  'especie',
  'cabal',
  'alelo',
  'pix',
  'ticket',
  'drex',
  'pagamento_online',
  'vr_alimentacao',
];

// Método para gerar a URL correta da imagem
const getProfilePhotoUrl = (profilePhoto) => {
  if (!profilePhoto) {
    return '/storage/images/no_imagem.svg'; // Caminho para imagem padrão
  }
  return new URL(profilePhoto, window.location.origin).href;
};

// Função para submeter o formulário
const submitForm = async () => {
  errors.value = {}; // Limpa os erros

  // Validações
  if (!nome.value) errors.value.nome = 'O nome é obrigatório';
  if (!selectedTipo.value) errors.value.selectedTipo = 'O tipo é obrigatório';
  if (!profilePhotoUrl.value && !selectedFile.value)
    errors.value.selectedFile = 'O ícone é obrigatório';

  if (Object.keys(errors.value).length === 0) {
    try {
      isLoading.value = true;

      const formData = new FormData();
      formData.append('id', metodo_id.value);
      formData.append('nome', nome.value);
      formData.append('tipo', selectedTipo.value);

      // Adicionar a foto de perfil se estiver presente
      if (selectedFile.value) {
        formData.append('profile_photo', selectedFile.value);
      }

      // Enviar os dados para o backend (atualizando o método de pagamento)
      const response = await axios.post(
        '/api/admin-metodos-pagamentos/atualizar', // Ajuste a URL para corresponder ao ID do método
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        }
      );

      toast.success('Método de pagamento atualizado com sucesso!');
      // Limpar o formulário
      cancelForm();
    } catch (error) {
      toast.error('Erro ao atualizar método de pagamento');
      console.error('Erro ao atualizar método de pagamento:', error);
    } finally {
      isLoading.value = false;
    }
  }
};

const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam;
  isConfirmDialogVisible.value = true;
};

const handleConfirm = () => {
  submitForm(); // Chama a função de atualização
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

// Limpar formulário
const cancelForm = () => {
  nome.value = '';
  tipos.value = '';
  profilePhotoUrl.value = '';
  selectedFile.value = null;
  errors.value = {};

  emit('voltar');
};
</script>

<style lang="css" scoped>
.painel-title {
  font-size: 34px;
  line-height: 15px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  margin-bottom: 10px; /* Espaçamento inferior */
}

.painel-subtitle {
  font-size: 17px;
  line-height: 25px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}
.form-buttons {
  display: flex;
  justify-content: flex-end;
  margin-top: 55px;
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
</style>
