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
          <div
            class="flex flex-wrap md:flex-nowrap items-end gap-4 mt-8 w-full"
          >
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
                  <!-- Exibe o nome da unidade -->
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
                      @blur="formatarQuantidade(fornecedor.id)"
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

          <div class="flex justify-start space-x-1 mt-[8%]">
            <ButtonCancelar
              class="w-full"
              text="Cancelar"
              @click="cancelForm"
            />
            <ButtonPrimaryMedio
              class="w-full"
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

const emit = defineEmits(['cancelar', 'atualizar']);

const nome = ref('');
const prioridade = ref(null);
const selectedUnidade = ref('');
const unidadeMedidas = ref([
  { nome: 'A granel', id: 'a_granel' },
  { nome: 'Unitário', id: 'unitario' },
]);
const selectedCategoria = ref('');
const categorias = ref([]);

const profilePhotoUrl = ref('');
const selectedFile = ref(null);
const errorMessage = ref('');
const fileInput = ref(null);
const isLoading = ref(false);
const isConfirmDialogVisible = ref(false);
const motivo = ref('');
const preco_fornecedor = ref({}); // Preços dos fornecedores
const qtd_minima_fornecedor = ref({}); // Quantidade Mínima
const fornecedores = ref([]);

const { produto } = toRefs(props);

const togglePrioridade = () => {
  prioridade.value = !prioridade.value;
  console.log('Prioridade:', prioridade.value, typeof prioridade.value);
};
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

// Função que carrega as listas de categoria
const fetchCategorias = async () => {
  try {
    const response = await axios.get('/api/categorias-produtos/lista'); // Chamada à API
    categorias.value = response.data; // Armazenando categorias no ref
  } catch (error) {
    toast.error('Erro ao carregar categorias.');
  }
};

// Atualiza os campos quando o produto é alterado
const atualizarProduto = (novoProduto) => {
  if (!novoProduto) return;

  nome.value = novoProduto.nome || '';
  prioridade.value = Boolean(novoProduto.prioridade);
  selectedCategoria.value = novoProduto.categoria_id || '';
  selectedUnidade.value = novoProduto.unidadeDeMedida || '';
  profilePhotoUrl.value = novoProduto.profile_photo || '';

  // Preenche a lista de fornecedores e categorias
  fornecedores.value = novoProduto.fornecedores || [];
  categorias.value = novoProduto.categorias || [];

  // Inicializa preco_fornecedor com os dados de preço do produto
  preco_fornecedor.value = novoProduto.precos.reduce((acc, preco) => {
    acc[preco.fornecedor_id] = preco.preco_unitario
      ? formatarValorMoeda(preco.preco_unitario)
      : '';
    return acc;
  }, {});

  // Inicializa qtd_minima_fornecedor com as quantidades mínimas dos fornecedores
  qtd_minima_fornecedor.value = novoProduto.precos.reduce((acc, preco) => {
    acc[preco.fornecedor_id] = preco.qtd_minima || '';
    return acc;
  }, {});
};

// Observa mudanças no ID do produto e atualiza os dados
watch(
  () => props.produto?.id,
  (novoId) => {
    if (novoId) {
      atualizarProduto(props.produto);
      fetchFornecedores();
      fetchCategorias();
    }
  },
  { immediate: true }
);

// Garante que os dados sejam carregados na montagem do componente
onMounted(() => {
  fetchFornecedores();
  fetchCategorias();
});

const submitForm = async () => {
  if (!nome.value) {
    toast.error('O campo nome é obrigatório.');
    return;
  }

  try {
    isLoading.value = true;

    const formData = new FormData();

    const prioridadeEnvio = prioridade.value ? 1 : 0;
    console.log('Prioridade enviada:', prioridadeEnvio, typeof prioridadeEnvio);

    const dadosProduto = {
      fornecedores: fornecedores.value.map((fornecedor) => ({
        fornecedor_id: fornecedor.id,
        preco_unitario: preco_fornecedor.value[fornecedor.id] || null,
        qtd_minima: qtd_minima_fornecedor.value[fornecedor.id] || null,
      })),
    };

    console.log(dadosProduto); // Verifique no console antes de enviar

    // Passando os dados do produto
    formData.append('id', props.produto.id);
    formData.append('nome', nome.value);
    formData.append('prioridade', prioridadeEnvio);
    formData.append('categoria_id', selectedCategoria.value);
    formData.append('unidadeDeMedida', selectedUnidade.value);
    formData.append('precos', JSON.stringify(dadosProduto));

    // Verificar se a imagem foi alterada
    if (selectedFile.value) {
      formData.append('profile_photo', selectedFile.value);
    }

    const response = await axios.post(`/api/produtos/atualizar`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    toast.success('Produto atualizado com sucesso!');
    emit('atualizar');
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

const formatarQuantidade = (fornecedor) => {
  let valor = qtd_minima_fornecedor.value[fornecedor] || ''; // Recupera o valor atual da quantidade mínima

  if (!valor.trim()) {
    qtd_minima_fornecedor.value[fornecedor] = ''; // Se o campo for apagado, mantém vazio
    return;
  }

  // Verifica a unidade de medida (unitário ou a granel)
  if (selectedUnidade.value === 'unitario') {
    // Se for 'unitario', o campo deve permitir apenas números inteiros
    let quantidadeInteira = valor.replace(/\D/g, ''); // Remove tudo que não for número
    qtd_minima_fornecedor.value[fornecedor] = quantidadeInteira; // Atualiza com os números inteiros
  } else if (selectedUnidade.value === 'a_granel') {
    // Se for 'a granel', o campo deve permitir valores com até 3 casas decimais
    let quantidadeNumerica = valor.replace(/\D/g, ''); // Remove tudo que não for número
    if (!quantidadeNumerica) {
      qtd_minima_fornecedor.value[fornecedor] = ''; // Se não houver números, mantém vazio
      return;
    }

    // Separa a parte inteira e a parte decimal da quantidade
    let inteiro = quantidadeNumerica.slice(0, -3) || '0'; // Parte inteira
    let centavos = quantidadeNumerica.slice(-3).padStart(3, '0'); // Parte decimal (centavos)

    // Atualiza com o valor formatado (parte inteira + parte decimal)
    qtd_minima_fornecedor.value[fornecedor] = `${Number(inteiro).toLocaleString(
      'pt-BR'
    )},${centavos}`;
  }
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
