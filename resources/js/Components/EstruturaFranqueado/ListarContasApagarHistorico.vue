<template>
  <div>
    <div class="search-container relative flex items-center w-full mb-4 mt-4">
      <div class="absolute left-3">
        <img
          src="/storage/images/search.svg"
          alt="Ícone de pesquisa"
          class="w-5 h-5 text-gray-500"
        />
      </div>
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Realizar uma busca..."
        class="search-input pl-10 w-full py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
        @input="onSearchInput"
      />
    </div>

    <div
      v-for="conta in filteredDados"
      :key="conta.id"
      @click="selecionarDados(conta)"
      class="flex justify-between items-center bg-white p-4 rounded-lg cursor-pointer hover:bg-gray-100 transition mt-3"
    >
      <div>
        <p class="text-lg font-medium text-gray-900">{{ conta.nome }}</p>
        <p class="text-sm text-gray-600">
          {{ conta.valor_formatado }} - Pago em
          {{ formatarData(conta.vencimento) }}
        </p>
      </div>

      <div>
        <img
          :src="getStatusIcon(conta.status)"
          :alt="conta.status"
          class="w-8 h-8"
        />
      </div>
    </div>

    <div v-if="!dados.length && !searchQuery" class="text-center text-gray-500 mt-8">
        Nenhuma despesa encontrada.
    </div>
    <div v-if="!filteredDados.length && searchQuery" class="text-center text-gray-500 mt-8">
        Nenhum resultado para a busca.
    </div>

    <div v-if="pagination.last_page > 1 && !searchQuery" class="flex justify-center mt-6 space-x-2">
      <button
        @click="goToPage(pagination.current_page - 1)"
        :disabled="pagination.current_page === 1"
        class="px-4 py-2 bg-green-500 text-white rounded-lg disabled:opacity-50"
      >
        Anterior
      </button>

      <button
        v-for="page in displayedPages"
        :key="page"
        @click="goToPage(page)"
        :class="{
          'px-4 py-2 rounded-lg': true,
          'bg-green-700 text-white': page === pagination.current_page,
          'bg-gray-200 text-gray-700 hover:bg-gray-300': page !== pagination.current_page
        }"
      >
        {{ page }}
      </button>

      <button
        @click="goToPage(pagination.current_page + 1)"
        :disabled="pagination.current_page === pagination.last_page"
        class="px-4 py-2 bg-green-500 text-white rounded-lg disabled:opacity-50"
      >
        Próximo
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, defineProps, watch } from 'vue';
import axios from 'axios';

const emit = defineEmits(['dado-selecionado']);

const props = defineProps({
  apiUrl: {
    type: String,
    required: true
  },
  title: {
    type: String,
    default: 'Histórico'
  },
  subtitle: {
    type: String,
    default: 'Visualize suas contas'
  }
});

const dados = ref([]);
const searchQuery = ref('');
const currentPage = ref(1);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  from: 1,
  to: 1,
  total: 0,
});

// Número máximo de botões de página a serem exibidos
const MAX_PAGE_BUTTONS = 5;

// Propriedade computada para determinar quais números de página exibir
const displayedPages = computed(() => {
  const pages = [];
  const startPage = Math.max(1, pagination.value.current_page - Math.floor(MAX_PAGE_BUTTONS / 2));
  const endPage = Math.min(pagination.value.last_page, startPage + MAX_PAGE_BUTTONS - 1);

  // Ajuste para garantir que sempre haja MAX_PAGE_BUTTONS se possível
  const adjustedStartPage = Math.max(1, endPage - MAX_PAGE_BUTTONS + 1);

  for (let i = adjustedStartPage; i <= endPage; i++) {
    pages.push(i);
  }
  return pages;
});


const fetchDados = async (page = 1) => {
  try {
    if (searchQuery.value) {
      const response = await axios.get(props.apiUrl);
      dados.value = response.data.data;
      pagination.value = { current_page: 1, last_page: 1, from: 1, to: dados.value.length, total: dados.value.length };
    } else {
      const response = await axios.get(`${props.apiUrl}?page=${page}`);
      dados.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        from: response.data.from,
        to: response.data.to,
        total: response.data.total,
      };
      currentPage.value = response.data.current_page;
    }
  } catch (error) {
    console.error('Erro ao carregar os dados:', error);
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchDados(page);
  }
};

onMounted(() => {
  fetchDados(currentPage.value);
});

const onSearchInput = () => {
    if (searchQuery.value) {
        fetchDados();
    } else {
        goToPage(1);
    }
};

const filteredDados = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return dados.value.filter(
    (conta) =>
      conta.nome.toLowerCase().includes(query) ||
      String(conta.valor).includes(query)
  );
});

const formatarData = (data) => {
  if (!data) return '';
  const partes = data.split('-');
  return `${partes[2]}/${partes[1]}/${partes[0]}`;
};

const getStatusIcon = (status) => {
  return status === 'pendente'
    ? '/storage/images/check_circle_laranja.svg'
    : '/storage/images/check_circle_verde.svg';
};
</script>

<style scoped>
/* Seu CSS existente */
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e;
  max-width: 600px;
}

/* ... (seu CSS adicional aqui) ... */
</style>
