<template>
    <LayoutFranqueado>

        <Head title="Painel" />
        <div class="painel-title">DRE Gerencial</div>
        <div class="painel-subtitle">
            <p>Acompanhe a saúde da sua operação</p>
        </div>
        <div class="flex justify-end mb-4">
            <button @click="showModal = true" class="mr-2" placeholder="Ajuda a entender esses gráficos!">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="15"
                height="15"
                viewBox="0 0 24 24"
                style="fill: #018f7f; transform: msfilter"
              >
                <path
                  d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"
                ></path>
              </svg>
            </button>
            <button @click="imprimirRelatorio" class="mr-3 bg-[#174111] hover:bg-[#12330d] text-white font-bold py-1 px-3 rounded text-sm transition duration-300">
                Imprimir Relatório Anual
            </button>
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
                <div class="rounded-lg">
                    <table class="w-full text-left text-[14px] border-collapse font-['Figtree']">
                        <thead>
                            <tr class="bg-[#174111] text-white">
                                <th colspan="2" class="p-1 px-5">Faturamento do Período</th>
                            </tr>
                        </thead>
                        <tbody v-if="loading">
                            <tr v-for="n in 1" :key="n">
                                <td class="p-1 px-5 shimmer h-6 w-1/2 rounded"></td>
                                <td class="p-1 px-5 shimmer h-6 w-1/4 rounded text-right"></td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td class="p-1 px-5 categorias">Faturamento do Período</td>
                                <td class="px-5 py-2 text-right valores">
                                    R$ {{ totalCaixas }}
                                </td>
                            </tr>
                        </tbody>
                        <template v-for="grupo in grupos" :key="grupo.nome_grupo">
                            <thead>
                                <tr class="bg-[#174111] text-white">
                                    <th colspan="2" class="p-1 px-5">{{ grupo.nome_grupo }}</th>
                                </tr>
                            </thead>
                            <tbody v-if="loading">
                                <tr v-for="n in grupo.categorias.length || 3" :key="n">
                                    <td class="p-1 px-5 shimmer h-6 w-1/2 rounded"></td>
                                    <td class="p-1 px-5 shimmer h-6 w-1/4 rounded text-right"></td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr v-for="categoria in grupo.categorias" :key="categoria.categoria"
                                    class="odd:bg-gray-100 even:bg-white p-1 px-5">
                                    <td class="p-1 px-5 categorias align-middle">
                                        {{ categoria.categoria }}
                                    </td>
                                    <td class="px-5 py-1 text-right valores">
                                        R$ {{ categoria.total }}
                                    </td>
                                </tr>
                            </tbody>
                        </template>
                        <thead>
                            <tr class="bg-[#174111] text-white">
                                <th colspan="1" class="p-2">Resultado do Período</th>
                                <th colspan="1" class="p-2 text-right font-bold">
                                    R$ {{ resultadoPeriodo }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div>
                    <div class="bg-white rounded-lg p-5 flex justify-center w-full h-[480px]">
                        <canvas id="myChart"></canvas>

                    </div>

                    <div class="mt-4 p-4 bg-green-100 text-green-800 rounded font-medium text-sm">
                        {{ feedbackChatbot }}
                    </div>


                    <div class="mt-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[#262a27] text-[17px] font-semibold font-['Figtree'] leading-snug">
                                Histórico de Resultados
                            </h3>
                        </div>

                        <div class="bg-white rounded-lg shadow">
                            <div
                                class="bg-[#164110] grid grid-cols-3 gap-4 py-2 px-6 text-xs font-semibold text-[#FFFFFF] uppercase tracking-wider rounded-tl-2xl rounded-tr-2xl">
                                <span class="text-center px-5">MÊS</span>
                                <span class="text-center">CMV</span>
                                <span class="text-center">RESULTADO</span>
                            </div>

                            <div ref="scrollContainer" class="overflow-y-auto max-h-96 scroll-hidden">
                                <ul class="space-y-2">
                                    <li v-if="loading">
                                        <div v-for="n in 5" :key="n" class="grid grid-cols-3 gap-4 px-6 py-2">
                                            <span class="shimmer h-5 w-3/4 rounded"></span>
                                            <span class="shimmer h-5 w-1/2 rounded"></span>
                                            <span class="shimmer h-5 w-1/2 rounded"></span>
                                        </div>
                                    </li>
                                    <li v-else v-for="mes in historico" :key="mes.nome_mes"
                                        class="hover:bg-gray-200 grid grid-cols-3 text-[15px] p-1">
                                        <span class="text-center text-gray-600 font-semibold">
                                            {{ mes.nome_mes }}
                                        </span>
                                        <span class="text-center text-gray-600 font-medium">
                                            R$ {{ mes.valor_cmv }}
                                        </span>
                                        <span class="text-center text-gray-600 font-semibold">
                                            R$ {{ mes.resultado_do_periodo }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <template v-if="showModal">
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg max-w-xl p-6 shadow-lg relative">
                    <button @click="showModal = false"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl font-bold">
                        &times;
                    </button>
                    <h2 class="text-xl font-bold mb-4">Como funcionam os cálculos do DRE</h2>
                    <p class="text-gray-700 leading-relaxed text-sm whitespace-pre-line">
                        {{ explicacaoDRE }}
                    </p>
                </div>
            </div>
        </template>


    </LayoutFranqueado>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
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
    CategoryScale,
    Filler,
} from 'chart.js';
import CalendarFilterDre from '@/Components/Filtros/CalendarFilterDre.vue';

const totalCaixas = ref('0,00');
const totalSalarios = ref('0,00');
const resultadoPeriodo = ref('0,00');
const grupos = ref([]);
const historico = ref([]); // This will now directly hold the calendar data
const explicacaoDRE = ref('');
const loading = ref(true);

const chartType = ref('pie'); // Assuming pie chart is the primary one
let myChart = null;

const graficoData = ref([]);
const graficoLabels = ref([]);
const graficoPorcentagem = ref([]); // This now comes directly from the backend data
const showModal = ref(false);

// teste
const feedbackChatbot = computed(() => gerarFeedbackChatbot(historico.value));


// Function to handle filter updates from CalendarFilterDre component
const handleFilterUpdate = (filters) => {
    console.log('Filtros atualizados:', filters);
    // Trigger data fetching with the new filters
    fetchData(filters.startDate, filters.endDate);
};

const imprimirRelatorio = () => {
    window.open('/relatorios/faturamento-anual', '_blank');
};

const fetchData = async (startDate, endDate) => {
    loading.value = true; // Set loading to true when fetching starts
    try {
        const response = await axios.get('/api/painel-dre/analitycs-dre', {
            params: {
                start_date: startDate,
                end_date: endDate,
            },
        });

        const data = response.data;
        
        // Debug: Log the complete response to check if total_salarios is present
        console.log('DRE API Response:', data);
        console.log('Total Salarios from API:', data.total_salarios);

        // Update main DRE period data
        totalCaixas.value = data.total_caixas || '0,00';
        totalSalarios.value = data.total_salarios || '0,00';
        resultadoPeriodo.value = data.resultado_do_periodo || '0,00';
        grupos.value = data.grupos || [];
        historico.value = data.calendario || []; // Directly assign the calendar data
        explicacaoDRE.value = data.explicacao_dre || [];

        // Prepare data for the chart using the new `grafico_data` from the backend
        graficoLabels.value = data.grafico_data.labels || [];
        graficoData.value = data.grafico_data.data || [];
        graficoPorcentagem.value = data.grafico_data.porcentagens || [];

        renderGrafico(); // Re-render the chart with new data

    } catch (error) {
        console.error('Erro ao buscar os dados do DRE:', error);
        // Optionally, set default empty values or show an error message
        totalCaixas.value = '0,00';
        totalSalarios.value = '0,00';
        resultadoPeriodo.value = '0,00';
        grupos.value = [];
        historico.value = [];
        graficoLabels.value = [];
        graficoData.value = [];
        graficoPorcentagem.value = [];
        if (myChart) {
            myChart.destroy(); // Destroy chart on error if it exists
            myChart = null;
        }
    } finally {
        loading.value = false; // Set loading to false when fetching is complete (success or error)
    }
};

// Register only the necessary chart controllers
Chart.register(
    ArcElement,
    Tooltip,
    Legend,
    DoughnutController,
    PieController,
    CategoryScale, // Needed if you were to use Bar/Line charts later
    Filler, // Needed for certain chart fills, keeping it for robustness
    ChartDataLabels
);

const renderGrafico = () => {
    const ctx = document.getElementById('myChart');
    if (!ctx) return; // Ensure the canvas element exists

    const chartCanvas = ctx.getContext('2d');

    if (myChart) {
        myChart.destroy(); // Destroy existing chart instance before creating a new one
    }

    const baseConfig = {
        type: chartType.value,
        data: {
            labels: graficoLabels.value,
            datasets: [
                {
                    data: graficoData.value,
                    backgroundColor: [
                        '#6DB631', // Green
                        '#FF9500', // Orange
                        '#FF2D55', // Red
                        '#5856D6', // Purple
                        '#F7464A', // Another Red/Orange (can be randomized or defined in backend)
                        '#FF7F50', // Coral (can be randomized or defined in backend)
                    ],
                    // borderColor and borderWidth are generally for bar/line charts, not typically for pie/doughnut unless for specific effects
                    borderColor: undefined,
                    borderWidth: 0,
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allows flexible sizing
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (tooltipItem) {
                            const valorFormatado = new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                            }).format(tooltipItem.raw);
                            const porcentagem =
                                graficoPorcentagem.value[tooltipItem.dataIndex];
                            return `${tooltipItem.label}: ${valorFormatado} (${porcentagem})`;
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
                    display: true, // Activate datalabels for pie chart
                    color: '#fff',
                    formatter: (value, context) => {
                        // Format the raw value as currency
                        const valorFormatado = new Intl.NumberFormat('pt-BR', {
                            style: 'currency',
                            currency: 'BRL',
                        }).format(value);
                        // Get the percentage from the backend data
                        const porcentagem = graficoPorcentagem.value[context.dataIndex];
                        // Combine value and percentage
                        return `${valorFormatado}\n(${porcentagem})`; // Use \n for line break
                    },
                    font: {
                        weight: 'bold',
                        size: 10,
                    },
                    // Adjust position if labels overlap
                    anchor: 'end',
                    align: 'start',
                    offset: 10,
                },
            },
            // Scales are not typically used for Pie/Doughnut charts
            scales: {},
        },
    };

    myChart = new Chart(chartCanvas, baseConfig);
};

// Fetch initial data when the component is mounted
onMounted(() => {
    // Pass default dates (current month) to fetchData
    const today = new Date();
    const startDate = new Date(today.getFullYear(), today.getMonth(), 1)
        .toLocaleDateString('pt-BR')
        .split('/')
        .join('-');
    const endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0)
        .toLocaleDateString('pt-BR')
        .split('/')
        .join('-');
    fetchData(startDate, endDate);
});

//  Chat
const gerarFeedbackChatbot = (calendario) => {
  if (!calendario || calendario.length === 0) return '';

  const hoje = new Date();
  const mesAtualNum = hoje.getMonth() + 1; // JS: 0-11
  const anoAtual = hoje.getFullYear();

  // Busca o mês atual e o anterior na lista (supondo que calendario é ordenado)
  const mesAtual = calendario.find(
    (m) => m.nome_mes.toLowerCase() === mesNomePorNumero(mesAtualNum).toLowerCase()
  );
  const mesAnterior = calendario.find(
    (m) => m.nome_mes.toLowerCase() === mesNomePorNumero(mesAtualNum - 1).toLowerCase()
  );

  if (!mesAtual) return '';

  // Conversão de strings para número removendo pontos e vírgulas
  const parseValor = (str) => Number(str.replace(/\./g, '').replace(',', '.'));

  const resultadoAtual = mesAtual ? parseValor(mesAtual.resultado_do_periodo) : 0;
  const resultadoAnterior = mesAnterior ? parseValor(mesAnterior.resultado_do_periodo) : null;

  let mensagem = '';

  if (resultadoAnterior === null) {
    mensagem = `Olá! No mês de ${mesAtual.nome_mes}, seu resultado foi de R$ ${mesAtual.resultado_do_periodo}. Vamos continuar assim! 🚀`;
  } else if (resultadoAtual > resultadoAnterior) {
    mensagem = `Boa notícia! Seu resultado em ${mesAtual.nome_mes} (R$ ${mesAtual.resultado_do_periodo}) melhorou em relação a ${mesAnterior.nome_mes} (R$ ${mesAnterior.resultado_do_periodo}). Continue assim! 🎉`;
  } else if (resultadoAtual < resultadoAnterior) {
    mensagem = `Atenção: o resultado em ${mesAtual.nome_mes} (R$ ${mesAtual.resultado_do_periodo}) está abaixo do mês anterior (${mesAnterior.nome_mes} - R$ ${mesAnterior.resultado_do_periodo}). Vamos analisar juntos para melhorar`;
  } else {
    mensagem = `Seu resultado em ${mesAtual.nome_mes} (R$ ${mesAtual.resultado_do_periodo}) está estável em relação a ${mesAnterior.nome_mes}. Seguimos firmes! 👍`;
  }

  return mensagem;
};

// Helper para converter número do mês para nome do mês em português
const mesNomePorNumero = (num) => {
  const meses = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
  ];
  if (num < 1) num = 12 + num; // para dezembro do ano anterior
  if (num > 12) num = num % 12;
  return meses[num - 1];
};

</script>

<style lang="css" scoped>
/* Existing styles remain */
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
