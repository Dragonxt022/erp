<template>
  <LayoutFranqueado>
    <Head title="Controle de estoque" />
    <div class="flex justify-between items-center mb-4">
      <!-- Coluna 1: Título e subtítulo -->
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
          Controle de estoque
        </div>
      </div>

      <!-- Coluna 2: Data -->
      <div
        class="text-[#262a27] text-[15px] font-semibold font-['Figtree'] leading-tight"
      >
        <div class="flex items-center space-x-2">
          <img
            src="/storage/images/calendar_month.svg"
            alt="Filtro"
            class="w-5 h-5"
          />
          
          <!-- Ajuste o tamanho do ícone conforme necessário -->
          <span class="text-gray-900 text-[17px] font-semibold">
            <CalendarSimples2 @update-filters="handleFilterUpdate" />
          </span>
        </div>
      </div>
    </div>

    <a href="https://forms.gle/8nkNhrqCG5UHXLAQ9" target="_blank" class="w-full rounded-xl">
      <img src="/storage/images/banner-estoque.png" alt="Banner Estoque" class="w-full h-auto rounded-xl shadow-xl hover:shadow-none transition-all duration-300" />
    </a>

    <div class="mt-5">
      <!-- Ajuste do grid para ser responsivo -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <!-- Coluna -->
        <div class="bg-white rounded-lg p-8 border border-gray-200 shadow-md">
          <h3 class="text-lg sm:text-xl md:text-lg font-semibold text-gray-500">
            Valor inicial
          </h3>
          <div
            class="text-[#262a27] text-[40px] sm:text-[30px] md:text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ {{ saldoEstoqueInicial }}
          </div>
        </div>

        <!-- Coluna -->
        <div class="bg-white rounded-lg p-8 border border-gray-200 shadow-md">
          <h3 class="text-lg sm:text-xl md:text-lg font-semibold text-gray-500">
            Estoque atual
          </h3>
          <div
            class="text-[#262a27] text-[40px] sm:text-[30px] md:text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ {{ saldoEstoqueFinal }}
          </div>
        </div>
      </div>
    </div>

    <!-- Histórico de Movimentações -->
    <div class="mt-8">
      <div class="flex items-center justify-between mb-4">
        <h3
          class="text-[#262a27] text-[17px] mb-[-13px] font-semibold font-['Figtree'] leading-snug"
        >
          Histórico de Movimentações
        </h3>
        <div
          class="flex items-center space-x-2 cursor-pointer"
          @click="toggleFilters"
        >
          <img
            src="/storage/images/filter_alt.svg"
            alt="Filtro"
            class="w-5 h-5"
          />
          <span class="text-gray-900 text-[17px] font-semibold">
            Filtrar resultados
          </span>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow">
        <!-- Cabeçalho da lista -->
        <div
          class="bg-[#164110] grid grid-cols-7 gap-4 py-4 px-6 text-xs font-semibold text-[#FFFFFF] uppercase tracking-wider rounded-tl-2xl rounded-tr-2xl"
        >
          <span
            @click="sortBy('operacao')"
            class="px-5 cursor-pointer flex items-center gap-2"
          >
            OPERAÇÃO
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('quantidade')"
            class="flex items-center gap-2 cursor-pointer"
          >
            QTD.
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('item')"
            class="cursor-pointer flex items-center gap-2"
          >
            Item
            <img
              src="/storage/images/sync_alt.svg"
              class="w-[19px] h-[19px] cursor-pointer"
            />
          </span>
          <span
            @click="sortBy('preco_unitario')"
            class="cursor-pointer flex items-center gap-2"
          >
            Valor Unit.
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('valor_total')"
            class="cursor-pointer flex items-center gap-2"
          >
            Valor Total
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('quanto')"
            class="cursor-pointer flex items-center gap-2"
          >
            Quando
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('responsavel')"
            class="cursor-pointer flex items-center gap-2"
          >
            Responsável
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
        </div>

        <!-- Filtros (apenas exibidos se ativados) -->
        <div
          v-if="showFilters"
          class="grid grid-cols-7 gap-4 py-2 px-6 bg-gray-50"
        >
          <input
            v-model="filters.operacao"
            type="text"
            placeholder="Filtrar por operação"
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.quantidade"
            type="text"
            placeholder="Filtrar por qtd."
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.item"
            type="text"
            placeholder="Filtrar por item"
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.preco_unitario"
            type="text"
            placeholder="Filtrar por valor unit."
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.valor_total"
            type="text"
            placeholder="Filtrar por valor total"
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.quando"
            type="text"
            placeholder="Filtrar por quando"
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.responsavel"
            type="text"
            placeholder="Filtrar por responsável"
            class="p-2 border border-gray-300 rounded"
          />
        </div>

        <!-- Dados da lista rolável -->
        <div
          ref="scrollContainer"
          class="overflow-y-auto max-h-96 scroll-hidden"
        >
          <ul class="space-y-1">
            <li
              v-for="(movimentacao, index) in filteredHistoricoMovimentacoes"
              :key="index"
              class="hover:bg-gray-200 grid grid-cols-7 gap-1 px-6 py-2 text-[16px]"
            >
              <span class="flex items-center text-gray-900 font-semibold">
                <img
                  :src="statusMap[movimentacao.operacao].icon"
                  alt="icon indicativo"
                  class="mr-3 w-5 h-5"
                />
                {{ statusMap[movimentacao.operacao].label }}
              </span>

              <span class="text-gray-900 font-semibold">
                {{ movimentacao.quantidade }}
                {{ movimentacao.unidade === 'unidade' ? 'uni' : 'kg' }}
              </span>
              <span class="text-gray-900 font-medium">
                {{ movimentacao.item }}
              </span>
              <span class="text-gray-900 font-semibold">
                R$ {{ formatCurrency(movimentacao.preco_unitario) }}
              </span>
              <span class="text-gray-900 font-semibold">
                R$ {{ formatCurrency(movimentacao.valor_total) }}
              </span>
              <span class="text-gray-600 font-semibold">
                {{ movimentacao.data }}
              </span>
              <span class="text-gray-500 font-semibold">
                {{ movimentacao.responsavel }}
              </span>
            </li>
          </ul>
        </div>

        <!-- Carregando... -->
        <div v-if="loading" class="text-center mt-4">Carregando...</div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
import CalendarSimples2 from '@/Components/Filtros/CalendarSimples2.vue';

// Dados CMV
// Dados do CMV
const start_date = ref('0,00');
const end_date = ref('0,00');
const saldoEstoqueInicial = ref('0,00');
const entradasDurantePeriodo = ref('0,00');
const saldoEstoqueFinal = ref('0,00');
const cmv = ref('0,00');

const historicoMovimentacoes = ref([]);

const loading = ref(false);
const scrollContainer = ref(null);

// Variáveis para ordenação
const sortKey = ref(null);
const sortAsc = ref(true);
const showFilters = ref(false);

// Filtros para pesquisa
const filters = ref({
  operacao: '',
  quantidade: '',
  quando: '',
  item: '',
  preco_unitario: '',
  valor_total: '',
  responsavel: '',
});

const statusMap = {
  Entrada: {
    icon: '/storage/images/arrow_back_verde.svg',
    label: 'Entrada',
  },
  Retirada: {
    icon: '/storage/images/arrow_back_red.svg',
    label: 'Retirada',
  },
  Saida: {
    icon: '/storage/images/arrow_back_red.svg',
    label: 'Saída',
  },
  Ajuste: {
    icon: '/storage/images/icon_ajustes.svg',
    label: 'Ajuste',
  },
  'Ajuste - Exclusão': {
    icon: '/storage/images/arrow_back_red.svg',
    label: 'Ajuste - Exclusão',
  },
  'Ajuste - Adição': {
    icon: '/storage/images/arrow_back_verde.svg',
    label: 'Ajuste - Adição',
  },
  'Ajuste - Redução': {
    icon: '/storage/images/arrow_back_red.svg',
    label: 'Ajuste - Redução',
  },
};

// Computed para retornar as movimentações filtradas
const filteredHistoricoMovimentacoes = computed(() => {
  return historicoMovimentacoes.value
    .filter((mov) => {
      return (
        (filters.value.operacao === '' ||
          mov.operacao
            .toLowerCase()
            .includes(filters.value.operacao.toLowerCase())) &&
        (filters.value.quantidade === '' ||
          String(mov.quantidade).includes(filters.value.quantidade)) &&
        (filters.value.item === '' ||
          mov.item.toLowerCase().includes(filters.value.item.toLowerCase())) &&
        (filters.value.preco_unitario === '' ||
          String(mov.preco_unitario).includes(filters.value.preco_unitario)) &&
        (filters.value.valor_total === '' ||
          String(mov.valor_total).includes(filters.value.valor_total)) &&
        (filters.value.responsavel === '' ||
          mov.responsavel
            .toLowerCase()
            .includes(filters.value.responsavel.toLowerCase())) &&
        (filters.value.quando === '' ||
          mov.data.toLowerCase().includes(filters.value.quando.toLowerCase()))
      );
    })
    .sort((a, b) => {
      if (sortKey.value) {
        if (sortAsc.value) {
          return a[sortKey.value] > b[sortKey.value] ? 1 : -1;
        } else {
          return a[sortKey.value] < b[sortKey.value] ? 1 : -1;
        }
      }
      return 0;
    });
});

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortAsc.value = !sortAsc.value;
  } else {
    sortKey.value = key;
    sortAsc.value = true;
  }
};

const toggleFilters = () => {
  showFilters.value = !showFilters.value;
};

// Função que lida com a atualização dos filtros
const handleFilterUpdate = (filters) => {
  console.log('Filtros atualizados:', filters);

  // Aqui você pode fazer a requisição com os novos filtros
  fetchDataCMV(filters.startDate, filters.endDate);
};

const fetchDataCMV = async (startDate = null, endDate = null) => {
  try {
    // Monta a URL condicionalmente
    let url = `/api/estoque/incial`;
    const params = [];

    if (startDate) params.push(`start_date=${startDate}`);
    if (endDate) params.push(`end_date=${endDate}`);

    if (params.length > 0) {
      url += `?${params.join('&')}`;
    }

    // Faz a requisição para a API
    const response = await axios.get(url);
    const data = response.data;

    // Se a resposta contiver os dados analíticos, atualiza os refs
    if (data.saldo_estoque_inicial) {
      historicoMovimentacoes.value = Array.isArray(data.historicoMovimentacoes)
        ? data.historicoMovimentacoes
        : Object.values(data.historicoMovimentacoes);
      start_date.value = data.start_date || 'não informado';
      end_date.value = data.end_date || 'não informado';
      saldoEstoqueInicial.value = data.saldo_estoque_inicial || '0,00';
      entradasDurantePeriodo.value = data.entradas_durante_periodo || '0,00';
      saldoEstoqueFinal.value = data.itensNoEstoque || '0,00';
      cmv.value = data.cmv || '0,00';
    } else {
      console.warn('Dados analíticos não encontrados.');
    }
  } catch (error) {
    console.error('Erro ao buscar dados analíticos:', error);
  }
};

// Função para formatar valores monetários
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '0,00';
  return Number(value).toFixed(2).replace('.', ',');
};

// Carrega os dados iniciais
onMounted(() => {
  fetchDataCMV();
});
</script>

<style lang="css" scoped>
.scroll-hidden {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE 10+ */
}

.scroll-hidden::-webkit-scrollbar {
  display: none; /* Chrome, Safari, Edge */
}

.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  line-height: 80%;
}

.painel-subtitle {
  font-size: 17px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}
/* Estilizando a tabela */

th {
  background-color: #164110;
  color: #ffffff;
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
}
</style>
