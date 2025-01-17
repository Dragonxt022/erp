<template>
  <div class="elemento-fixo">
    <div
      v-if="!isEditMode"
      class="w-full h-[200px] bg-white rounded-[20px] p-12"
    >
      <h3 class="text-xl font-bold mb-4 text-gray-500">
        Estou recebendo os itens de
      </h3>
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
                produto.unidadeDeMedida === 'unitario' ? 'Unitario' : 'A Granel'
              }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-if="produtoData.precos && produtoData.precos.length > 0"
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
  </div>
</template>

<script setup>
import { defineProps, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import LabelModel from '../Label/LabelModel.vue';

const props = defineProps({
  produto: {
    type: Object,
    required: true,
  },
});

const produtoData = ref({
  precos: [],
});

const isEditMode = ref(false);

const formatarParaReais = (valorEmCentavos) => {
  return (valorEmCentavos / 100).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  });
};

const fetchProduto = async () => {
  try {
    if (props.produto && props.produto.id) {
      const response = await axios.get(`/api/produtos/lista`);
      const produtos = response.data;

      // Encontra o produto pelo ID
      produtoData.value = produtos.find((p) => p.id === props.produto.id) || {};
    }
  } catch (error) {
    console.error('Erro ao buscar o produto:', error);
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
