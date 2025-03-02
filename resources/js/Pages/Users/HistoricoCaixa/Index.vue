<template>
  <LayoutFranqueado>
    <Head title="Histórico de Caixa" />
    <div class="flex justify-between items-center mb-4">
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
          Histórico de Caixa
        </div>
        <div class="painel-subtitle">
          <p class="text-sm sm:text-base md:text-lg">
            Acompanhe seu negócio em tempo real
          </p>
        </div>
      </div>

      <div
        class="text-[#262a27] text-[15px] font-semibold font-['Figtree'] leading-tight"
      >
        <div class="flex items-center space-x-2">
          <img
            src="/storage/images/calendar_month.svg"
            alt="Filtro"
            class="w-5 h-5"
          />
          <span class="text-gray-900 text-[17px] font-semibold">
            <CalendarFilterDia @update-filters="handleFilterUpdate" />
          </span>
        </div>
      </div>
    </div>

    <div class="mt-5">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6">
        <!-- Coluna 1: Métodos de Pagamento -->
        <div>
          <div class="bg-white rounded-lg px-12 py-8">
            <table class="w-full">
              <thead>
                <tr>
                  <th
                    class="text-gray-500 text-left text-sm sm:text-base md:text-lg font-semibold font-['Figtree'] leading-snug"
                  >
                    Método de pagamento
                  </th>
                  <th
                    class="text-gray-500 text-right text-sm sm:text-base md:text-lg font-semibold font-['Figtree'] leading-snug"
                  >
                    Valor
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="metodo in metodosPagamento" :key="metodo.nome">
                  <td class="py-3 flex items-center gap-5">
                    <img
                      v-if="metodo.img_icon"
                      :src="`/${metodo.img_icon}`"
                      :alt="metodo.nome"
                      class="w-8 h-8"
                    />
                    <div
                      class="text-[#262a27] text-[17px] font-semibold font-['Figtree'] leading-snug"
                    >
                      {{ metodo.nome }}
                    </div>
                  </td>
                  <td class="py-3 text-right text-[#262a27] font-semibold">
                    {{ metodo.valor }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div
            class="w-full h-[60px] bg-[#d2fac3] rounded-bl-[10px] rounded-br-[10px] px-12 flex justify-between items-center"
          >
            <div
              class="text-[#1d5915] text-xl font-bold font-['Figtree'] leading-snug"
            >
              TOTAL
            </div>
            <div
              class="text-[#1d5915] text-xl font-bold font-['Figtree'] leading-snug"
            >
              {{ total }}
            </div>
          </div>
        </div>

        <!-- Coluna 2: Gráfico com Seletor -->
        <div>
          <div class="bg-white rounded-lg px-12 py-8">
            <div class="mb-4 text-center">
              <label for="chartType" class="text-gray-700 font-semibold mr-2">
                Tipo de Gráfico:
              </label>
              <select
                v-model="chartType"
                @change="renderGrafico"
                id="chartType"
                class="border rounded px-2 py-1"
              >
                <option value="pie">Pizza (Pie)</option>
                <option value="doughnut">Rosquinha (Doughnut)</option>
                <option value="bar">Barras (Bar)</option>
                <option value="line">Linha (Line)</option>
                <option value="radar">Radar</option>
                <option value="polarArea">Área Polar (Polar Area)</option>
              </select>
            </div>
            <canvas id="myChart"></canvas> 
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
        <div class="flex items-center space-x-2">
          <img
            src="/storage/images/filter_alt.svg"
            alt="Filtro"
            class="w-5 h-5"
          />
        </div>
      </div>

      <div class="bg-white rounded-lg shadow">
        <div
          class="bg-[#164110] grid grid-cols-5 gap-4 py-4 px-6 text-xs font-semibold text-[#FFFFFF] uppercase tracking-wider rounded-tl-2xl rounded-tr-2xl"
        >
          <span class="text-left px-5">OPERAÇÃO</span>
          <span class="text-center">VALOR</span>
          <span class="text-left">HORA</span>
          <span class="text-center">MOTIVO</span>
          <span class="text-center">RESPONSÁVEL</span>
        </div>

        <div
          ref="scrollContainer"
          class="overflow-y-auto max-h-96 scroll-hidden"
        >
          <ul class="space-y-2">
            <li
              v-for="movimentacao in historico"
              :key="movimentacao.hora"
              class="hover:bg-gray-200 grid grid-cols-5 gap-2 px-6 py-3 text-[16px]"
            >
              <span class="flex items-center text-gray-900 font-semibold">
                <img
                  :src="getIconByStatus(movimentacao.operacao)"
                  alt="icon"
                  class="mr-3 w-5 h-5"
                />
                <span>{{ getStatusText(movimentacao.operacao) }}</span>
              </span>
              <span class="text-center text-gray-900 font-semibold">
                {{ movimentacao.valor }}
              </span>
              <span class="text-left text-gray-900 font-medium">
                {{ movimentacao.hora }}
              </span>
              <span class="text-center text-gray-600 font-semibold">
                {{ movimentacao.motivo }}
              </span>
              <span class="text-center text-gray-500 font-semibold">
                {{ movimentacao.responsavel }}
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
import {
  Chart,
  ArcElement,
  Tooltip,
  Legend,
  DoughnutController,
  PieController,
  BarController,
  LineController,
  RadarController,
  PolarAreaController,
  LineElement,
  BarElement,
  PointElement,
  RadialLinearScale,
  LinearScale,
  CategoryScale,
  Filler,
} from 'chart.js';

import ChartDataLabels from 'chartjs-plugin-datalabels'; // Certifique-se de importar o plugin
import CalendarFilterDia from '@/Components/Filtros/CalendarFilterDia.vue';

// Registrar os controladores necessários
Chart.register(
  ArcElement,
  Tooltip,
  Legend,
  DoughnutController,
  PieController,
  BarController,
  LineController,
  RadarController,
  PolarAreaController,
  LineElement,
  BarElement,
  PointElement,
  RadialLinearScale,
  LinearScale,
  CategoryScale,
  Filler,
  ChartDataLabels
);

const metodosPagamento = ref([]);
const total = ref('');
const historico = ref([]);
const graficoData = ref([]);
const graficoLabels = ref([]);
const graficoPorcentagem = ref([]);

// Nova variável reativa para o tipo de gráfico
const chartType = ref('pie');
let myChart = null;

// Função que retorna o ícone correto com base no status
const getIconByStatus = (status) => {
  switch (status) {
    case 'abertura':
    case 'suprimento':
      return '/storage/images/arrow_back_verde.svg';
    case 'fechamento':
      return '/storage/images/thumb_up.svg';
    case 'sangria':
      return '/storage/images/arrow_back_red.svg';
    default:
      return ''; // Se o status não for reconhecido
  }
};

// Função que retorna o texto correto com base no status
const getStatusText = (status) => {
  switch (status) {
    case 'abertura':
      return 'Abertura';
    case 'fechamento':
      return 'Fechamento';
    case 'suprimento':
      return 'Suprimento';
    case 'sangria':
      return 'Sangria';
    default:
      return ''; // Se o status não for reconhecido
  }
};

// Função que lida com a atualização dos filtros
const handleFilterUpdate = (filters) => {
  console.log('Filtros atualizados:', filters);

  // Aqui você pode fazer a requisição com os novos filtros
  fetchData(filters.startDate, filters.endDate, filters.periodo);
};

const fetchData = async (startDate, endDate, periodo) => {
  try {
    const response = await axios.get(
      '/api/analyticos/lista-metodos-pagamentos',
      {
        params: {
          start_date: startDate,
          end_date: endDate,
          periodo: periodo || 'total',
        },
      }
    );

    const data = response.data;

    metodosPagamento.value = data.metodos;
    historico.value = data.historico;
    total.value = data.total;

    // Preparando os dados do gráfico
    graficoLabels.value = data.grafico.labels;
    graficoData.value = data.grafico.data;
    graficoPorcentagem.value = data.grafico.porcentagem;

    renderGrafico();
  } catch (error) {
    console.error('Erro ao buscar dados:', error);
  }
};

const renderGrafico = () => {
  const ctx = document.getElementById('myChart').getContext('2d');

  if (myChart) {
    myChart.destroy();
  }

  // Configuração básica comum a todos os gráficos
  const baseConfig = {
    type: chartType.value,
    data: {
      labels: graficoLabels.value,
      datasets: [
        {
          data: graficoData.value,
          backgroundColor: [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#F7464A',
            '#FF7F50',
          ],
          borderColor: chartType.value === 'line' ? '#36A2EB' : undefined,
          borderWidth:
            chartType.value === 'line' || chartType.value === 'bar' ? 1 : 0,
          fill: chartType.value === 'line' ? true : false,
        },
      ],
    },
    options: {
      plugins: {
        tooltip: {
          callbacks: {
            label: function (tooltipItem) {
              const valorFormatado = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL',
              }).format(tooltipItem.raw);
              return tooltipItem.label + ': ' + valorFormatado;
            },
          },
        },
        legend: {
          position: 'bottom',
          labels: {
            usePointStyle: true,
            padding: 15,
            boxWidth: 10,
          },
        },
        datalabels: {
          anchor: 'center',
          align: 'center',
          color:
            chartType.value === 'bar' || chartType.value === 'line'
              ? '#000'
              : '#fff',
          font: {
            weight: 'bold',
            size: 14,
          },
          formatter: (value, ctx) => {
            const index = ctx.dataIndex;
            return graficoPorcentagem.value[index];
          },
          display:
            chartType.value === 'pie' ||
            chartType.value === 'doughnut' ||
            chartType.value === 'polarArea',
        },
      },
      scales:
        chartType.value === 'bar' || chartType.value === 'line'
          ? {
              x: {
                type: 'category', // Explicitamente definindo como "category"
              },
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function (value) {
                    return new Intl.NumberFormat('pt-BR', {
                      style: 'currency',
                      currency: 'BRL',
                    }).format(value);
                  },
                },
              },
            }
          : undefined,
    },
  };

  myChart = new Chart(ctx, baseConfig);
};

onMounted(() => {
  fetchData();
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

.tabela th {
  background-color: #164110;
  color: #ffffff;
}

.TrRedonEsquerda {
  border-radius: 20px 0px 0px 0px;
}

.TrRedonDireita {
  border-radius: 0px 20px 0px 0px;
}

.tabela tr:nth-child(even) {
  background-color: #f4f5f3;
}

.tabela tr:hover {
  background-color: #dededea9;
}
</style>
