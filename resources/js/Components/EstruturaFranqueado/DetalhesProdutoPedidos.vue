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
              class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider"
            >
              Valor /
              {{ produto.unidadeDeMedida === 'unitario' ? 'Unidade' : 'Kg' }}
            </th>

            <th
              class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider rounded-tr-2xl"
            >
              QTD.Mínima
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
            <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
              {{
                formatarUnidade(produtoData.unidadeDeMedida, preco.qtd_minima)
              }}
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
      <label class="up w-full text-[#6d6d6d] text-[15px] font-semibold">
        {{ produto.unidadeDeMedida === 'unitario' ? 'Unitário' : 'A Granel' }}
      </label>
      <InputModel
        v-model="quantidade"
        :placeholder="produto.unidadeDeMedida === 'unitario' ? '0' : '0,000'"
      />
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

const adicionarAoCarrinho = () => {
  // Verifique se o fornecedor foi selecionado
  if (!fornecedorSelecionado.value) {
    toast.warning('Por favor, selecione o fornecedor!');
    return;
  }

  // Verifique se a quantidade é válida
  if (!quantidade.value || parseFloat(quantidade.value) <= 0) {
    toast.warning('É necessário informar uma quantidade válida (maior que 0)!');
    return;
  }

  // Verifique se o produtoData existe
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

  // Verifique a quantidade mínima para o fornecedor
  const qtdMinima = fornecedorPreco.qtd_minima;
  const quantidadeInformada = parseFloat(quantidade.value);

  // Tratar a quantidade mínima (convertendo para número, se necessário)
  const qtdMinimaNum =
    typeof qtdMinima === 'string'
      ? parseFloat(qtdMinima.replace(',', '.'))
      : qtdMinima;

  if (quantidadeInformada < qtdMinimaNum) {
    toast.warning(
      `A quantidade mínima para este fornecedor precisa ser acima de ${qtdMinimaNum}.`
    );
    return;
  }

  // Busca o nome do fornecedor no array de fornecedores
  const fornecedor = fornecedores.value.find(
    (f) => f.id === fornecedorSelecionado.value
  );

  // Preço do fornecedor
  const precoUnitario = fornecedorPreco ? fornecedorPreco.preco_unitario : 0;

  // Cria o item que será adicionado ao carrinho
  const itemCarrinho = {
    id: produtoData.value.id,
    nome: produtoData.value.nome,
    unidadeDeMedida: produtoData.value.unidadeDeMedida,
    fornecedorId: fornecedorSelecionado.value,
    nomeFornecedor: fornecedor
      ? fornecedor.razao_social
      : 'Fornecedor desconhecido',
    quantidade:
      produtoData.value.unidadeDeMedida === 'unitario'
        ? parseInt(quantidade.value) // Inteiro para unitário
        : parseFloat(quantidade.value).toFixed(3), // 3 casas para a granel
    preco: precoUnitario, // Preço do fornecedor
  };

  console.info('item adicionado', itemCarrinho);

  // Emite o item para ser adicionado ao carrinho
  emit('adicionarAoCarrinho', itemCarrinho);
  toast.success('Item adicionado a sua lista de pedido.');

  // Limpa o campo de quantidade após adicionar ao carrinho
  quantidade.value = '';

  // Bloqueia a seleção de fornecedor após o primeiro item ser adicionado
  fornecedorBloqueado.value = true;
};

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

const formatarParaReais = (valor) => {
  return Number(valor).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  });
};

const formatarUnidade = (unidadeDeMedida, valor) => {
  if (unidadeDeMedida === 'unitario') return parseInt(valor);
  if (unidadeDeMedida === 'a_granel')
    return parseFloat(valor).toLocaleString('pt-BR', {
      minimumFractionDigits: 3,
      maximumFractionDigits: 3,
    });

  return valor; // Retorna o valor original caso não seja reconhecido
};

// Função para formatar a unidade de medida
const formatarUnidades = (unidadeDeMedida, valor) => {
  if (!valor.trim()) return ''; // Permite apagar o campo sem forçar um valor padrão

  if (unidadeDeMedida === 'unitario') {
    return valor.replace(/\D/g, ''); // Apenas números inteiros
  }

  if (unidadeDeMedida === 'a_granel') {
    let quantidadeNumerica = valor.replace(/\D/g, ''); // Remove tudo que não for número

    if (!quantidadeNumerica) return ''; // Mantém vazio se não houver números

    let inteiro = quantidadeNumerica.slice(0, -3) || '0'; // Parte inteira
    let decimal = quantidadeNumerica.slice(-3).padStart(3, '0'); // Parte decimal

    return `${Number(inteiro).toLocaleString('pt-BR')},${decimal}`;
  }

  return valor; // Caso não se encaixe nas condições
};

const fetchProduto = async () => {
  isLoading.value = true;
  try {
    if (props.produto && props.produto.id) {
      const response = await axios.get(`/api/produtos/lista`);
      const produtosPorCategoria = response.data;

      // Transformar o objeto de categorias em um array de produtos
      const produtos = Object.values(produtosPorCategoria).flatMap(
        (categoria) => categoria.produtos
      ); // Pega os produtos de cada categoria

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

watch(quantidade, (novoValor) => {
  quantidade.value = formatarUnidades(props.produto.unidadeDeMedida, novoValor);
});
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
