<template>
  <div
    v-if="produtoSelecionado"
    class="w-[100%] h-full flex flex-col p-4 bg-gray-50"
  >
    <h3 class="text-xl font-bold mb-4 text-gray-500">
      Estou recebendo todos os itens de
    </h3>

    <!-- Selecionar Fornecedor -->
    <div class="mb-6 relative">
      <div class="relative">
        <select
          v-model="fornecedorSelecionado"
          id="fornecedor"
          class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-green-600 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        >
          <option value="" disabled>Selecione um fornecedor</option>
          <option
            v-for="fornecedor in fornecedores"
            :key="fornecedor.id"
            :value="fornecedor.id"
          >
            {{ fornecedor.razao_social }}
          </option>
        </select>
      </div>
    </div>

    <div class="w-full bg-white rounded-lg p-6 mb-6">
      <div class="flex items-center">
        <!-- Imagem do Produto -->
        <div class="flex-shrink-0">
          <img
            :src="getProfilePhotoUrl(produtoSelecionado.profile_photo)"
            alt="Foto do Produto"
            class="w-24 h-24 rounded-lg"
          />
        </div>

        <!-- Detalhes do Produto -->
        <div class="ml-6">
          <div class="text-2xl font-semibold text-gray-800">
            {{ produtoSelecionado.nome || 'N/A' }}
          </div>
        </div>
      </div>
    </div>

    <form @submit.prevent="adicionarProduto" class="space-y-4">
      <!-- Campo de Quantidade -->
      <div>
        <LabelModel :text="unidadeLabel" />
        <InputModel
          v-model="quantidade"
          :placeholder="unidadePlaceholder"
          @input="validarQuantidade"
        />
        <p v-if="quantidadeInvalida" class="text-red-500 text-sm text-center">
          Insira um valor válido.
        </p>
      </div>

      <!-- Campo de Valor -->
      <div>
        <LabelModel text="Valor" />

        <div class="relative flex items-center w-full">
          <!-- Input de texto -->
          <input
            v-model="valor"
            placeholder="R$ 0,00"
            @input="formatarValor"
            class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
          />
        </div>
      </div>

      <!-- Botões -->
      <div class="flex justify-end space-x-4">
        <ButtonPrimary @click="adicionarProduto">Adicionar</ButtonPrimary>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, computed } from 'vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import LabelModel from '../Label/LabelModel.vue';
import InputModel from '../Inputs/InputModel.vue';
import ButtonPrimary from '../Button/ButtonPrimary.vue';

const toast = useToast();

const emit = defineEmits();

const props = defineProps({
  produtoSelecionado: {
    type: Object,
    required: false,
  },
});

const fornecedores = ref([]);
const fornecedorSelecionado = ref('');
const quantidade = ref('');
const valor = ref(props.produtoSelecionado?.valor || '');
const quantidadeInvalida = ref(false);
const fornecedorInvalido = ref(false);
const valorInvalido = ref(false);
const errorMessage = ref('');

// Computed para alterar o rótulo e a configuração do input de acordo com a unidade de medida
const unidadeLabel = computed(() => {
  return props.produtoSelecionado.unidadeDeMedida === 'a_granel'
    ? 'Kg'
    : 'Quantidade';
});

const unidadePlaceholder = computed(() => {
  return props.produtoSelecionado.unidadeDeMedida === 'a_granel'
    ? 'Digite o peso (ex: 1.5)'
    : 'Digite a quantidade';
});

// Validação da quantidade
const validarQuantidade = () => {
  const regex =
    props.produtoSelecionado.unidadeDeMedida === 'a_granel'
      ? /^[0-9]+(\.[0-9]+)?$/ // Aceita números inteiros e fracionados
      : /^[0-9]+$/; // Aceita apenas números inteiros

  quantidadeInvalida.value = !regex.test(quantidade.value);
};

// Validação do fornecedor
const validarFornecedor = () => {
  fornecedorInvalido.value = !fornecedorSelecionado.value;
};

// Validação do valor
const validarValor = () => {
  const regexValor = /^[0-9]+(\.[0-9]{1,2})?$/; // Aceita números com até 2 casas decimais
  valorInvalido.value = !regexValor.test(valor.value);
};

// Função para formatar o valor em centavos
const formatarValor = () => {
  const numeros = valor.value.replace(/\D/g, ''); // Remove tudo que não for número
  const inteiro = numeros.slice(0, -2) || '0'; // Parte inteira do valor
  const centavos = numeros.slice(-2).padStart(2, '0'); // Parte decimal (centavos)
  valor.value = `R$ ${Number(inteiro).toLocaleString('pt-BR')},${centavos}`;
};

// Reseta os valores do formulário
const resetForm = () => {
  quantidade.value = '';
  valor.value = '';
  //   fornecedorSelecionado.value = '';
};

// Busca Fornecedores da API
const fetchFornecedores = async () => {
  try {
    const response = await axios.get('/api/estoque/fornecedores');
    fornecedores.value = response.data.data.map((item) => ({
      id: item.id || 'N/D',
      razao_social: item.razao_social || 'N/D',
    }));
  } catch (error) {
    console.error('Erro ao carregar fornecedores:', error);
  }
};

fetchFornecedores();

// Métodos
const getProfilePhotoUrl = (profilePhoto) => {
  return profilePhoto
    ? new URL(profilePhoto, window.location.origin).href
    : '/storage/images/no_imagem.svg';
};

// Função de validação geral
const validate = () => {
  errorMessage.value = ''; // Limpar mensagem de erro

  if (!fornecedorSelecionado.value) {
    toast.error('Fornecedor não selecionado.');
    errorMessage.value = 'Fornecedor não selecionado.';
    return false;
  }

  if (!quantidade.value || quantidadeInvalida.value) {
    toast.error('Quantidade inválida.');
    errorMessage.value = 'Quantidade inválida.';
    return false;
  }

  if (!valor.value || valorInvalido.value) {
    toast.error('Valor inválido.');
    errorMessage.value = 'Valor inválido.';
    return false;
  }

  return true;
};

const adicionarProduto = () => {
  if (!validate()) return; // Valida antes de adicionar o produto

  const produtoComDados = {
    ...props.produtoSelecionado,
    fornecedor_id: fornecedorSelecionado.value,
    quantidade: quantidade.value,
    valor: valor.value,
    unidade_de_medida: props.produtoSelecionado.unidadeDeMedida, // A unidade de medida é enviada automaticamente
  };

  toast.success('Inusmo adicionado');
  resetForm();
  emit('adicionarAoCarrinho', produtoComDados);
};
</script>

<style scoped>
.elemento-fixo {
  position: -webkit-sticky; /* Para navegadores que exigem o prefixo */
  position: sticky;
  top: 0; /* Defina o valor para o topo de onde o elemento ficará fixo */
  z-index: 10; /* Para garantir que o elemento fique sobre outros */
}
/* Tornando a lista rolável com barra de rolagem invisível */
.scrollbar-hidden::-webkit-scrollbar {
  display: none;
}

.scrollbar-hidden {
  -ms-overflow-style: none; /* Para o IE e Edge */
  scrollbar-width: none; /* Para o Firefox */
}
.owner {
  font-size: 13px;
  font-family: Figtree;
  font-weight: 500;
  line-height: 18px;
  color: #6d6d6e;
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
