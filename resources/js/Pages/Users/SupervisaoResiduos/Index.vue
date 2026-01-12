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
            <CalendarSimples2 @update-filters="handleFilterUpdate" />
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
              :class="{
                'font-bold text-[120.01px] tracking-wider': true,
                'text-[#1d5915]':
                  parseFormattedValue(aproveitamentoMedio) >= 70,
                'text-red-600': parseFormattedValue(aproveitamentoMedio) < 70,
              }"
            >
              {{ aproveitamentoMedio }}%
            </span>
            <svg
              class="w-[40px] h-[40px] ml-2"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <polygon
                :points="
                  parseFormattedValue(aproveitamentoMedio) >= 70
                    ? '12,2 22,20 2,20'
                    : '12,22 22,2 2,2'
                "
                :fill="
                  parseFormattedValue(aproveitamentoMedio) >= 70
                    ? '#6DB631'
                    : '#DC2626'
                "
              />
            </svg>
          </div>
          <div
            class="flex items-center gap-2 text-[#6d6d6d] text-[15px] font-semibold -mt-9"
          >
            <!-- <img
              src="/storage/images/trending_up.svg"
              alt="Filtro"
              class="w-5 h-5"
            /> -->
            <p>0% coletando informações</p>
          </div>
        </div>

        <!-- Coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="font-semibold text-[#6d6d6d] text-[15px] mb-2">
            Eficiência por colaborador
          </h3>
          <div class="flex flex-col gap-2 compromissos-container">
            <div
              v-for="(colaborador, index) in colaboradoresEficientes"
              :key="colaborador.responsavel_id"
              class="flex justify-between items-center bg-[#F5FAF4] px-5 py-2 rounded-lg hover:bg-gray-100 transition-all duration-300"
            >
              <div class="flex items-center gap-3">
                <p class="text-[14px] font-medium text-gray-900">
                  #{{ index + 1 }}
                </p>
                <img
                  :src="
                    colaborador.responsavel.profile_photo_url ||
                    '/storage/images/default-profile.png'
                  "
                  alt="Foto de perfil"
                  class="w-8 h-8 rounded-full object-cover border border-gray-300"
                />
                <p class="text-sm text-[#262a27]">
                  {{ colaborador.responsavel.name }}
                </p>
              </div>
              <div class="text-[17px] font-semibold text-gray-900">
                {{ colaborador.media_aproveitamento }}%
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
        <!-- Cabeçalho da tabela -->
        <div
          class="bg-[#164110] grid grid-cols-7 gap-4 py-4 px-6 text-xs font-semibold text-[#FFFFFF] uppercase tracking-wider rounded-tl-2xl rounded-tr-2xl"
        >
          <span
            @click="sortBy('aproveitamento')"
            class="px-5 cursor-pointer flex items-center gap-2"
          >
            Aproveitamento
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('calibre.nome')"
            class="cursor-pointer flex items-center gap-2"
          >
            Calibre
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('peso_bruto')"
            class="cursor-pointer flex items-center gap-2"
          >
            Peso Bruto
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('peso_limpo')"
            class="flex items-center gap-2 cursor-pointer"
          >
            Peso Limpo
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>

          <span
            @click="sortBy('desperdicio')"
            class="cursor-pointer flex items-center gap-2"
          >
            Desperdício
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('created_at')"
            class="cursor-pointer flex items-center gap-2"
          >
            Quando
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
          <span
            @click="sortBy('responsavel.name')"
            class="cursor-pointer flex items-center gap-2"
          >
            Responsável
            <img src="/storage/images/sync_alt.svg" class="w-[19px] h-[19px]" />
          </span>
        </div>

        <!-- Inputs de filtro (exibidos quando showFilters é true) -->
        <div
          v-if="showFilters"
          class="grid grid-cols-7 gap-4 py-2 px-6 bg-gray-50"
        >
          <input
            v-model="filters.aproveitamento"
            type="text"
            placeholder="Filtrar aproveitamento"
            class="p-2 border border-gray-300 rounded text-sm"
          />
          <input
            v-model="filters['calibre.nome']"
            type="text"
            placeholder="Filtrar calibre"
            class="p-2 border border-gray-300 rounded text-sm"
          />
          <input
            v-model="filters.peso_bruto"
            type="text"
            placeholder="Filtrar peso bruto"
            class="p-2 border border-gray-300 rounded text-sm"
          />
          <input
            v-model="filters.peso_limpo"
            type="text"
            placeholder="Filtrar peso limpo"
            class="p-2 border border-gray-300 rounded text-sm"
          />
          <input
            v-model="filters.desperdicio"
            type="text"
            placeholder="Filtrar desperdício"
            class="p-2 border border-gray-300 rounded text-sm"
          />
          <input
            v-model="filters.created_at"
            type="text"
            placeholder="Filtrar data"
            class="p-2 border border-gray-300 rounded text-sm"
          />
          <input
            v-model="filters['responsavel.name']"
            type="text"
            placeholder="Filtrar responsável"
            class="p-2 border border-gray-300 rounded text-sm"
          />
        </div>

        <!-- Dados da tabela -->
        <div class="overflow-y-auto max-h-96 scroll-hidden">
          <ul class="space-y-1">
            <li
              v-for="(item, index) in filteredHistoricoMovimentacoes"
              :key="index"
              class="grid grid-cols-7 gap-4 px-6 py-2 text-[16px]"
            >
              <span
                class="flex items-center justify-center text-gray-900 font-semibold"
              >
                <svg
                  class="w-[25px] h-[25px] mr-2"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <polygon
                    :points="
                      parseFormattedValue(item.aproveitamento) >= 70
                        ? '12,2 22,20 2,20'
                        : '12,22 22,2 2,2'
                    "
                    :fill="
                      parseFormattedValue(item.aproveitamento) >= 70
                        ? '#6DB631'
                        : '#DC2626'
                    "
                  />
                </svg>
                {{ item.aproveitamento }}
              </span>
              <span class="text-gray-900 font-semibold">
                {{ item.calibre.nome }}
              </span>
              <span class="text-gray-900 font-semibold">
                {{ item.peso_bruto }}
              </span>
              <span class="text-gray-900 font-semibold">
                {{ item.peso_limpo }}
              </span>
              <span class="text-gray-900 font-semibold">
                {{ item.desperdicio }}
              </span>
              <span class="text-gray-600 font-semibold">
                {{ item.created_at }}
              </span>
              <span class="text-gray-500 font-semibold">
                {{ item.responsavel.name }}
              </span>
            </li>
          </ul>
        </div>
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

const start_date = ref('');
const end_date = ref('');
const aproveitamentoMedio = ref(0);
const colaboradoresEficientes = ref([]);
const historicoMovimentacoes = ref([]);

const loading = ref(false);
const scrollContainer = ref(null);

// Variáveis para ordenação
const sortKey = ref(null);
const sortAsc = ref(true);
const showFilters = ref(false);

// Filtros para pesquisa
const filters = ref({
  aproveitamento: '',
  'calibre.nome': '',
  peso_limpo: '',
  peso_bruto: '',
  desperdicio: '',
  created_at: '',
  'responsavel.name': '',
});

// Função para converter strings formatadas em números
const parseFormattedValue = (value) => {
  if (!value || typeof value !== 'string') return 0;
  // Remove tudo exceto números, vírgula e ponto, substitui vírgula por ponto e converte para float
  return parseFloat(value.replace(/[^\d,.]/g, '').replace(',', '.')) || 0;
};

// Computed para retornar as movimentações filtradas e ordenadas
const filteredHistoricoMovimentacoes = computed(() => {
  return historicoMovimentacoes.value
    .filter((item) => {
      return (
        (filters.value.aproveitamento === '' ||
          String(item.aproveitamento).includes(filters.value.aproveitamento)) &&
        (filters.value['calibre.nome'] === '' ||
          item.calibre.nome
            .toLowerCase()
            .includes(filters.value['calibre.nome'].toLowerCase())) &&
        (filters.value.peso_limpo === '' ||
          String(item.peso_limpo).includes(filters.value.peso_limpo)) &&
        (filters.value.peso_bruto === '' ||
          String(item.peso_bruto).includes(filters.value.peso_bruto)) &&
        (filters.value.desperdicio === '' ||
          String(item.desperdicio).includes(filters.value.desperdicio)) &&
        (filters.value.created_at === '' ||
          item.created_at
            .toLowerCase()
            .includes(filters.value.created_at.toLowerCase())) &&
        (filters.value['responsavel.name'] === '' ||
          item.responsavel.name
            .toLowerCase()
            .includes(filters.value['responsavel.name'].toLowerCase()))
      );
    })
    .sort((a, b) => {
      if (sortKey.value) {
        const keyParts = sortKey.value.split('.');
        let aValue = a;
        let bValue = b;
        keyParts.forEach((part) => {
          aValue = aValue[part];
          bValue = bValue[part];
        });
        // Converte valores formatados em números para ordenação
        const aNum =
          typeof aValue === 'string' ? parseFormattedValue(aValue) : aValue;
        const bNum =
          typeof bValue === 'string' ? parseFormattedValue(bValue) : bValue;
        if (sortAsc.value) {
          return aNum > bNum ? 1 : -1;
        } else {
          return aNum < bNum ? 1 : -1;
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

const fetchHistoricoSalmao = async (startDate = null, endDate = null) => {
  try {
    let url = '/api/gestao-residuos/listar';
    const params = [];

    if (startDate) params.push(`start_date=${startDate}`);
    if (endDate) params.push(`end_date=${endDate}`);

    if (params.length > 0) {
      url += `?${params.join('&')}`;
    }

    const response = await axios.get(url);
    const data = response.data;

    aproveitamentoMedio.value = data.aproveitamento_medio;
    colaboradoresEficientes.value = data.colaboradores_eficientes;
    historicoMovimentacoes.value = data.historico;
  } catch (error) {
    console.error('Erro ao buscar histórico de salmão:', error);
  }
};

onMounted(() => {
  fetchHistoricoSalmao();
});

const handleFilterUpdate = (filters) => {
  fetchHistoricoSalmao(filters.startDate, filters.endDate);
};
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
  max-height: 170px; /* Defina a altura máxima desejada para a coluna */
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
