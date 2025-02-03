<template>
  <LayoutFranqueado>
    <Head title="Painel" />
    <div class="flex justify-between items-center mb-4">
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
          Visão geral da loja
        </div>
        <div class="painel-subtitle">
          <p class="text-sm sm:text-base md:text-lg">
            Acompanhe o desempenho geral
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
            <CalendarSimples />
          </span>
        </div>
      </div>
    </div>
    <div class="mt-5">
      <div class="grid grid-cols-3 grid-rows-1 gap-4">
        <!-- Primeira coluna -->
        <div class="row-span-3">
          <div class="bg-white rounded-lg p-5 h-full w-full">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">
              Compromissos
            </h3>
            <!-- Contêiner com altura máxima e rolagem -->
            <div
              class="compromissos-container flex flex-col gap-2 overflow-hidden"
            >
              <!-- Container de compromissos -->
              <div
                v-for="conta in dados"
                :key="conta.id"
                @click="navigateToContasApagar"
                class="flex justify-between items-center bg-[#F5FAF4] p-3 rounded-lg cursor-pointer hover:bg-gray-100 transition-all ease-in-out duration-300"
              >
                <!-- Informações da conta -->
                <div class="flex flex-col">
                  <p class="text-[14px] font-medium text-gray-900">
                    {{ conta.nome }}
                  </p>
                  <p class="text-sm text-gray-600">
                    {{ conta.valor_formatado }} - Vencimento
                    {{ formatarData(conta.vencimento) }}
                  </p>
                </div>

                <!-- Ícone de status -->
                <div>
                  <img
                    :src="getStatusIcon(conta.status)"
                    :alt="'Status: ' + conta.status"
                    class="w-6 h-6 object-contain transition-transform transform hover:scale-105"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Segunda coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Faturamento</h3>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ {{ totalCaixas }}
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <!-- <img src="/storage/images/trending_up.svg" alt="" /> -->
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              0% coletando dados
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-7">
          <div class="flex items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-700">CMV</h3>
            <button @click="openModal" class="ml-2">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="15"
                height="15"
                viewBox="0 0 24 24"
                style="fill: rgba(0, 0, 0, 1); transform: ; msfilter: "
              >
                <path
                  d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"
                ></path>
              </svg>
            </button>
          </div>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ {{ cmv }}
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <!-- <img src="/storage/images/trending_up.svg" alt="" /> -->
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              0% coletando dados
            </div>
          </div>
          <!-- modal de informações do cmv -->
          <div
            v-if="isModalOpen"
            class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50"
          >
            <div class="bg-white rounded-lg p-7 w-[350px] shadow-lg">
              <h3 class="text-lg font-semibold text-gray-700 mb-4">
                Detalhes do CMV
              </h3>
              <div class="text-sm text-gray-600 space-y-2">
                <p>
                  <span class="font-semibold text-gray-800">Período:</span>
                  {{ start_date }} - {{ end_date }}
                </p>
                <p>
                  <span class="font-semibold text-gray-800">
                    Saldo Inicial:
                  </span>
                  R$ {{ saldoEstoqueInicial }}
                </p>
                <p>
                  <span class="font-semibold text-gray-800">Entradas:</span>
                  R$ {{ entradasDurantePeriodo }}
                </p>
                <p>
                  <span class="font-semibold text-gray-800">Saldo Final:</span>
                  R$ {{ saldoEstoqueFinal }}
                </p>
                <p class="text-lg font-bold text-gray-900 mt-3">
                  <span class="font-semibold">CMV:</span>
                  R$ {{ cmv }}
                </p>
              </div>
              <button
                @click="closeModal"
                class="mt-4 text-red-800 rounded-md transition"
              >
                Fechar
              </button>
            </div>
          </div>
        </div>

        <!-- Terceira coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Ticket médio</h3>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ {{ ticketMedio }}
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <!-- <img src="/storage/images/trending_down.svg" alt="" /> -->
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              0% coletando dados
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg p-4">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Pedidos</h3>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            {{ quantidadePedidos }}
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <!-- <img src="/storage/images/trending_up.svg" alt="" /> -->
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              0% coletando dados
            </div>
          </div>
        </div>

        <!-- Quarta coluna -->
        <div class="bg-white rounded-lg p-4 col-span-3">
          <h3 class="text-lg font-semibold text-gray-700">
            Faturamento diário
          </h3>
          <div class="w-full h-[220px]">
            <canvas ref="barChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { Chart, registerables } from 'chart.js';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import CalendarSimples from '@/Components/Filtros/CalendarSimples.vue';

// Registrar todos os componentes necessários do Chart.js
Chart.register(...registerables);

// Referência para o elemento canvas do gráfico
const barChart = ref(null);
const chartInstance = ref(null); // Armazena a instância do gráfico
const faturamentoDias = ref([]); // Valores de faturamento diário
const diasLabels = ref([]); // Labels dos últimos 7 dias

const dados = ref([]);

// Dados do CMV
const start_date = ref('0,00');
const end_date = ref('0,00');
const saldoEstoqueInicial = ref('0,00');
const entradasDurantePeriodo = ref('0,00');
const saldoEstoqueFinal = ref('0,00');
const cmv = ref('0,00');

// Caixa e tickets
const totalCaixas = ref('0,00');
const quantidadePedidos = ref(0);
const ticketMedio = ref('0,00');

// Estado do modal
const isModalOpen = ref(false);

const navigateToContasApagar = () => {
  Inertia.replace(route('franqueado.contasApagar'));
};

// Estado do modal
const openModal = () => {
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

// Função para buscar os dados analíticos
const fetchDataCMV = async (startDate = null, endDate = null) => {
  try {
    // Monta a URL condicionalmente
    let url = `/api/painel-analitycos/calcular-cmv`;
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
// Função para buscar os dados analíticos
const fetchDataFaturamento = async (startDate = null, endDate = null) => {
  try {
    // Monta a URL condicionalmente
    let url = `/api/painel-analitycos/calcular-fluxo-caixa`;
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
    totalCaixas.value = data.total_caixas || '0,00';
    quantidadePedidos.value = data.quantidade_pedidos || 0;
    ticketMedio.value = data.ticket_medio || '0,00';
  } catch (error) {
    console.error('Erro ao buscar dados analíticos:', error);
  }
};

// Chama a função ao montar o componente com valores padrão
onMounted(() => {
  fetchDataCMV(); // Agora aceita chamadas sem parâmetros
  fetchDataFaturamento();
  fetchDados();
  fetchData();
  updateChart();
});

// Buscar as contas da API
const fetchDados = async () => {
  try {
    const response = await axios.get('/api/cursto/listar-contas-a-pagar');
    dados.value = response.data.data;
  } catch (error) {
    console.error('Erro ao carregar os dados:', error);
  }
};

// Função para buscar os dados do backend
const fetchData = async () => {
  try {
    const response = await axios.get(
      '/api/painel-analitycos/faturamento-por-semana'
    );
    const data = response.data;

    if (data.faturamento) {
      diasLabels.value = data.faturamento.map((item) => item.dia);
      faturamentoDias.value = data.faturamento.map((item) => item.total);
      updateChart();
    }
  } catch (error) {
    console.error('Erro ao buscar dados:', error);
  }
};

// Formatar a data
const formatarData = (data) => {
  const partes = data.split('-'); // Divide "YYYY-MM-DD"
  return `${partes[2]}/${partes[1]}/${partes[0]}`; // Retorna "DD/MM/YYYY"
};

// Retornar o ícone do status
const getStatusIcon = (status) => {
  return status === 'pendente'
    ? '/storage/images/check_circle_laranja.svg'
    : '/storage/images/check_circle_verde.svg';
};

// Função para atualizar o gráfico com os dados reais
const updateChart = () => {
  if (chartInstance.value) {
    chartInstance.value.destroy(); // Destroi o gráfico anterior antes de criar um novo
  }

  chartInstance.value = new Chart(barChart.value.getContext('2d'), {
    type: 'bar',
    data: {
      labels: diasLabels.value, // Dias numerados de 1 a 7
      datasets: [
        {
          label: 'Faturamento Diário',
          data: faturamentoDias.value,
          backgroundColor: 'rgba(75, 192, 75, 0.6)', // Cor de fundo das barras
          borderColor: 'rgba(75, 192, 75, 1)', // Cor das bordas
          borderWidth: 2,
          borderRadius: 8, // Arredondamento das bordas
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
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return value >= 1000 ? `${value / 1000}mil` : value; // Formatação para mostrar milhar
            },
          },
        },
      },
    },
  });
};
//
</script>

<style lang="css" scoped>
.painel-title {
  font-size: 34px;
  line-height: 15px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  margin-bottom: 10px; /* Espaçamento inferior */
}

.painel-subtitle {
  font-size: 17px;
  line-height: 25px;
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
</style>
