<template>
    <LayoutFranqueadora>
        <Head title="Painel" />
        
        <div class="painel-title">DRE Gerencial</div>
        <div class="painel-subtitle">
            <p>Acompanhe a saúde da operação das unidades</p>
        </div>

        <div class="flex flex-col md:flex-row justify-end items-center mb-4 gap-3">
            <!-- Seletor de Unidade -->
            <div class="w-full md:w-64">
                <UnidadeSelectorDropdown :default-unit-id="selectedUnitId" @unit-selected="handleUnitChange" />
            </div>

            <div class="flex items-center">
                <button @click="showModal = true" class="mr-2" title="Ajuda a entender esses gráficos!">
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
                            <CalendarFilterDre @update-filters="handleFilterUpdate" :default-to-previous-month="true" />
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="rounded-lg bg-white overflow-hidden shadow-sm">
                    <table class="w-full text-left text-[14px] border-collapse font-['Figtree']">
                        <thead>
                            <tr class="bg-[#174111] text-white">
                                <th colspan="2" class="p-1 px-5">Faturamento do Período</th>
                            </tr>
                        </thead>
                        <tbody v-if="loading">
                            <tr v-for="n in 1" :key="n">
                                <td class="p-1 px-5 flex items-center">
                                    <div class="shimmer h-6 w-full rounded"></div>
                                </td>
                                <td class="p-1 px-5 text-right w-1/4">
                                    <div class="shimmer h-6 w-full rounded"></div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr class="bg-white">
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
                                <tr v-for="n in (grupo.categorias.length || 3)" :key="n">
                                    <td class="p-1 px-5">
                                        <div class="shimmer h-6 w-full rounded"></div>
                                    </td>
                                    <td class="p-1 px-5 text-right w-1/4">
                                        <div class="shimmer h-6 w-full rounded"></div>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr v-for="categoria in grupo.categorias" :key="categoria.categoria"
                                    class="odd:bg-gray-100 even:bg-white p-1 px-5 transition-colors">
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
                                <th colspan="1" class="p-2 px-5">Resultado do Período</th>
                                <th colspan="1" class="p-2 px-5 text-right font-bold">
                                    R$ {{ resultadoPeriodo }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div>
                    <div class="bg-white rounded-lg p-5 flex justify-center w-full min-h-[480px] shadow-sm">
                        <canvas id="myChart"></canvas>
                        <div v-if="!loading && graficoData.length === 0" class="flex items-center text-gray-400">Sem dados para exibir no gráfico</div>
                    </div>

                    <div v-if="feedbackChatbot" class="mt-4 p-4 bg-green-100 text-green-800 rounded shadow-sm font-medium text-sm border-l-4 border-green-500">
                        {{ feedbackChatbot }}
                    </div>

                    <div class="mt-5">
                        <div class="flex items-center justify-between mb-2 px-1">
                            <h3 class="text-[#262a27] text-[17px] font-semibold font-['Figtree'] leading-snug">
                                Histórico de Resultados
                            </h3>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                            <div
                                class="bg-[#164110] grid grid-cols-3 gap-4 py-2 px-6 text-xs font-semibold text-white uppercase tracking-wider rounded-tl-2xl rounded-tr-2xl">
                                <span class="text-center">MÊS</span>
                                <span class="text-center">CMV</span>
                                <span class="text-center">RESULTADO</span>
                            </div>

                            <div class="overflow-y-auto max-h-96 scroll-hidden">
                                <ul class="space-y-0">
                                    <li v-if="loading">
                                        <div v-for="n in 5" :key="n" class="grid grid-cols-3 gap-4 px-6 py-2">
                                            <span class="shimmer h-5 w-full rounded"></span>
                                            <span class="shimmer h-5 w-full rounded"></span>
                                            <span class="shimmer h-5 w-full rounded"></span>
                                        </div>
                                    </li>
                                    <li v-else v-for="mes in historico" :key="mes.nome_mes"
                                        class="hover:bg-gray-200 grid grid-cols-3 text-[15px] p-2 border-b last:border-0 transition-colors">
                                        <span class="text-center text-gray-600 font-semibold align-middle">
                                            {{ mes.nome_mes }}
                                        </span>
                                        <span class="text-center text-gray-600 font-medium align-middle">
                                            R$ {{ mes.valor_cmv }}
                                        </span>
                                        <span class="text-center font-bold align-middle" :class="parseValor(mes.resultado_do_periodo) >= 0 ? 'text-green-600' : 'text-red-600'">
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
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg max-w-xl p-8 shadow-xl relative animate-in fade-in zoom-in duration-200">
                    <button @click="showModal = false"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none">
                        &times;
                    </button>
                    <h2 class="text-xl font-bold mb-4 text-gray-800">Como funcionam os cálculos do DRE</h2>
                    <div class="text-gray-700 leading-relaxed text-sm whitespace-pre-line max-h-[60vh] overflow-y-auto pr-2">
                        {{ explicacaoDRE }}
                    </div>
                    <div class="mt-8 flex justify-end">
                        <button @click="showModal = false" class="px-6 py-2 bg-[#164110] text-white rounded-lg hover:bg-opacity-90 transition-colors font-semibold">Fechar</button>
                    </div>
                </div>
            </div>
        </template>
    </LayoutFranqueadora>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import LayoutFranqueadora from '@/Layouts/LayoutFranqueadora.vue';
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
import UnidadeSelectorDropdown from '@/Components/Filtros/UnidadeSelectorDropdown.vue';

// State
const totalCaixas = ref('0,00');
const totalSalarios = ref('0,00');
const resultadoPeriodo = ref('0,00');
const grupos = ref([]);
const historico = ref([]);
const explicacaoDRE = ref('');
const loading = ref(true);
const showModal = ref(false);

const page = usePage();
const selectedUnitId = ref(page.props.auth.user.unidade_id);
const startDate = ref('');
const endDate = ref('');

const chartType = ref('pie');
let myChart = null;
const graficoData = ref([]);
const graficoLabels = ref([]);
const graficoPorcentagem = ref([]);

// Handlers
const handleUnitChange = (unitId) => {
    selectedUnitId.value = unitId;
    fetchData(startDate.value, endDate.value);
};

const handleFilterUpdate = (filters) => {
    startDate.value = filters.startDate;
    endDate.value = filters.endDate;
    fetchData(filters.startDate, filters.endDate);
};

const imprimirRelatorio = () => {
    // For franqueadora, we might want to pass the unit_id too if the route supports it
    const url = selectedUnitId.value 
        ? `/relatorios/faturamento-anual?unidade_id=${selectedUnitId.value}`
        : '/relatorios/faturamento-anual';
    window.open(url, '_blank');
};

// Data Fetching
const fetchData = async (sDate, eDate) => {
    if (!selectedUnitId.value) return;
    
    loading.value = true;
    try {
        const response = await axios.get('/api/painel-dre/analitycs-dre', {
            params: {
                start_date: sDate,
                end_date: eDate,
                unidade_id: selectedUnitId.value
            },
        });

        const data = response.data;
        totalCaixas.value = data.total_caixas || '0,00';
        totalSalarios.value = data.total_salarios || '0,00';
        resultadoPeriodo.value = data.resultado_do_periodo || '0,00';
        grupos.value = data.grupos || [];
        historico.value = data.calendario || [];
        explicacaoDRE.value = data.explicacao_dre || '';

        graficoLabels.value = data.grafico_data.labels || [];
        graficoData.value = data.grafico_data.data || [];
        graficoPorcentagem.value = data.grafico_data.porcentagens || [];

        renderGrafico();
    } catch (error) {
        console.error('Erro ao buscar dados do DRE:', error);
        resetData();
    } finally {
        loading.value = false;
    }
};

const resetData = () => {
    totalCaixas.value = '0,00';
    totalSalarios.value = '0,00';
    resultadoPeriodo.value = '0,00';
    grupos.value = [];
    historico.value = [];
    graficoLabels.value = [];
    graficoData.value = [];
    graficoPorcentagem.value = [];
    if (myChart) {
        myChart.destroy();
        myChart = null;
    }
};

// Chart Logic
Chart.register(ArcElement, Tooltip, Legend, DoughnutController, PieController, CategoryScale, Filler, ChartDataLabels);

const renderGrafico = () => {
    const ctx = document.getElementById('myChart');
    if (!ctx) return;

    if (myChart) myChart.destroy();

    myChart = new Chart(ctx, {
        type: chartType.value,
        data: {
            labels: graficoLabels.value,
            datasets: [{
                data: graficoData.value,
                backgroundColor: ['#6DB631', '#FF9500', '#FF2D55', '#5856D6', '#F7464A', '#FF7F50'],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (tooltipItem) {
                            const valorFormatado = new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                            }).format(tooltipItem.raw);
                            const porcentagem = graficoPorcentagem.value[tooltipItem.dataIndex];
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
                        font: { size: 12 }
                    },
                },
                datalabels: {
                    display: true,
                    color: '#fff',
                    formatter: (value, context) => {
                        const valorFormatado = new Intl.NumberFormat('pt-BR', {
                            style: 'currency',
                            currency: 'BRL',
                        }).format(value);
                        const porcentagem = graficoPorcentagem.value[context.dataIndex];
                        return `${valorFormatado}\n(${porcentagem})`;
                    },
                    font: { weight: 'bold', size: 10 },
                    anchor: 'end',
                    align: 'start',
                    offset: 10,
                },
            },
        },
    });
};

// Utils
const parseValor = (str) => {
    if (!str) return 0;
    return Number(str.replace(/\./g, '').replace(',', '.'));
};

const feedbackChatbot = computed(() => {
  if (!historico.value || historico.value.length === 0) return '';
  const hoje = new Date();
  const mesAtualNum = hoje.getMonth() + 1;
  const mesAtual = historico.value.find(m => m.nome_mes.toLowerCase() === mesNomePorNumero(mesAtualNum).toLowerCase());
  const mesAnterior = historico.value.find(m => m.nome_mes.toLowerCase() === mesNomePorNumero(mesAtualNum - 1).toLowerCase());
  if (!mesAtual) return '';
  const resAtual = parseValor(mesAtual.resultado_do_periodo);
  const resAnterior = mesAnterior ? parseValor(mesAnterior.resultado_do_periodo) : null;
  if (resAnterior === null) return `Olá! No mês de ${mesAtual.nome_mes}, seu resultado foi de R$ ${mesAtual.resultado_do_periodo}. Vamos continuar assim! 🚀`;
  if (resAtual > resAnterior) return `Boa notícia! Seu resultado em ${mesAtual.nome_mes} (R$ ${mesAtual.resultado_do_periodo}) melhorou em relação a ${mesAnterior.nome_mes} (R$ ${mesAnterior.resultado_do_periodo}). Continue assim! 🎉`;
  if (resAtual < resAnterior) return `Atenção: o resultado em ${mesAtual.nome_mes} (R$ ${mesAtual.resultado_do_periodo}) está abaixo do mês anterior (${mesAnterior.nome_mes} - R$ ${mesAnterior.resultado_do_periodo}). Vamos analisar juntos para melhorar`;
  return `Seu resultado em ${mesAtual.nome_mes} (R$ ${mesAtual.resultado_do_periodo}) está estável em relação a ${mesAnterior.nome_mes}. Seguimos firmes! 👍`;
});

const mesNomePorNumero = (num) => {
  const meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
  let n = num;
  if (n < 1) n = 12 + n;
  if (n > 12) n = n % 12;
  return meses[n - 1];
};

// Init
onMounted(() => {
    // CalendarFilterDre will emit the initial filter update
});
</script>

<style scoped>
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
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.scroll-hidden::-webkit-scrollbar { display: none; }
.scroll-hidden { -ms-overflow-style: none; scrollbar-width: none; }
</style>
