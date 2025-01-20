<template>
  <div v-if="!isEditMode" class="elemento-fixo">
    <div>
      <h3 class="text-xl font-bold mb-4 text-gray-500">
        Enviar pedido para o fornecedor
      </h3>
      <div class="mb-6 relative">
        <div class="relative">
          <select
            v-model="fornecedorSelecionado"
            id="fornecedor"
            :disabled="fornecedorBloqueado"
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
    </div>
    <div class="w-full h-[150px] bg-white rounded-[20px] p-12">
      <div class="relative w-full h-full">
        <div class="flex items-center">
          <div class="w-1/1 flex justify-center">
            <img
              :src="getProfilePhotoUrl(produto.profile_photo)"
              alt="Foto do Produto"
              class="w-20 h-20 rounded-md shadow-lg"
            />
          </div>
          <div class="w-2/3 pl-5">
            <div
              class="text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[30px] tracking-tight"
            >
              {{ produto.nome || 'N/A' }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Tabela de Preços -->
    <div v-if="!isEditMode" class="mt-8">
      <LabelModel
        text="valor por fornecedor"
        class="tracking-wider uppercase"
      />
      <table class="min-w-full table-auto">
        <thead>
          <tr>
            <th
              class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider rounded-tl-2xl"
            >
              Fornecedor
            </th>
            <th
              class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider rounded-tr-2xl"
            >
              Preço
              {{
                produto.unidadeDeMedida === 'unitario'
                  ? 'Por Unidade'
                  : 'Por Kg'
              }}
            </th>
          </tr>
        </thead>
        <tbody>
          <!-- Mostra animação enquanto está carregando -->
          <tr v-if="isLoading" class="text-center">
            <td
              colspan="2"
              class="px-6 py-4 text-[16px] text-gray-500 font-semibold"
            >
              <div class="flex justify-center items-center">
                <svg
                  class="animate-spin h-5 w-5 mr-3 text-gray-500"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"
                  ></path>
                </svg>
                Carregando...
              </div>
            </td>
          </tr>

          <!-- Mostra preços se disponíveis -->
          <tr
            v-else-if="produtoData.precos && produtoData.precos.length > 0"
            v-for="(preco, index) in produtoData.precos"
            :key="index"
            class="text-center"
          >
            <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
              {{ preco.fornecedor }}
            </td>
            <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
              {{ formatarParaReais(preco.preco_unitario) }}
            </td>
          </tr>

          <!-- Exibe mensagem caso não haja preços -->
          <tr v-else class="text-center">
            <td
              colspan="2"
              class="px-6 py-4 text-[16px] text-gray-500 font-semibold"
            >
              Nenhum preço disponível para este produto.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="mt-12">
      <label
        class="up w-full h-[18.63px] text-[#6d6d6d] text-[15px] font-semibold font-['Figtree'] leading-tight mt-3 mb-3"
      >
        {{ produto.unidadeDeMedida === 'unitario' ? 'Unitario' : 'A Granel' }}
      </label>
      <InputModel v-model="quantidade" />
    </div>
    <div class="mt-5 flex gap-2">
      <ButtonPrimaryCarrinho
        :disabled="isLoading"
        text="adicionar ao carrinho"
        iconPath="/storage/images/carrinho.svg"
        @click="adicionarAoCarrinho"
        class="uppercase"
      />
      <ButtonPrimaryMedio
        class="w-full"
        text="Finalizar Pedido"
        iconPath="/storage/images/arrow_left_alt.svg"
        @click="finalizarResumo"
      />
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, watch } from 'vue';
import { useToast } from 'vue-toastification';

import axios from 'axios';
import LabelModel from '../Label/LabelModel.vue';
import ButtonPrimaryCarrinho from '../Button/ButtonPrimaryCarrinho.vue';
import InputModel from '../Inputs/InputModel.vue';
import ButtonCancelar from '../Button/ButtonCancelar.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
const toast = useToast();

const props = defineProps({
  produto: {
    type: Object,
    required: true,
  },
});

const produtoData = ref({
  precos: [],
});

// Emissor de eventos
const emit = defineEmits(['adicionarAoCarrinho']);

const isLoading = ref(false);
const isEditMode = ref(false);
const fornecedorBloqueado = ref(false);
const fornecedores = ref([]);
const fornecedorSelecionado = ref('');
const quantidade = ref('');

const finalizarResumo = () => {
  emit('finalizar');
};

// Função para adicionar um item ao carrinho
const adicionarAoCarrinho = () => {
  if (!fornecedorSelecionado.value) {
    toast.warning('Por favor, selecione o fornecedor!');
    return;
  }

  if (!quantidade.value) {
    toast.warning('É necessário informar uma quantidade!');
    return;
  }

  // Verifique se produtoData existe
  if (!produtoData.value || !produtoData.value.precos) {
    toast.error(
      'Houve um erro com o produto, tente novamente ou chame o suporte.'
    );
    return;
  }

  // Verifique se o fornecedor selecionado tem um preço associado
  const fornecedorPreco = produtoData.value.precos.find(
    (preco) => preco.fornecedor_id === fornecedorSelecionado.value
  );

  // Caso não encontre um preço do fornecedor, exibe uma mensagem de erro
  if (!fornecedorPreco) {
    toast.warning('Este fornecedor não está disponível no momento!');
    return;
  }

  // Busca o nome do fornecedor no array de fornecedores
  const fornecedor = fornecedores.value.find(
    (f) => f.id === fornecedorSelecionado.value
  );

  const precoUnitario = fornecedorPreco ? fornecedorPreco.preco_unitario : 0;

  const itemCarrinho = {
    id: produtoData.value.id,
    nome: produtoData.value.nome,
    unidadeDeMedida: produtoData.value.unidadeDeMedida,
    fornecedorId: fornecedorSelecionado.value,
    nomeFornecedor: fornecedor
      ? fornecedor.razao_social
      : 'Fornecedor desconhecido',
    quantidade: parseFloat(quantidade.value),
    preco: precoUnitario, // Preço definido do fornecedor
  };

  // Emite o item para ser adicionado ao carrinho
  emit('adicionarAoCarrinho', itemCarrinho);
  toast.success('Item adicionado a sua lista de pedido.');

  // Limpa o campo de quantidade após adicionar ao carrinho
  quantidade.value = '';

  // Travar o seletor de fornecedor
  fornecedorBloqueado.value = true;
};

// const adicionarAoCarrinho = () => {
//   if (!fornecedorSelecionado.value) {
//     toast.warning('Por favor, selecione o fornecedor!');
//     return;
//   }

//   if (!quantidade.value) {
//     toast.warning('É necessário informar uma quantidade!');
//     return;
//   }

//   // Verifique se produtoData existe
//   if (!produtoData.value || !produtoData.value.precos) {
//     toast.error(
//       'Houve um erro com o produto, tente novamente ou chame o suporte.'
//     );
//     return;
//   }

//   // Verifique se o fornecedor selecionado tem um preço associado
//   const fornecedorPreco = produtoData.value.precos.find(
//     (preco) => preco.fornecedor_id === fornecedorSelecionado.value
//   );

//   // Caso não encontre um preço do fornecedor, exibe uma mensagem de erro
//   if (!fornecedorPreco) {
//     toast.warning('Este fornecedor não está disponível no momento!');
//     return;
//   }

//   const precoUnitario = fornecedorPreco ? fornecedorPreco.preco_unitario : 0;

//   const itemCarrinho = {
//     id: produtoData.value.id,
//     nome: produtoData.value.nome,
//     unidadeDeMedida: produtoData.value.unidadeDeMedida,
//     fornecedorId: fornecedorSelecionado.value,
//     nomeFornecedor: fornecedorPreco.fornecedor, // Nome do fornecedor do preço encontrado
//     quantidade: parseFloat(quantidade.value),
//     preco: precoUnitario, // Preço definido do fornecedor
//   };

//   // Emite o item para ser adicionado ao carrinho
//   emit('adicionarAoCarrinho', itemCarrinho);
//   toast.success('Item adicionado a sua lista de pedido.');

//   // Limpa o campo de quantidade após adicionar ao carrinho
//   quantidade.value = '';
// };

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

const formatarParaReais = (valorEmCentavos) => {
  return (valorEmCentavos / 100).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  });
};

const fetchProduto = async () => {
  isLoading.value = true;
  try {
    if (props.produto && props.produto.id) {
      const response = await axios.get(`/api/produtos/lista`);
      const produtos = response.data;

      // Encontra o produto pelo ID
      produtoData.value = produtos.find((p) => p.id === props.produto.id) || {};
    }
  } catch (error) {
    console.error('Erro ao buscar o produto:', error);
  } finally {
    isLoading.value = false;
  }
};

const getProfilePhotoUrl = (profilePhoto) => {
  if (!profilePhoto) {
    return '/storage/images/no_imagem.svg'; // Caminho para imagem padrão
  }
  return new URL(profilePhoto, window.location.origin).href;
};

// Monitora alterações no objeto produto
watch(
  () => props.produto,
  () => {
    fetchProduto();
  },
  { immediate: true } // Executa a busca ao montar o componente
);
</script>

<style scoped>
/* Estilizando a tabela */
table {
  width: 100%;
  margin-top: 20px;

  border-collapse: collapse;
}

th,
td {
  padding: 8px;
}

th {
  background-color: #164110;
  color: #f3f8f3;
  margin-bottom: 5px;
}

.TrRedonEsquerda {
  border-radius: 20px 0px 0px 0px;
}

.TrRedonDireita {
  border-radius: 0px 20px 0px 0px;
}

tr:nth-child(even) {
  background-color: #f4f5f3;
}

tr:hover {
  background-color: #dededea9;
  cursor: pointer;
}

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
</style>
