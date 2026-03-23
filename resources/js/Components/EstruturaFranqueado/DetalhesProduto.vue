<template>
  <div class="elemento-fixo">
    <div
      v-if="!isEditMode"
      class="w-full bg-white rounded-[20px] p-8"
    >
      <div class="relative w-full h-full">
        <!-- Container das colunas -->
        <div class="flex items-center">
          <!-- Coluna da Imagem -->
          <div class="w-1/1 flex justify-center">
            <img
              :src="getProfilePhotoUrl(produto.profile_photo)"
              alt="Foto do Usuário"
              class="hidden w-20 h-20 rounded-md shadow-lg"
            />
          </div>

          <!-- Coluna do Nome -->
          <div class="w-2/3 pl-5">
            <div class="text-gray-700 text-xl font-bold font-['Figtree'] tracking-tight">
              {{ produto.nome || 'N/A' }}

              <div class="text-gray-600 text-md font-medium font-['Figtree'] mt-1 tracking-tight">
                R$ {{
                  produto.valor_total_lote
                }}
              </div>
            </div>
            <div class="quantidade text-gray-600 font-semibold mt-8 bg-gray-50 px-3 py-1 rounded-lg">
              {{ getQuantidadeTotal(produto.lotes) }}
              <span class="text-gray-300 text-xs">
                {{ produto.unidadeDeMedida === 'unitario' ? 'uni' : 'kg' }}
              </span>
            </div>
          </div>
        </div>
        <!-- Exibe o botão de edição apenas se uma unidade for selecionada -->
      </div>
    </div>
    <!-- Tabela de Lotes -->
    <div v-if="!isEditMode" class="">
      <div class="mt-8">
        <table class="min-w-full table-auto">
          <thead>
            <tr>
              <th
                class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider TrRedonEsquerda"
              >
                entrada
              </th>
              <th
                class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider"
              >
                Fornecedor
              </th>
              <th
                class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider"
              >
                qtd
              </th>
              <!-- Título da coluna, que muda dinamicamente -->

              <th
                class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider"
              >
                {{
                  produto.unidadeDeMedida === 'unitario' ? 'v. unit' : 'v. kg'
                }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(lote, index) in produto.lotes"
              :key="index"
              class="text-center"
            >
              <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
                {{ lote.data }}
              </td>
              <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
                {{ lote.fornecedor }}
              </td>
              <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
                {{ lote.quantidade }}
                {{ produto.unidadeDeMedida === 'unitario' ? 'uni' : 'kg' }}
              </td>

              <!-- Preço por Quilo ou Preço Unitário -->
              <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
                {{ lote.preco_insumo }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <EditarProduto
      v-if="isEditMode"
      ref="dadosProduto"
      :isVisible="isEditMode"
      :produto="produto"
      @dadosProduto="fetchProdutos"
      @cancelar="cancelEdit"
    />
  </div>
</template>

<script setup>
import { defineProps, ref } from 'vue';
import { defineEmits } from 'vue';

import EditarProduto from './EditarProduto.vue';
import ButtonEditeMedio from '../Button/ButtonEditeMedio.vue';

const props = defineProps({
  produto: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['atualizar']);

const showCadastroProduto = ref(false);

const isEditMode = ref(false);

const fetchProdutos = () => {
  const dadosProduto = ref.dadosProduto;
  dadosProduto.fetchProduto();
};

const getProfilePhotoUrl = (profilePhoto) => {
  if (!profilePhoto) {
    return '/storage/images/no_imagem.svg'; // Caminho para imagem padrão
  }
  return new URL(profilePhoto, window.location.origin).href;
};



const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value;
  showCadastroProduto.value = false;
};

const getQuantidadeTotal = (lotes) => {
  if (!Array.isArray(lotes)) {
    console.warn('getQuantidadeTotal recebeu um valor inválido:', lotes);
    return 0;
  }
  return lotes.reduce((total, lote) => total + lote.quantidade, 0);
};

const cancelEdit = () => {
  emit('atualizar');
  isEditMode.value = false;
  showCadastroProduto.value = true;
};
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
  padding: 12px;
}

th {
  background-color: #f3f8f3;
  color: #262a27;
  margin-bottom: 10px;
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
