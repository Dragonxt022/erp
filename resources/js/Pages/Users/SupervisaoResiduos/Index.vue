<template>
  <LayoutFranqueado>
    <Head title="Controle de Resíduos" />
    <div class="flex justify-between items-center mb-4">
      <!-- Coluna 1: Título e subtítulo -->
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
          Controle de Resíduos
        </div>
        <div class="painel-subtitle">
          <p class="text-sm sm:text-base md:text-lg">
            Acompanhe o aproveitamento de insumos
          </p>
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
            <CalendarSimples @update-filters="handleFilterUpdate" />
          </span>
        </div>
      </div>
    </div>
    <div class="mt-5">
      <!-- Ajuste do grid para ser responsivo -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4">
        <!-- Coluna -->
        <div class="bg-white rounded-lg p-7">
          <p class="font-semibold text-[#6d6d6d] text-[15px] leading-none">
            Aproveitamento médio
          </p>
          <div class="flex items-center -mt-9">
            <span
              class="font-bold text-[120.01px] text-[#1d5915] tracking-wider"
            >
              72%
            </span>
            <svg
              class="w-[40px] h-[40px] ml-2"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <polygon points="12,2 22,20 2,20" fill="#6DB631" />
            </svg>
          </div>
          <div
            class="flex items-center gap-2 text-[#6d6d6d] text-[15px] font-semibold -mt-9"
          >
            <img
              src="/storage/images/trending_up.svg"
              alt="Filtro"
              class="w-5 h-5"
            />
            <p>0% coletando informações</p>
          </div>
        </div>

        <!-- Coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="font-semibold text-[#6d6d6d] text-[15px] mb-2">
            Eficiência por colaborador
          </h3>
          <div
            class="text-[#262a27] text-[40px] sm:text-[30px] md:text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            <div
              class="compromissos-container flex flex-col gap-2 overflow-hidden"
            >
              <!-- Container de compromissos -->
              <div
                class="flex justify-between items-center bg-[#F5FAF4] px-5 rounded-lg cursor-pointer hover:bg-gray-100 transition-all ease-in-out duration-300"
              >
                <!-- Informações da conta -->
                <div class="flex items-center gap-2">
                  <p class="text-[14px] font-medium text-gray-900">#1</p>
                  <p class="text-sm text-[#262a27]">Nome do usuário</p>
                </div>

                <!-- Ícone de status -->
                <div
                  class="text-[17px] object-contain transition-transform transform hover:scale-105"
                >
                  72%
                </div>
              </div>
            </div>
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
            @click="sortBy('aproveitamento')"
            class="px-5 cursor-pointer flex items-center gap-2"
          >
            APROVEITAMENTO
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('calibre')"
            class="cursor-pointer flex items-center gap-2"
          >
            Calibre
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('peso_lipo')"
            class="flex items-center gap-2 cursor-pointer"
          >
            peso limpo
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('peso_bruto')"
            class="cursor-pointer flex items-center gap-2"
          >
            peso bruto
            <img
              src="/storage/images/sync_alt.svg"
              class="w-[19px] h-[19px] cursor-pointer"
            />
          </span>
          <span
            @click="sortBy('desperdicio')"
            class="cursor-pointer flex items-center gap-2"
          >
            desperdício
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
            v-model="filters.aproveitamento"
            type="text"
            placeholder="Filtrar"
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.calibre"
            type="text"
            placeholder="Filtrar"
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.peso_lipo"
            type="text"
            placeholder="Filtrar"
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.peso_bruto"
            type="text"
            placeholder="Filtrar"
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.desperdicio"
            type="text"
            placeholder="Filtrar"
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.quando"
            type="text"
            placeholder="Filtrar"
            class="p-2 border border-gray-300 rounded"
          />
          <input
            v-model="filters.responsavel"
            type="text"
            placeholder="Filtrar"
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
              class="hover:bg-gray-200 grid grid-cols-5 gap-1 px-6 py-2 text-[16px]"
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
import CalendarSimples from '@/Components/Filtros/CalendarSimples.vue';

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
  Ajuste: {
    icon: '/storage/images/icon_ajustes.svg',
    label: 'Ajuste',
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
      saldoEstoqueFinal.value = data.saldo_estoque_final || '0,00';
      cmv.value = data.cmv || '0,00';
    } else {
      console.warn('Dados analíticos não encontrados.');
    }
  } catch (error) {
    console.error('Erro ao buscar dados analíticos:', error);
  }
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

.compromissos-container {
  max-height: 400px; /* Defina a altura máxima desejada para a coluna */
  overflow-y: auto; /* Habilita rolagem vertical */
}

/* Esconde a barra de rolagem */
.compromissos-container::-webkit-scrollbar {
  display: none; /* Esconde a barra de rolagem no Chrome, Safari, e Edge */
}

.compromissos-container {
  -ms-overflow-style: none; /* Esconde a barra de rolagem no Internet Explorer */
  scrollbar-width: none; /* Esconde a barra de rolagem no Firefox */
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
