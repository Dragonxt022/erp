<template>
  <div class="relative w-full h-80 mx-auto p-4">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { Chart, ArcElement, Tooltip, Legend, DoughnutController, PieController } from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';

// Registro dos elementos e plugins do Chart.js
Chart.register(ArcElement, Tooltip, Legend, DoughnutController, PieController, ChartDataLabels);

const props = defineProps({
  dados: {
    type: Array,
    required: true,
  },
});

const chartCanvas = ref(null);
let chartInstance = null;

// Gera cores para as fatias de forma dinâmica
const gerarCores = (quantidade) => {
  const paleta = ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#f43f5e'];
  return Array.from({ length: quantidade }, (_, i) => paleta[i % paleta.length]);
};

// Formata valores em Real
const formatarReal = (valor) => {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);
};

// Renderiza o gráfico
const renderChart = async () => {
  await nextTick();
  if (!chartCanvas.value) return;

  if (chartInstance) chartInstance.destroy();

  const valores = props.dados.map(d => d.valor);
  const labels = props.dados.map(d => d.categoria);
  const cores = gerarCores(labels.length);

  chartInstance = new Chart(chartCanvas.value, {
    type: 'pie',
    data: {
      labels,
      datasets: [{
        data: valores,
        backgroundColor: cores,
        borderColor: '#fff',
        borderWidth: 2,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
        },
        tooltip: {
          callbacks: {
            label: function(tooltipItem) {
              const valor = tooltipItem.raw;
              const total = valores.reduce((a, b) => a + b, 0);
              const percentual = total > 0 ? ((valor / total) * 100).toFixed(1) : 0;
              return `${tooltipItem.label}: ${formatarReal(valor)} (${percentual}%)`;
            },
          },
        },
        datalabels: {
          display: true,
          color: '#fff',
          font: { weight: 'bold', size: 12 },
          formatter: (value) => {
            const total = valores.reduce((a, b) => a + b, 0);
            const percentual = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
            return `${formatarReal(value)}\n(${percentual}%)`;
          },
          anchor: 'center',
          align: 'center',
        },
      },
    },
  });
};

onMounted(renderChart);
watch(() => props.dados, renderChart, { deep: true });
</script>

<style scoped>
/* Define altura mínima para o gráfico ocupar o espaço e ser responsivo */
canvas {
  width: 100% !important;
  height: 100% !important;
}
</style>
