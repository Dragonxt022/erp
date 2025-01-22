<template>
  <LayoutFranqueado>
    <Head title="Histórico de Caixa" />
    <div class="flex justify-between items-center mb-4">
      <!-- Coluna 1: Título e subtítulo -->
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
            01/09/2024 - 30/09/2024
          </span>
        </div>
      </div>
    </div>
    <div class="mt-5">
      <!-- Ajuste do grid para ser responsivo -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <!-- Coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg sm:text-xl md:text-lg font-semibold text-gray-500">
            Valor inicial
          </h3>
          <div
            class="text-[#262a27] text-[40px] sm:text-[30px] md:text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ {{ valorInicial }}
          </div>
        </div>

        <!-- Coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg sm:text-xl md:text-lg font-semibold text-gray-500">
            Estoque atual
          </h3>
          <div
            class="text-[#262a27] text-[40px] sm:text-[30px] md:text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ {{ valorInsumos }}
          </div>
        </div>

        <!-- Coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg sm:text-xl md:text-lg font-semibold text-gray-500">
            Itens no estoque
          </h3>
          <div
            class="text-[#262a27] text-[40px] sm:text-[30px] md:text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            {{ itensNoEstoque }}
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
          <span class="text-gray-900 text-[17px] font-semibold">
            Filtrar resultados
          </span>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow">
        <!-- Cabeçalho da lista -->
        <div
          class="bg-[#164110] grid grid-cols-5 gap-4 py-4 px-6 text-xs font-semibold text-[#FFFFFF] uppercase tracking-wider rounded-tl-2xl rounded-tr-2xl"
        >
          <span class="text-left px-5">OPERAÇÃO</span>
          <span class="text-center">QTD.</span>
          <span class="text-left">Item</span>
          <span class="text-center">Quando</span>
          <span class="text-center">Responsável</span>
        </div>

        <!-- Dados da lista rolável -->
        <div
          ref="scrollContainer"
          class="overflow-y-auto max-h-96 scroll-hidden"
        >
          <ul class="space-y-2">
            <li
              v-for="(movimentacao, index) in historicoMovimentacoes"
              :key="index"
              :class="{
                'hover:bg-gray-200': true,
                'grid grid-cols-5 gap-2 px-6 py-3 text-[16px]': true,
              }"
            >
              <span class="flex items-center text-gray-900 font-semibold">
                <img
                  :src="
                    movimentacao.operacao === 'Entrada'
                      ? '/storage/images/arrow_back_verde.svg'
                      : '/storage/images/arrow_back_red.svg'
                  "
                  alt="icon indicativo"
                  class="mr-3 w-5 h-5"
                />
                {{
                  movimentacao.operacao === 'Entrada' ? 'Entrada' : 'Retirada'
                }}
              </span>
              <span class="text-center text-gray-900 font-semibold">
                {{ movimentacao.quantidade }}
                {{ movimentacao.unidade === 'unidade' ? 'uni' : 'kg' }}
              </span>
              <span class="text-left text-gray-900 font-medium">
                {{ movimentacao.item }}
              </span>
              <span class="text-center text-gray-600 font-semibold">
                {{ movimentacao.data }}
              </span>
              <span class="text-center text-gray-500 font-semibold">
                {{ movimentacao.responsavel }}
              </span>
            </li>
          </ul>
        </div>

        <!-- Carregando... -->
        <div v-if="loading" class="text-center mt-4">Carregando...</div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';

const valorInicial = ref('0.00');
const valorInsumos = ref('0.00');
const itensNoEstoque = ref('0.00');
const historicoMovimentacoes = ref([]);
const currentPage = ref(1);
const loading = ref(false);
const hasMore = ref(true);
const scrollContainer = ref(null);

// Função para carregar movimentações e dados da API
const loadMovimentacoes = async () => {
  if (loading.value || !hasMore.value) return;

  loading.value = true;

  try {
    // Realiza a requisição para a API
    const { data } = await axios.get(
      `/api/estoque/incial?page=${currentPage.value}`
    );

    // Atualiza os dados de insumos e itens no estoque
    valorInsumos.value = data.valorInsumos;
    itensNoEstoque.value = data.itensNoEstoque;

    // Atualiza o histórico de movimentações
    historicoMovimentacoes.value.push(...data.historicoMovimentacoes.data);

    // Verifica se há mais páginas
    if (
      data.historicoMovimentacoes.current_page >=
      data.historicoMovimentacoes.last_page
    ) {
      hasMore.value = false;
    } else {
      currentPage.value++;
    }
  } catch (error) {
    console.error('Erro ao carregar movimentações:', error);
  } finally {
    loading.value = false;
  }
};

const onScroll = () => {
  const container = scrollContainer.value;
  if (
    container.scrollTop + container.clientHeight >=
    container.scrollHeight - 10
  ) {
    loadMovimentacoes();
  }
};

// Carrega os dados iniciais
onMounted(() => {
  loadMovimentacoes();
  scrollContainer.value.addEventListener('scroll', onScroll);
});

onBeforeUnmount(() => {
  if (scrollContainer.value) {
    scrollContainer.value.removeEventListener('scroll', onScroll);
  }
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
