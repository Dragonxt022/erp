<template>
  <LayoutFranqueadora>
    <Head title="Painel" />
    
    <!-- Header com título e filtros -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
      <div>
        <div class="painel-title">Visão geral da franquia</div>
        <div class="painel-subtitle">
          <p>Acompanhe o desempenho das unidades</p>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
        <!-- Seletor de Unidade -->
        <div class="w-full sm:w-64">
          <UnidadeSelectorDropdown :default-unit-id="selectedUnitId" @unit-selected="handleUnitChange" />
        </div>
        
        <!-- Filtro de Calendário -->
        <div class="text-[#262a27] text-[15px] font-semibold font-['Figtree'] leading-tight">
          <div class="flex items-center space-x-2">
            <img src="/storage/images/calendar_month.svg" alt="Filtro" class="w-5 h-5" />
            <span class="text-gray-900 text-[17px] font-semibold">
              <CalendarSimples2 :default-to-previous-month="true" @date-range-selected="handleDateChange" />
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
    </div>

    <!-- Conteúdo da página -->
    <div v-else class="mt-5">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Primeira coluna - Compromissos -->
        <div class="md:row-span-3">
          <div class="bg-white rounded-lg p-5 h-full">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">
              Compromissos Próximos
            </h3>
            <div class="compromissos-container flex flex-col gap-3">
              <div
                v-for="compromisso in compromissos"
                :key="compromisso.id"
                class="flex justify-between items-center bg-[#F5FAF4] p-3 rounded-lg hover:bg-gray-100 transition-all cursor-pointer"
              >
                <div class="flex flex-col flex-1">
                  <p class="text-[14px] font-medium text-gray-900">
                    {{ compromisso.nome }}
                  </p>
                  <p class="text-sm text-gray-600">
                    {{ compromisso.valor_formatado }} - Vence em {{ formatarData(compromisso.vencimento) }}
                  </p>
                </div>
                <div>
                  <img
                    :src="getStatusIcon(compromisso.status)"
                    :alt="'Status: ' + compromisso.status"
                    class="w-6 h-6 object-contain"
                  />
                </div>
              </div>

              <!-- Estado vazio -->
              <div v-if="compromissos.length === 0" class="text-center py-8 text-gray-500">
                <p>Nenhum compromisso próximo</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Segunda coluna - Faturamento -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Faturamento</h3>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ {{ faturamento }}
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <img
                :src="comparisons.faturamento.direction === 'up' ? '/storage/images/trending_up.svg' : comparisons.faturamento.direction === 'down' ? '/storage/images/trending_down.svg' : '/storage/images/trending_neutral.svg'"
                alt="Tendência"
              />
            </div>

            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              {{ comparisons.faturamento.formatted }}
              {{ comparisons.faturamento.direction === 'up' ? 'maior' : comparisons.faturamento.direction === 'down' ? 'menor' : 'igual' }}
              que no período anterior
            </div>
          </div>
        </div>

        <!-- Terceira coluna - CMV -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">CMV</h3>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ {{ cmv }}
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <img
                :src="comparisons.cmv.direction === 'up' ? '/storage/images/trending_up.svg' : comparisons.cmv.direction === 'down' ? '/storage/images/trending_down.svg' : '/storage/images/trending_neutral.svg'"
                alt="Tendência"
              />
            </div>

            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              {{ comparisons.cmv.formatted }}
              {{ comparisons.cmv.direction === 'up' ? 'maior' : comparisons.cmv.direction === 'down' ? 'menor' : 'igual' }}
              que no período anterior
            </div>
          </div>
        </div>

        <!-- Quarta coluna - Ticket Médio -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Ticket médio</h3>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ {{ ticketMedio }}
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <img
                :src="comparisons.ticket_medio.direction === 'up' ? '/storage/images/trending_up.svg' : comparisons.ticket_medio.direction === 'down' ? '/storage/images/trending_down.svg' : '/storage/images/trending_neutral.svg'"
                alt="Tendência"
              />
            </div>

            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              {{ comparisons.ticket_medio.formatted }}
              {{ comparisons.ticket_medio.direction === 'up' ? 'maior' : comparisons.ticket_medio.direction === 'down' ? 'menor' : 'igual' }}
              que no período anterior
            </div>
          </div>
        </div>

        <!-- Quinta coluna - Pedidos -->
        <div class="bg-white rounded-lg p-4">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Pedidos</h3>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            {{ quantidadePedidos }}
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <img
                :src="comparisons.pedidos.direction === 'up' ? '/storage/images/trending_up.svg' : comparisons.pedidos.direction === 'down' ? '/storage/images/trending_down.svg' : '/storage/images/trending_neutral.svg'"
                alt="Tendência"
              />
            </div>

            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              {{ comparisons.pedidos.formatted }}
              {{ comparisons.pedidos.direction === 'up' ? 'maior' : comparisons.pedidos.direction === 'down' ? 'menor' : 'igual' }}
              que no período anterior
            </div>
          </div>
        </div>

        <!-- Gráfico de Faturamento Diário -->
        <div class="bg-white rounded-lg p-4 col-span-1 md:col-span-3">
          <h3 class="text-lg font-semibold text-gray-700">
            Faturamento diário
          </h3>
          <div class="w-full h-[220px]">
            <canvas ref="barChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </LayoutFranqueadora>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { Chart, registerables } from 'chart.js';
import LayoutFranqueadora from '@/Layouts/LayoutFranqueadora.vue';
import UnidadeSelectorDropdown from '@/Components/Filtros/UnidadeSelectorDropdown.vue';
import CalendarSimples2 from '@/Components/Filtros/CalendarSimples2.vue';
import axios from 'axios';

// Registrar todos os componentes necessários do Chart.js
Chart.register(...registerables);

// Referências
const barChart = ref(null);
const chartInstance = ref(null);
const loading = ref(false);

const page = usePage();

// Filtros
const selectedUnitId = ref(page.props.auth.user.unidade_id); // Inicia com a unidade do logado
const startDate = ref(null);
const endDate = ref(null);

// Dados dos indicadores
const faturamento = ref('0,00');
const cmv = ref('0,00');
const ticketMedio = ref('0,00');
const quantidadePedidos = ref(0);
const compromissos = ref([]);

// Comparações com período anterior
const comparisons = ref({
  faturamento: { percentage: 0, direction: 'neutral', formatted: '0%' },
  cmv: { percentage: 0, direction: 'neutral', formatted: '0%' },
  ticket_medio: { percentage: 0, direction: 'neutral', formatted: '0%' },
  pedidos: { percentage: 0, direction: 'neutral', formatted: '0%' },
});

// Dados do gráfico
const faturamentoDias = ref([]);
const diasLabels = ref([]);

// Handlers
const handleUnitChange = (unitId) => {
  selectedUnitId.value = unitId;
  fetchAllData();
};

const handleDateChange = (dateRange) => {
  startDate.value = dateRange.start_date;
  endDate.value = dateRange.end_date;
  fetchAllData();
};

// Buscar todos os dados
const fetchAllData = async () => {
  loading.value = true;
  try {
    await Promise.all([
      fetchIndicadores(),
      fetchFaturamentoDiario(),
      fetchCompromissos(),
    ]);
  } catch (error) {
    console.error('Erro ao buscar dados:', error);
  } finally {
    loading.value = false;
  }
};

// Buscar indicadores
const fetchIndicadores = async () => {
  try {
    const params = new URLSearchParams();
    if (selectedUnitId.value !== null) params.append('unidade_id', selectedUnitId.value);
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);

    const response = await axios.get(`/api/admin/painel/indicadores?${params.toString()}`);
    const data = response.data;

    faturamento.value = data.faturamento || '0,00';
    cmv.value = data.cmv || '0,00';
    ticketMedio.value = data.ticket_medio || '0,00';
    quantidadePedidos.value = data.quantidade_pedidos || 0;
    comparisons.value = data.comparisons || comparisons.value;
  } catch (error) {
    console.error('Erro ao buscar indicadores:', error);
  }
};

// Buscar faturamento diário
const fetchFaturamentoDiario = async () => {
  try {
    const params = new URLSearchParams();
    if (selectedUnitId.value !== null) params.append('unidade_id', selectedUnitId.value);

    const response = await axios.get(`/api/admin/painel/faturamento-diario?${params.toString()}`);
    const data = response.data;

    if (data.faturamento && Array.isArray(data.faturamento)) {
      diasLabels.value = data.faturamento.map((item) => item.dia);
      faturamentoDias.value = data.faturamento.map((item) => parseFloat(item.total));
      updateChart();
    }
  } catch (error) {
    console.error('Erro ao buscar faturamento diário:', error);
  }
};

// Buscar compromissos
const fetchCompromissos = async () => {
  try {
    const params = new URLSearchParams();
    if (selectedUnitId.value !== null) params.append('unidade_id', selectedUnitId.value);

    const response = await axios.get(`/api/admin/painel/compromissos?${params.toString()}`);
    compromissos.value = response.data.compromissos || [];
  } catch (error) {
    console.error('Erro ao buscar compromissos:', error);
  }
};

// Atualizar gráfico
const updateChart = () => {
  if (chartInstance.value) {
    chartInstance.value.destroy();
  }

  if (!barChart.value) return;

  chartInstance.value = new Chart(barChart.value.getContext('2d'), {
    type: 'bar',
    data: {
      labels: diasLabels.value,
      datasets: [
        {
          label: 'Faturamento Diário',
          data: faturamentoDias.value,
          backgroundColor: 'rgba(75, 192, 75, 0.6)',
          borderColor: 'rgba(75, 192, 75, 1)',
          borderWidth: 2,
          borderRadius: 8,
          borderSkipped: false,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
        },
        tooltip: {
          enabled: true,
          callbacks: {
            label: function (context) {
              return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            },
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return value >= 1000 ? `${value / 1000}k` : value;
            },
          },
        },
      },
    },
  });
};

// Formatar data
const formatarData = (data) => {
  const partes = data.split('-');
  return `${partes[2]}/${partes[1]}/${partes[0]}`;
};

// Ícones de status
const statusIcons = {
  pendente: '/storage/images/check_circle_laranja.svg',
  pago: '/storage/images/check_circle_verde.svg',
  agendada: '/storage/images/agendada.svg',
  atrasado: '/storage/images/atrasada.svg',
};

const getStatusIcon = (status) => {
  return statusIcons[status] || '/storage/images/check_circle_laranja.svg';
};

// Lifecycle
onMounted(() => {
  fetchAllData();
});

onUnmounted(() => {
  if (chartInstance.value) {
    chartInstance.value.destroy();
  }
});
</script>

<style lang="css" scoped>
.painel-title {
  font-size: 34px;
  line-height: 40px;
  font-weight: 700;
  color: #262a27;
  margin-bottom: 10px;
}

.painel-subtitle {
  font-size: 17px;
  line-height: 25px;
  color: #6d6d6e;
  max-width: 600px;
}

.compromissos-container {
  max-height: 500px;
  overflow-y: auto;
}

/* Esconde a barra de rolagem */
.compromissos-container::-webkit-scrollbar {
  display: none;
}

.compromissos-container {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
