<template>
  <LayoutFranqueado>
    <Head title="Painel" />
    <div class="flex justify-between items-center mb-4">
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-3xl">
          ERP Legado
        </div>
        <div class="painel-subtitle">
          <p class="text-sm sm:text-base md:text-md">
            Acompanhe o desempenho geral
          </p>
        </div>
      </div>
    </div>

    <div class="mt-5 w-full h-screen">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Primeira coluna -->
        <div class="md:row-span-1">
          <div class="bg-white rounded-lg border border-gray-200 p-5 h-full w-full">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">
              Pagamentos
            </h3>
            <div class="compromissos-container flex flex-col gap-2 overflow-hidden">
              <div
                v-for="conta in dados"
                :key="conta.id"
                @click="navigateToContasApagar"
                class="flex justify-between items-center bg-[#F5FAF4] p-3 rounded-lg cursor-pointer hover:bg-gray-100 transition-all ease-in-out duration-300"
              >
                <div class="flex flex-col">
                  <p class="text-[14px] font-medium text-gray-900">
                    {{ conta.nome }}
                  </p>
                  <p class="text-sm text-gray-600">
                    {{ conta.valor_formatado }} - Vence em
                    {{ formatarData(conta.vencimento) }}
                  </p>
                </div>

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
        <div class="flex flex-col items-center justify-center w-full bg-white rounded-lg p-12 border border-gray-200 group">
          <img src="https://login.taiksu.com.br/applications/6967173ec2616.png" alt="Logo" class="w-16 h-16 mb-4 group-hover:scale-110 transition-all ease-in-out duration-300">
          <h2 class="text-lg font-semibold text-gray-700 text-center">Estamos migrando para o visão geral</h2>
          <p class="text-sm text-gray-500 text-center">Veja os dados da unidade no novo app.</p>
          <a href="https://login.taiksu.com.br/?redirect_uri=https%3A%2F%2Fdashboard.taiksu.com.br%2Fcallback" class="text-sm text-white bg-green-500 px-6 shadow-xl hover:shadow-sm transition-shadow duration-400 py-1 rounded-full hover:bg-green-600 transition-all ease-in-out duration-300 mt-4 text-center">Acessar</a>
        </div>

        <!-- Quarta linha que ocupa 3 colunas -->
        <div class="hidden bg-white rounded-lg p-4 col-span-1 md:col-span-3">
          <h3 class="text-lg font-semibold text-gray-700">Faturamento diário</h3>
          <div class="w-full h-[20px]">
            <canvas ref="barChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>


<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
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
// Buscar as contas da API
const fetchDados = async () => {
  try {
    const response = await axios.get('/api/cursto/listar-contas-a-pagar');
    dados.value = response.data.data;
  } catch (error) {
    console.error('Erro ao carregar os dados:', error);
  }
};

// Chamada ao montar o componente
onMounted(fetchDados);

// Selecionar uma conta
const selecionarDados = (conta) => {
  emit('dado-selecionado', conta);
};

// Filtragem das contas por pesquisa
const filteredDados = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return dados.value.filter(
    (conta) =>
      conta.nome.toLowerCase().includes(query) ||
      String(conta.valor).includes(query)
  );
});

// Formatar a data
const formatarData = (data) => {
  const partes = data.split('-'); // Divide "YYYY-MM-DD"
  return `${partes[2]}/${partes[1]}/${partes[0]}`; // Retorna "DD/MM/YYYY"
};



// Retornar o ícone do status
const statusIcons = {
  pendente: '/storage/images/check_circle_laranja.svg',
  pago: '/storage/images/check_circle_verde.svg',
  agendada: '/storage/images/agendada.svg',
  atrasado: '/storage/images/atrasada.svg',
};

const getStatusIcon = (status) => {
  return statusIcons[status] || '/storage/images/check_circle_laranja.svg';
};

// Cores do badge
const statusColors = {
  pendente: 'bg-orange-100 text-orange-700',
  pago: 'bg-green-100 text-green-700',
  agendada: 'bg-blue-100 text-blue-700',
  atrasado: 'bg-red-100 text-red-700',
};

const getStatusClass = (status) => {
  return statusColors[status.toLowerCase()] || 'bg-gray-100 text-gray-700';
};


const fetchData = async () => {
    try {
        const response = await axios.get(
            '/api/painel-analitycos/faturamento-por-dia-mes'
        );
        const data = response.data;

        // Obtém o mês atual no formato "MM"
        const mesAtual = new Date().getMonth() + 1; // getMonth() retorna de 0 a 11, por isso somamos 1

        // Filtra apenas os dias do mês vigente
        const faturamentoArray = Object.values(data.faturamento)
            .filter((item) => {
                const mesDoItem =
                    parseInt(item.dia) <= new Date().getDate() ? mesAtual : mesAtual - 1; // Se o dia for maior que o dia atual, provavelmente é do mês anterior
                return mesDoItem === mesAtual;
            })
            .sort((a, b) => a.dia - b.dia); // Ordena do menor para o maior

        if (Array.isArray(faturamentoArray)) {
            diasLabels.value = faturamentoArray.map((item) => item.dia);
            faturamentoDias.value = faturamentoArray.map((item) =>
                parseFloat(item.total)
            ); // Converte "total" para número
            updateChart();
        } else {
            console.error(
                'Erro: Os dados recebidos não estão no formato esperado.',
                data
            );
        }
    } catch (error) {
        console.error('Erro ao buscar dados:', error);
    }
};

// Função para atualizar o gráfico com os dados reais
const updateChart = () => {
    if (chartInstance.value) {
        chartInstance.value.destroy(); // Destroi o gráfico anterior antes de criar um novo
    }

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
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return value >= 1000 ? `${value / 1000} mil` : value; // Formatação para milhar
                        },
                    },
                },
            },
        },
    });
};

</script>

<style lang="css" scoped>
.painel-title {
    font-weight: 700;
    color: #262a27;

}

.painel-subtitle {
    color: #6d6d6e;
}

.compromissos-container {
    max-height: 400px;
    /* Defina a altura máxima desejada para a coluna */
    overflow-y: auto;
    /* Habilita rolagem vertical */
}

/* Esconde a barra de rolagem */
.compromissos-container::-webkit-scrollbar {
    display: none;
    /* Esconde a barra de rolagem no Chrome, Safari, e Edge */
}

.compromissos-container {
    -ms-overflow-style: none;
    /* Esconde a barra de rolagem no Internet Explorer */
    scrollbar-width: none;
    /* Esconde a barra de rolagem no Firefox */
}
</style>
