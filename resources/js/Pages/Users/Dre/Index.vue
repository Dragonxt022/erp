<template>
  <LayoutFranqueado>
    <Head title="Painel" />
    <div class="painel-title">DRE Gerencial</div>
    <div class="painel-subtitle">
      <p>Acompanhe a saúde da sua operação</p>
    </div>
    <div class="flex justify-end mb-4">
      <div class="text-[#262a27] text-[15px] font-semibold font-['Figtree'] leading-tight">
        <div class="flex items-center space-x-2 justify-end">
          <span class="text-gray-900 text-[17px] font-semibold">
            <CalendarFilterDre @update-filters="handleFilterUpdate" />
          </span>
        </div>
      </div>
    </div>

    <div class="mt-5">
      <div class="grid grid-cols-2 grid-rows-1 gap-4">
        <!-- Tabela de Faturamento -->
        <div class="rounded-lg">
          <table class="w-full text-left text-[14px] border-collapse font-['Figtree']">
            <thead>
              <tr class="bg-[#174111] text-white">
                <th colspan="2" class="p-1 px-5">Faturamento do Período</th>
              </tr>
            </thead>
            <tbody v-if="loading">
              <tr v-for="n in 25" :key="n">
                <td class="p-1 px-5 shimmer h-6 w-1/2 rounded"></td>
                <td class="p-1 px-5 shimmer h-6 w-1/4 rounded text-right"></td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td class="p-1 px-5 categorias">Faturamento do Período</td>
                <td class="px-5 py-2 text-right valores">R$ {{ totalCaixas }}</td>
              </tr>
            </tbody>
            <template v-for="grupo in grupos" :key="grupo.nome_grupo">
              <thead>
                <tr class="bg-[#174111] text-white">
                  <th colspan="2" class="p-1 px-5">{{ grupo.nome_grupo }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="categoria in grupo.categorias"
                  :key="categoria.categoria"
                  class="odd:bg-gray-100 even:bg-white p-1 px-5"
                >
                  <td class="p-1 px-5 categorias align-middle">{{ categoria.categoria }}</td>
                  <td class="px-5 py-1 text-right valores">R$ {{ categoria.total }}</td>
                </tr>
              </tbody>
            </template>
            <thead>
              <tr class="bg-[#174111] text-white">
                <th colspan="1" class="p-2">Resultado do Período</th>
                <th colspan="1" class="p-2 text-right font-bold">R$ {{ resultadoPeriodo }}</th>
              </tr>
            </thead>
          </table>
        </div>

        <!-- Gráfico e Histórico na mesma coluna -->
        <div>
          <!-- Gráfico -->
          <div class="bg-white rounded-lg p-5 flex justify-center w-full h-[480px]">
            <canvas id="myChart"></canvas>
          </div>
          <!-- Histórico de Resultados -->
          <div class="mt-5">
              <div class="flex items-center justify-between">
                <h3 class="text-[#262a27] text-[17px] font-semibold font-['Figtree'] leading-snug">
                  Histórico de Resultados
                </h3>
              </div>

              <div class="bg-white rounded-lg shadow">
                <div class="bg-[#164110] grid grid-cols-3 gap-4 py-2 px-6 text-xs font-semibold text-[#FFFFFF] uppercase tracking-wider rounded-tl-2xl rounded-tr-2xl">
                  <span class="text-center px-5">MÊS</span>
                  <span class="text-center">CMV</span>
                  <span class="text-right">RESULTADO</span>
                </div>

                <div ref="scrollContainer" class="overflow-y-auto max-h-96 scroll-hidden">
                  <ul class="space-y-2">
                    <li
                      v-for="mes in historico"
                      :key="mes.nome_mes"
                      class="hover:bg-gray-200 grid grid-cols-3 gap-4 px-6 text-[14px]"
                    >
                      <span class="text-center px-5 text-gray-600 font-semibold">
                        {{ mes.nome_mes }}
                      </span>
                      <span class="text-center text-gray-600 font-medium">
                        R$ {{ mes.categorias[0].valor_cmv }}
                      </span>
                      <span class="text-right text-gray-600 font-semibold">
                        R$ {{ mes.categorias[0].resultado_do_periodo }}
                      </span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import axios from 'axios';
import ChartDataLabels from 'chartjs-plugin-datalabels'; 
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
import CalendarFilterDre from '@/Components/Filtros/CalendarFilterDre.vue';

const totalCaixas = ref('0,00');
const resultadoPeriodo = ref('0,00');
const grupos = ref([]);
const historico = ref([]);
const loading = ref(true);

const chartType = ref('pie');
let myChart = null;

const graficoData = ref([]);
const graficoLabels = ref([]);
const graficoPorcentagem = ref([]);

const listaLabels = ref([]);
const listaData = ref([]);
const listaPorcentagem = ref([]);

// Função que lida com a atualização dos filtros
const handleFilterUpdate = (filters) => {
  console.log('Filtros atualizados:', filters);

  // Aqui você pode fazer a requisição com os novos filtros
  fetchData(filters.startDate, filters.endDate);
};


const fetchData = async (startDate, endDate) => {
  try {
    const response = await axios.get('/api/painel-dre/analitycs-dre', {
      params: {
        start_date: startDate,
        end_date: endDate,
      },
    });

    const data = response.data;

    totalCaixas.value = data.total_caixas || '0,00';
    resultadoPeriodo.value = data.resultado_do_periodo || '0,00';
    grupos.value = data.grupos || [];
    historico.value = data.calendario || [];

    // Inicializar arrays para o gráfico (filtrado) e para a lista (completo)
    graficoLabels.value = [];
    graficoData.value = [];
    graficoPorcentagem.value = [];

    // Arrays para a lista à direita (todos os dados)
    listaLabels.value = [];
    listaData.value = [];
    listaPorcentagem.value = [];

    // Iterar sobre os grupos para construir os dados
    data.grupos.forEach(grupo => {
      const categorias = Array.isArray(grupo.categorias)
        ? grupo.categorias
        : Object.values(grupo.categorias);

      categorias.forEach(categoria => {
        const valor = parseFloat(categoria.total.replace('.', '').replace(',', '.'));
        const porcentagem = categoria.porcentagem;

        // Adicionar a todos os dados para a lista à direita
        listaLabels.value.push(categoria.categoria);
        listaData.value.push(valor);
        listaPorcentagem.value.push(porcentagem);

        // Adicionar ao gráfico apenas se a porcentagem não for "0,00%"
        if (porcentagem !== '0,00%') {
          graficoLabels.value.push(categoria.categoria);
          graficoData.value.push(valor);
          graficoPorcentagem.value.push(porcentagem);
        }
      });
    });

    renderGrafico();
  } catch (error) {
    console.error('Erro ao buscar os dados do DRE:', error);
  } finally {
    loading.value = false;
  }
};



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
            '#6DB631',
            '#FF9500',
            '#FF2D55',
            '#5856D6',
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

onMounted(fetchData);
</script>

<style lang="css" scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
  margin-bottom: -10px;
}
.painel-subtitle {
  font-size: 17px;
  color: #6d6d6e;
  max-width: 600px;
}

.categorias {
  color: #6d6d6e;
  font-size: 14px;
  font-family: Figtree;
  font-weight: 600;
  text-transform: capitalize;
  line-height: 14px;
  word-wrap: break-word;
}

.valores {
  color: #6d6d6e;
  font-size: 14px;
  font-family: Figtree;
  font-weight: 700;
  text-transform: capitalize;
  line-height: 14px;
  word-wrap: break-word;
}

/* Animação de shimmer */
.shimmer {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite linear;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style>
