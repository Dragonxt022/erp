<template>
  <transition name="fade">
    <div v-if="isVisible" class="sidebar-container elemento-fixo">
      <!-- Animação de Carregamento -->
      <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
      </div>
      <div v-else class="w-full h-[80%] bg-white rounded-[20px] p-12">
        <form @submit.prevent="submitForm">
          <!-- Upload de Imagem -->
          <div class="flex justify-center mb-6 relative group">
            <!-- Ícone de Favorito no canto superior direito -->

            <img
              :src="
                prioridade
                  ? '/storage/images/favorito_selecionado.svg'
                  : '/storage/images/favorito_selecionar.svg'
              "
              alt="Favorito"
              title="Adicionar esse item como prioridade."
              class="absolute top-2 right-2 w-9 h-9 cursor-pointer"
              @click.stop="togglePrioridade"
            />
            <div
              class="w-[110px] h-[110px] bg-[#f3f8f3] rounded-xl flex items-center justify-center cursor-pointer overflow-hidden relative"
              @click="openFileSelector"
            >
              <template v-if="profilePhotoUrl">
                <img
                  :src="profilePhotoUrl"
                  alt="Imagem selecionada"
                  title="Foto do item"
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
          <InputModel v-model="nome" placeholder="Shoyu Premium Sakaki" />

          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>

          <div
            class="flex flex-wrap md:flex-nowrap items-end gap-4 mt-8 w-full"
          >
            <!-- Unidade de Medida -->
            <div class="flex flex-col w-full md:w-1/2">
              <LabelModel text="Unidade de medida" class="mb-2" />
              <select
                v-model="selectedUnidade"
                class="h-[44px] bg-[#F3F8F3] border-gray-100 rounded-lg border-2 border-[#d7d7db] p-2 text-base text-[#6DB631] font-bold focus:ring-2 focus:ring-green-500"
              >
                <option value="" disabled selected>
                  Selecione uma unidade
                </option>
                <option
                  v-for="unidadeMedida in unidadeMedidas"
                  :key="unidadeMedida.id"
                  :value="unidadeMedida.id"
                  class="text-base font-semibold"
                >
                  {{ unidadeMedida.nome }}
                </option>
              </select>
            </div>

            <!-- Categoria -->
            <div class="flex flex-col w-full md:w-1/2">
              <LabelModel text="Categoria" class="mb-2" />
              <select
                v-model="selectedCategoria"
                class="h-[44px] bg-[#F3F8F3] border-gray-100 rounded-lg border-2 border-[#d7d7db] p-2 text-base text-[#6DB631] font-bold focus:ring-2 focus:ring-green-500"
              >
                <option value="" disabled selected>
                  Selecione uma categoria
                </option>
                <!-- Opção padrão -->
                <option
                  v-for="categoria in categorias"
                  :key="categoria.id"
                  :value="categoria.id"
                  class="text-base font-semibold"
                >
                  {{ categoria.nome }}
                </option>
              </select>
            </div>
          </div>

          <!-- Tabela de fornecedores -->
          <div class="mt-12">
            <table class="min-w-full table-auto">
              <thead>
                <tr class="bg-[#d2fac3]">
                  <th
                    class="px-6 py-2 text-left text-[15px] font-semibold text-[#1d5915] uppercase tracking-wider rounded-tl-2xl"
                  >
                    Fornecedor
                  </th>
                  <th
                    class="px-6 py-2 text-[15px] font-semibold text-[#1d5915] uppercase tracking-wider"
                  >
                    Valor
                  </th>
                  <th
                    class="px-6 py-2 text-[15px] font-semibold text-[#1d5915] uppercase tracking-wider rounded-tr-2xl"
                  >
                    Qtd.Mínima
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
                  <td
                    class="px-6 py-2 text-[16px] text-gray-800 font-semibold text-center"
                  >
                    <input
                      v-model="qtd_minima_fornecedor[fornecedor.id]"
                      type="text"
                      :placeholder="
                        selectedUnidade === 'unitario' ? '0' : '0,000'
                      "
                      @input="formatarQuantidade(fornecedor.id)"
                      class="border rounded-lg px-2 py-1 w-[110px] h-[38px] text-center"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-start space-x-1 mt-[15%]">
            <ButtonCancelar
              class="w-full"
              text="Cancelar"
              @click="cancelForm"
            />
            <ButtonPrimaryMedio
              class="w-full"
              text="Cadastrar"
              @click="showConfirmDialog('Criar novo Produto?')"
            />
          </div>
        </form>
      </div>
      <ConfirmDialog
        :isVisible="isConfirmDialogVisible"
        :motivo="motivo"
        @confirm="handleConfirm"
        @cancel="handleCancel"
      />
    </div>
  </transition>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';
import { defineProps, defineEmits } from 'vue';
import InputModel from '../Inputs/InputModel.vue';
import LabelModel from '../Label/LabelModel.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import ButtonCancelar from '../Button/ButtonCancelar.vue';
import { useToast } from 'vue-toastification';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';

const toast = useToast();

const props = defineProps({
  isVisible: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['cancelar']);

const nome = ref('');
const profilePhotoUrl = ref('');
const prioridade = ref(false);
const selectedCategoria = ref('');
const categorias = ref([]);

const selectedUnidade = ref('');
const unidadeMedidas = ref([
  { nome: 'A granel', id: 'a_granel' },
  { nome: 'Unitário', id: 'unitario' },
]);

const selectedFile = ref(null);
const errorMessage = ref('');
const fileInput = ref(null);
const isLoading = ref(false);

const preco_fornecedor = ref({}); // Preços dos fornecedores
const qtd_minima_fornecedor = ref({}); // Quantidade Mínima
const fornecedores = ref([]); // Armazenar fornecedores
const isConfirmDialogVisible = ref(false);

const motivo = ref('');

onMounted(async () => {
  try {
    const response = await axios.get('/api/fornecedores'); // Chamada à API
    fornecedores.value = response.data.data; // Armazenando fornecedores

    // Inicializar preco_fornecedor com 'R$ 0,00' para cada fornecedor
    fornecedores.value.forEach((fornecedor) => {
      if (!preco_fornecedor.value[fornecedor.id]) {
        preco_fornecedor.value[fornecedor.id] = 'R$ 0,00'; // Valor padrão
      }
    });
  } catch (error) {
    toast.error('Erro ao carregar fornecedores.');
  }

  // Formatar os valores existentes de preços para os fornecedores já definidos
  fornecedores.value.forEach((fornecedor) => {
    if (preco_fornecedor.value[fornecedor.id]) {
      formatarValor(fornecedor.id);
    }
  });
});

// Buscas as categorias dos produtos
onMounted(async () => {
  try {
    const response = await axios.get('/api/categorias-produtos/lista'); // Chamada à API
    categorias.value = response.data; // Armazenando categorias no ref
  } catch (error) {
    toast.error('Erro ao carregar categorias.');
  }
});

// Funções
const formatarValor = (fornecedorId) => {
  let valor = preco_fornecedor.value[fornecedorId] || '';
  valor = valor.toString().replace(/\D/g, ''); // Remove tudo que não for número
  valor = (parseInt(valor, 10) / 100).toFixed(2); // Divide por 100 para formatar como decimal

  // Formata com separador de milhar
  const valorFormatado = new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(valor);

  preco_fornecedor.value[fornecedorId] = `R$ ${valorFormatado}`;
};

const formatarQuantidade = (fornecedorId) => {
  let valor = qtd_minima_fornecedor.value[fornecedorId] || '';

  if (!valor.trim()) {
    qtd_minima_fornecedor.value[fornecedorId] = ''; // Se o campo for apagado, mantém vazio
    return;
  }

  if (selectedUnidade.value === 'unitario') {
    // Se for unitário, o campo deve permitir apenas números inteiros
    let quantidadeInteira = valor.replace(/\D/g, ''); // Remove tudo que não for número
    qtd_minima_fornecedor.value[fornecedorId] = quantidadeInteira; // Mantém somente os inteiros
  } else if (selectedUnidade.value === 'a_granel') {
    // Se for a granel (quilo), o campo deve permitir valores com 3 casas decimais
    let quantidadeNumerica = valor.replace(/\D/g, ''); // Remove tudo que não for número
    if (!quantidadeNumerica) {
      qtd_minima_fornecedor.value[fornecedorId] = ''; // Se não houver números, mantém vazio
      return;
    }

    let inteiro = quantidadeNumerica.slice(0, -3) || '0'; // Parte inteira
    let centavos = quantidadeNumerica.slice(-3).padStart(3, '0'); // Parte decimal

    qtd_minima_fornecedor.value[fornecedorId] = `${Number(
      inteiro
    ).toLocaleString('pt-BR')},${centavos}`;
  }
};

const openFileSelector = () => {
  fileInput.value?.click();
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
  resetForm();
  emit('cancelar');
};

const togglePrioridade = () => {
  prioridade.value = !prioridade.value;
};

const resetForm = () => {
  nome.value = '';

  selectedCategoria.value = '';
  selectedUnidade.value = '';
  profilePhotoUrl.value = '';
  selectedFile.value = null;
  errorMessage.value = '';
};

const validateForm = () => {
  if (
    !nome.value ||
    !selectedFile.value ||
    !selectedUnidade.value ||
    !selectedCategoria.value
  ) {
    toast.error('Por favor, preencha todos os campos obrigatórios.');
    errorMessage.value = 'Por favor, preencha todos os campos obrigatórios.';
    return false;
  }
  return true;
};

const submitForm = async () => {
  if (!validateForm()) return;

  try {
    isLoading.value = true;
    const formData = new FormData();
    formData.append('nome', nome.value);
    formData.append('prioridade', prioridade.value);
    formData.append('categoria_id', selectedCategoria.value);
    formData.append('unidadeDeMedida', selectedUnidade.value);
    formData.append('profile_photo', selectedFile.value);

    // Enviando preços dos fornecedores
    if (
      preco_fornecedor.value &&
      Object.keys(preco_fornecedor.value).length > 0
    ) {
      Object.entries(preco_fornecedor.value).forEach(
        ([fornecedorId, valorCentavos]) => {
          if (
            valorCentavos &&
            valorCentavos !== '' &&
            valorCentavos !== 'R$ NaN'
          ) {
            formData.append(`precos[${fornecedorId}]`, valorCentavos);
          }
        }
      );
    }

    // Enviando quantidades mínimas dos fornecedores
    if (
      qtd_minima_fornecedor.value &&
      Object.keys(qtd_minima_fornecedor.value).length > 0
    ) {
      Object.entries(qtd_minima_fornecedor.value).forEach(
        ([fornecedorId, qtd]) => {
          if (qtd && qtd !== '') {
            formData.append(`quantidades[${fornecedorId}]`, qtd);
          }
        }
      );
    }

    // Enviar o formData com todos os dados
    const response = await axios.post('/api/produtos/cadastrar', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    toast.success('Produto cadastrado com sucesso!');
    Inertia.visit(route('franqueadora.insumos'));

    resetForm();
  } catch (error) {
    toast.error('Erro ao cadastrar o produto.');
    errorMessage.value = error.response?.data?.message || 'Erro inesperado.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.elemento-fixo {
  max-height: 600px; /* Ajuste conforme necessário */
  overflow-y: auto; /* Adiciona a rolagem vertical */
  scrollbar-width: none; /* Remove a barra de rolagem no Firefox */
}

.elemento-fixo::-webkit-scrollbar {
  width: 0; /* Torna a barra de rolagem invisível */
  height: 0;
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

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #6db631;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 2s linear infinite;
}
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

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
