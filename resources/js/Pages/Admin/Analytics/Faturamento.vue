<template>
    <LayoutFranqueadora>
        <Head title="Faturamento Analítico" />
        
        <div class="painel-title">Faturamento Analítico</div>
        <div class="painel-subtitle">
            <p>Análise comparativa de faturamento dos últimos 12 meses</p>
        </div>

        <div class="flex flex-col md:flex-row justify-end items-center mb-6 gap-4">
            <!-- Seletor de Unidade -->
            <div class="w-full md:w-64">
                <UnidadeSelectorDropdown :default-unit-id="selectedUnitId" @unit-selected="handleUnitChange" />
            </div>
        </div>

        <div class="mt-5">
            <div class="grid grid-cols-1 gap-6">
                <!-- Gráfico de Faturamento -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Evolução de 12 Meses</h3>
                            <p class="text-sm text-gray-500">Comparativo mensal e variação nominal</p>
                        </div>
                        <div class="flex flex-wrap gap-4">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-[#164110] rounded-sm shadow-sm"></div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-700">Faturamento</span>
                                    <span class="text-[10px] text-gray-400">Total do mês</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-[#6DB631] rounded-sm shadow-sm"></div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-green-700">Crescimento</span>
                                    <span class="text-[10px] text-gray-400">Variação positiva</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-[#FF2D55] rounded-sm shadow-sm"></div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-red-700">Queda</span>
                                    <span class="text-[10px] text-gray-400">Variação negativa</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="h-[450px] relative">
                        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#164110]"></div>
                        </div>
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Projeção -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Projeção para os Próximos 12 Meses</h3>
                            <p class="text-sm text-gray-500">Estimativa baseada na média de crescimento histórico ({{ mediaCrescimento }}%)</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-1 bg-[#164110] border-t-2 border-dashed border-[#164110]"></div>
                            <span class="text-xs font-bold text-gray-700">Tendência Projetada</span>
                        </div>
                    </div>
                    
                    <div class="h-[400px] relative">
                        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#164110]"></div>
                        </div>
                        <canvas id="projectionChart"></canvas>
                    </div>
                </div>

                <!-- Tabela de Dados -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50">
                        <h3 class="text-lg font-bold text-gray-800">Detalhamento Mensal</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <th class="px-6 py-4">Período</th>
                                    <th class="px-6 py-4 text-right">Faturamento Total</th>
                                    <th class="px-6 py-4 text-right">Variação Nominal</th>
                                    <th class="px-6 py-4 text-right">Variação Percentual</th>
                                </tr>
                            </thead>
                            <tbody v-if="loading" class="divide-y divide-gray-100">
                                <tr v-for="n in 5" :key="n">
                                    <td class="px-6 py-4"><div class="h-4 bg-gray-100 rounded w-24 animate-pulse"></div></td>
                                    <td class="px-6 py-4"><div class="h-4 bg-gray-100 rounded w-20 animate-pulse ml-auto"></div></td>
                                    <td class="px-6 py-4"><div class="h-4 bg-gray-100 rounded w-20 animate-pulse ml-auto"></div></td>
                                    <td class="px-6 py-4"><div class="h-4 bg-gray-100 rounded w-16 animate-pulse ml-auto"></div></td>
                                </tr>
                            </tbody>
                            <tbody v-else class="divide-y divide-gray-100">
                                <tr v-for="(item, index) in reversedDados" :key="index" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-800">{{ item.nome_mes }}</span>
                                            <span class="text-xs text-gray-400">{{ item.ano }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900 text-base">
                                        R$ {{ item.faturamento_formatado }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1" :class="item.diferenca >= 0 ? 'text-green-600' : 'text-red-600'">
                                            <span v-if="item.diferenca > 0" class="text-xs">▲</span>
                                            <span v-else-if="item.diferenca < 0" class="text-xs">▼</span>
                                            <span class="font-semibold">R$ {{ item.diferenca_formatada }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="px-2 py-1 rounded-full text-xs font-bold" 
                                              :class="item.percentual >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'">
                                            {{ item.percentual >= 0 ? '+' : '' }}{{ item.percentual }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';
import UnidadeSelectorDropdown from '@/Components/Filtros/UnidadeSelectorDropdown.vue';

Chart.register(BarController, BarElement, LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler, ChartDataLabels);

const page = usePage();
const selectedUnitId = ref(page.props.auth.user.unidade_id);
const loading = ref(true);
const dados = ref([]);
const projecao = ref([]);
const mediaCrescimento = ref(0);
let myChart = null;
let projectionChart = null;

const reversedDados = computed(() => [...dados.value].reverse());

const handleUnitChange = (unitId) => {
    selectedUnitId.value = unitId;
    fetchData();
};

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/painel/faturamento-analitico', {
            params: { unidade_id: selectedUnitId.value }
        });
        dados.value = response.data.dados || [];
        projecao.value = response.data.projecao || [];
        mediaCrescimento.value = response.data.media_crescimento || 0;
        
        renderChart();
        renderProjectionChart();
    } catch (error) {
        console.error('Erro ao buscar faturamento analítico:', error);
    } finally {
        loading.value = false;
    }
};

const renderChart = () => {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    if (myChart) myChart.destroy();

    const labels = dados.value.map(d => `${d.nome_mes.substring(0,3)}/${d.ano.substring(2)}`);
    const faturamentoData = dados.value.map(d => d.faturamento);
    const diffData = dados.value.map(d => d.diferenca);

    myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Faturamento',
                    data: faturamentoData,
                    backgroundColor: '#164110',
                    borderRadius: 4,
                    order: 2,
                    yAxisID: 'y',
                    datalabels: {
                        color: '#164110',
                        anchor: 'end',
                        align: 'top',
                        offset: 4,
                        font: { weight: 'bold', size: 10, family: 'Figtree' },
                        formatter: (value) => {
                            if (value === 0) return '';
                            return new Intl.NumberFormat('pt-BR', { notation: 'compact', maximumFractionDigits: 1 }).format(value);
                        }
                    }
                },
                {
                    label: 'Variação',
                    data: diffData,
                    backgroundColor: dados.value.map(d => d.diferenca >= 0 ? '#6DB631' : '#FF2D55'),
                    borderRadius: 4,
                    order: 1,
                    yAxisID: 'y1',
                    datalabels: {
                        display: true,
                        color: (context) => context.dataset.backgroundColor[context.dataIndex],
                        anchor: 'start',
                        align: 'bottom',
                        offset: 4,
                        font: { weight: 'bold', size: 9, family: 'Figtree' },
                        formatter: (value) => {
                            if (value === 0) return '';
                            const sign = value > 0 ? '+' : '';
                            return sign + new Intl.NumberFormat('pt-BR', { notation: 'compact', maximumFractionDigits: 1 }).format(value);
                        }
                    }
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Figtree', weight: '600' } }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: { color: '#f3f4f6' },
                    ticks: {
                        font: { family: 'Figtree' },
                        callback: (value) => new Intl.NumberFormat('pt-BR', { notation: 'compact' }).format(value)
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        font: { family: 'Figtree' },
                        callback: (value) => new Intl.NumberFormat('pt-BR', { notation: 'compact' }).format(value)
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    padding: 12,
                    backgroundColor: '#164110',
                    titleFont: { family: 'Figtree', size: 14, weight: 'bold' },
                    bodyFont: { family: 'Figtree', size: 13 },
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
};

const renderProjectionChart = () => {
    const ctx = document.getElementById('projectionChart');
    if (!ctx) return;

    if (projectionChart) projectionChart.destroy();

    const historicalLabels = dados.value.map(d => `${d.nome_mes.substring(0,3)}/${d.ano.substring(2)}`);
    const projectionLabels = projecao.value.map(d => `${d.nome_mes.substring(0,3)}/${d.ano.substring(2)}`);
    
    // Encontrar o último mês com faturamento real para conectar a projeção corretamente
    let lastRealIndex = -1;
    for (let i = dados.value.length - 1; i >= 0; i--) {
        if (dados.value[i].faturamento > 0) {
            lastRealIndex = i;
            break;
        }
    }

    if (lastRealIndex === -1) return;

    const baseMonth = dados.value[lastRealIndex];
    const baseLabel = `${baseMonth.nome_mes.substring(0,3)}/${baseMonth.ano.substring(2)}`;
    
    const combinedLabels = [baseLabel, ...projectionLabels];
    const combinedData = [baseMonth.faturamento, ...projecao.value.map(d => d.faturamento)];

    projectionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: combinedLabels,
            datasets: [
                {
                    label: 'Projeção de Faturamento',
                    data: combinedData,
                    borderColor: '#164110',
                    backgroundColor: 'rgba(22, 65, 16, 0.1)',
                    borderWidth: 3,
                    borderDash: [5, 5],
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#164110',
                    datalabels: {
                        color: '#164110',
                        anchor: 'end',
                        align: 'top',
                        offset: 8,
                        font: { weight: 'bold', size: 10, family: 'Figtree' },
                        formatter: (value) => {
                            return new Intl.NumberFormat('pt-BR', { notation: 'compact', maximumFractionDigits: 1 }).format(value);
                        }
                    }
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    padding: 12,
                    backgroundColor: '#164110',
                    callbacks: {
                        label: function(context) {
                            return 'Estimativa: ' + new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Figtree', weight: '600' } }
                },
                y: {
                    beginAtZero: false,
                    grid: { color: '#f3f4f6' },
                    ticks: {
                        font: { family: 'Figtree' },
                        callback: (value) => new Intl.NumberFormat('pt-BR', { notation: 'compact' }).format(value)
                    }
                }
            }
        }
    });
};

onMounted(() => {
    fetchData();
});
</script>

<style scoped>
.painel-title {
    font-size: 34px;
    font-weight: 700;
    color: #262a27;
    margin-bottom: -5px;
}
.painel-subtitle {
    font-size: 17px;
    color: #6d6d6e;
    margin-bottom: 30px;
}

/* Custom scrollbar for x-overflow table */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}
.overflow-x-auto::-webkit-scrollbar-track {
    background: #f9fafb;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}
.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}
</style>
