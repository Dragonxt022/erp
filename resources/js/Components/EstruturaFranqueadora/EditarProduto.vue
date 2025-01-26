<template>
  <transition name="fade">
    <div v-if="isVisible" class="sidebar-container">
      <!-- Animação de Carregamento -->
      <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
      </div>
      <div v-else class="w-full h-[860px] bg-white rounded-[20px] p-12">
        <form @submit.prevent="submitForm">
          <!-- Upload de Imagem -->
          <div class="flex justify-center mb-6 relative group">
            <div
              class="w-[110px] h-[110px] bg-[#f3f8f3] rounded-xl flex items-center justify-center cursor-pointer overflow-hidden relative"
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
          <!-- Tabela de fornecedores -->
          <div class="mt-8">
            <table class="min-w-full table-auto">
              <thead>
                <tr class="bg-[#d2fac3]">
                  <th
                    class="px-6 py-2 text-left text-[15px] font-semibold text-[#1d5915] uppercase tracking-wider rounded-tl-2xl"
                  >
                    Fornecedores
                  </th>
                  <th
                    class="px-6 py-2 text-[15px] font-semibold text-[#1d5915] uppercase tracking-wider rounded-tr-2xl"
                  >
                    Valor
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="fornecedor in fornecedores" :key="fornecedor.id">
                  <td
                    class="px-6 py-2 text-[16px] text-gray-800 font-semibold text-left"
                  >
                    {{ fornecedor.razao_social }}
                  </td>
                  <td
                    class="px-6 py-2 text-[16px] text-gray-800 font-semibold text-center"
                  >
                    <input
                      v-model="preco_fornecedor[fornecedor.id]"
                      type="text"
                      placeholder="R$ 0,00"
                      @input="formatarValor(fornecedor.id)"
                      class="border rounded-lg px-2 py-1 w-[110px] h-[38px] text-center"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-start space-x-1 mt-[8%]">
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
import { onMounted, ref, watch } from 'vue';
import { useToast } from 'vue-toastification';
import { Inertia } from '@inertiajs/inertia';
import { defineProps, defineEmits } from 'vue';
import axios from 'axios';
import InputModel from '../Inputs/InputModel.vue';
import LabelModel from '../Label/LabelModel.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import ButtonCancelar from '../Button/ButtonCancelar.vue';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';
import { toRefs } from 'vue';

const toast = useToast();

// Função para formatar o valor de moeda
const formatarValorMoeda = (valor) => {
  valor = valor.toString().replace(/\D/g, ''); // Remove tudo que não for número
  valor = (parseInt(valor, 10) / 100).toFixed(2); // Divide por 100 para formatar como decimal

  // Formata com separador de milhar
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(valor);
};

// Definição das propriedades e variáveis reativas
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
const preco_fornecedor = ref([]);
const fornecedores = ref([]);

const { produto } = toRefs(props);

// Atualiza os campos quando o produto é alterado
// Atualiza os campos quando o produto é alterado
watch(
  () => props.produto,
  (novoProduto) => {
    if (novoProduto) {
      nome.value = novoProduto.nome || '';
      categoria.value = novoProduto.categoria || '';
      unidadeDeMedida.value = novoProduto.unidadeDeMedida || '';
      profilePhotoUrl.value = novoProduto.profile_photo || '';

      // Inicializa preco_fornecedor com os dados de preco do produto
      preco_fornecedor.value = novoProduto.precos.reduce((acc, preco) => {
        // Verifica se o preço do fornecedor existe e formata
        acc[preco.fornecedor_id] = preco.preco_unitario
          ? formatarValorMoeda(preco.preco_unitario)
          : ''; // Deixa o campo vazio se não houver preço
        return acc;
      }, {});
    }
  },
  { immediate: true }
);

// Função para carregar fornecedores da API
const fetchFornecedores = async () => {
  try {
    const response = await axios.get('/api/fornecedores');
    fornecedores.value = response.data.data;

    // Mapear os preços iniciais do produto para cada fornecedor
    produto.value.precos.forEach((preco) => {
      preco_fornecedor.value[preco.id] = (preco.preco_unitario / 100).toFixed(
        2
      );
    });
  } catch (error) {
    console.error('Erro ao carregar fornecedores:', error);
  }
};

// Chama a função quando o componente for montado
onMounted(() => {
  fetchFornecedores();
});
// Função para envio do formulário
const submitForm = async () => {
  if (!nome.value) {
    toast.error('O campo nome é obrigatório.');
    return;
  }

  try {
    isLoading.value = true;

    const formData = new FormData();

    // Passando os dados do produto
    formData.append('id', props.produto.id);
    formData.append('nome', nome.value);
    formData.append('categoria', categoria.value);
    formData.append('unidadeDeMedida', unidadeDeMedida.value);

    // Verificar se a imagem foi alterada
    if (selectedFile.value) {
      formData.append('profile_photo', selectedFile.value);
    }

    // Enviar os preços com base nos fornecedores, incluindo fornecedor_id e preco_unitario
    Object.entries(preco_fornecedor.value).forEach(([fornecedorId, valor]) => {
      if (fornecedorId === 'undefined' || fornecedorId === null) {
        return;
      }

      const valorCentavos = valor.replace(/\D/g, ''); // Remove não números

      formData.append(
        'precos[]',
        JSON.stringify({
          preco_id: null,
          fornecedor_id: fornecedorId,
          preco_unitario: valorCentavos,
        })
      );
    });

    const response = await axios.post(`/api/produtos/atualizar`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    Inertia.replace(route('franqueadora.insumos'), {
      produto: response.data.produto,
      preserveState: true,
    });

    toast.success('Produto atualizado com sucesso!');
  } catch (error) {
    // Log detalhado do erro
    console.error('Erro ao atualizar o produto:', error);
    if (error.response) {
      toast.error(
        `Erro: ${error.response.data.message || 'Erro desconhecido'}`
      );
    } else if (error.request) {
      toast.error('Erro ao conectar ao servidor. Tente novamente.');
    } else {
      toast.error(`Erro: ${error.message}`);
    }
  } finally {
    isLoading.value = false;
  }
};

// Método para formatar o valor ao digitar
const formatarValor = (fornecedor) => {
  let valor = preco_fornecedor.value[fornecedor] || '';
  valor = valor.toString().replace(/\D/g, ''); // Remove tudo que não for número
  valor = (parseInt(valor, 10) / 100).toFixed(2); // Divide por 100 para formatar como decimal

  // Formata com separador de milhar
  const valorFormatado = new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(valor);

  preco_fornecedor.value[fornecedor] = `R$ ${valorFormatado}`;
};

// Método para gerar a URL correta da imagem
const getProfilePhotoUrl = (profilePhoto) => {
  if (!profilePhoto) {
    return '/storage/images/no_imagem.svg'; // Caminho para imagem padrão
  }
  return new URL(profilePhoto, window.location.origin).href;
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

// Validação de tipo e tamanho do arquivo de imagem
const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      toast.error('A imagem não pode exceder 5 MB.');
      return;
    }

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
