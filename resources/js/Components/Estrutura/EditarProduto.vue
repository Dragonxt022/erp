<template>
  <transition name="fade">
    <div v-if="isVisible" class="sidebar-container">
      <!-- Animação de Carregamento -->
      <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
      </div>
      <div v-else class="w-full h-[80%] bg-white rounded-[20px] p-12">
        <form @submit.prevent="submitForm">
          <!-- Upload de Imagem -->
          <div class="flex justify-center mb-6 relative group">
            <div
              class="w-[110px] h-[110px] bg-[#f3f8f3] rounded-xl flex items-center justify-center cursor-pointer overflow-hidden relative"
              @click="openFileSelector"
            >
              <template v-if="profilePhotoUrl">
                <img
                  :src="profilePhotoUrl"
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

          <!-- Campo Nome -->
          <LabelModel text="Nome" />
          <InputModel v-model="nome" placeholder="" />

          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>

          <!-- Prioridade -->
          <LabelModel
            class="font-semibold text-gray-800 mt-8"
            text="Prioridade"
          />
          <div class="flex items-center space-x-4 mt-2">
            <label class="flex items-center space-x-2 cursor-pointer">
              <input
                type="radio"
                v-model="categoria"
                value="principal"
                class="hidden"
              />
              <div
                class="w-5 h-5 rounded-full border-2 border-green-500 flex items-center justify-center"
              >
                <div
                  v-if="categoria === 'principal'"
                  class="w-3 h-3 rounded-full bg-green-500"
                ></div>
              </div>
              <span>Principal</span>
            </label>

            <label class="flex items-center space-x-2 cursor-pointer">
              <input
                type="radio"
                v-model="categoria"
                value="secundario"
                class="hidden"
              />
              <div
                class="w-5 h-5 rounded-full border-2 border-green-500 flex items-center justify-center"
              >
                <div
                  v-if="categoria === 'secundario'"
                  class="w-3 h-3 rounded-full bg-green-500"
                ></div>
              </div>
              <span>Secundário</span>
            </label>
          </div>

          <!-- Unidade de medida -->
          <LabelModel
            class="font-semibold text-gray-800 mt-8"
            text="Unidade de medida"
          />
          <div class="flex items-center space-x-4 mt-2">
            <label class="flex items-center space-x-2 cursor-pointer">
              <input
                type="radio"
                v-model="unidadeDeMedida"
                value="a_granel"
                class="hidden"
              />
              <div
                class="w-5 h-5 rounded-full border-2 border-green-500 flex items-center justify-center"
              >
                <div
                  v-if="unidadeDeMedida === 'a_granel'"
                  class="w-3 h-3 rounded-full bg-green-500"
                ></div>
              </div>
              <span>A granel</span>
            </label>

            <label class="flex items-center space-x-2 cursor-pointer">
              <input
                type="radio"
                v-model="unidadeDeMedida"
                value="unitario"
                class="hidden"
              />
              <div
                class="w-5 h-5 rounded-full border-2 border-green-500 flex items-center justify-center"
              >
                <div
                  v-if="unidadeDeMedida === 'unitario'"
                  class="w-3 h-3 rounded-full bg-green-500"
                ></div>
              </div>
              <span>Unitário</span>
            </label>
          </div>

          <div class="flex justify-start space-x-1 mt-[15%]">
            <ButtonCancelar text="Cancelar" @click="cancelForm" />
            <ButtonPrimaryMedio
              text="Atualizar"
              @click="showConfirmDialog('Atualizar esse Produto?')"
            />
          </div>
        </form>
        <ConfirmDialog
          :isVisible="isConfirmDialogVisible"
          :motivo="motivo"
          @confirm="handleConfirm"
          @cancel="handleCancel"
        />
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, watch } from 'vue';
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
  produto: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['cancelar']);

const nome = ref('');

const categoria = ref('');
const unidadeDeMedida = ref('');

const profilePhotoUrl = ref('');
const selectedFile = ref(null);
const errorMessage = ref('');
const fileInput = ref(null);
const isLoading = ref(false);

const isConfirmDialogVisible = ref(false);
const motivo = ref('');

watch(
  () => props.produto,
  (novoProduto) => {
    if (novoProduto) {
      nome.value = novoProduto.nome || '';
      categoria.value = novoProduto.categoria || '';
      unidadeDeMedida.value = novoProduto.unidadeDeMedida || '';
      profilePhotoUrl.value = novoProduto.profile_photo || '';
    }
  },
  { immediate: true } // Executa imediatamente ao montar o componente
);

// Funções
import { Inertia } from '@inertiajs/inertia';

const submitForm = async () => {
  if (!nome.value) {
    toast.error('O campo nome é obrigatório.');
    return;
  }

  try {
    isLoading.value = true;

    // Criar uma nova instância de FormData
    const formData = new FormData();

    // Passando os dados do produto (nome e id)
    formData.append('id', props.produto.id);
    formData.append('nome', nome.value);
    formData.append('categoria', categoria.value);
    formData.append('unidadeDeMedida', unidadeDeMedida.value);

    // Gerar o nome para a imagem (pode ser um nome único)
    if (selectedFile.value) {
      const imageName = `${Date.now()}_${selectedFile.value.name}`;
      formData.append('profile_photo_name', imageName);
      formData.append('profile_photo', selectedFile.value);
    }

    // Enviar os dados para o backend
    const response = await axios.post(`/api/atualizar-produtos`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    // Atualizar a página sem perder o estado, mantendo os dados atualizados
    Inertia.replace(route('insumos'), {
      produto: response.data.produto, // Atualize os dados do produto com a resposta
      preserveState: true, // Preserve o estado atual da página
    });

    toast.success('Produto atualizado com sucesso!');
  } catch (error) {
    toast.error('Erro ao atualizar o produto.');
    errorMessage.value = error.response?.data?.message || 'Erro inesperado.';
  } finally {
    isLoading.value = false;
  }
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

const openFileSelector = () => {
  fileInput.value?.click();
};

const removeImage = () => {
  profilePhotoUrl.value = '';
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

const cancelForm = () => {
  emit('cancelar');
};
</script>

<style scoped>
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
/* Estilos mantidos */
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

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
