<template>
  <LayoutFranqueado>
    <Head title="Fluxo do Caixa" />
    <div class="flex justify-between items-center mb-4">
      <!-- Coluna 1: Título e subtítulo -->
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
          Fluxo do caixa
        </div>
        <div class="painel-subtitle">
          <p class="text-sm sm:text-base md:text-lg">
            Acompanhe seu negócio em tempo real
          </p>
        </div>
      </div>
    </div>
    <div class="mt-5">
      <!-- Ajuste do grid para ser responsivo -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4">
        <!-- Coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg sm:text-xl md:text-lg font-semibold text-gray-500">
            Métodos de pagamentos
          </h3>
          <div
            class="text-[#262a27] text-[40px] sm:text-[30px] md:text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            <div
              class="w-[560px] h-[50px] flex justify-center items-center gap-3"
            >
              <!-- Ícone -->
              <div class="w-6 h-6 bg-[#d9d9d9]"></div>

              <!-- Texto da espécie -->
              <div
                class="w-[250px] text-[#262a27] text-[17px] font-semibold font-['Figtree'] leading-snug"
              >
                Nome do metodo
              </div>

              <!-- Espaço flexível -->
              <div class="flex-grow h-7 bg-white"></div>

              <!-- Valor -->

              <InputModel
                v-model="porcentagem"
                class="w-[30%]"
                placeholder="R$ 0,00"
                @input="atualizarMetodo"
              />
            </div>
          </div>
        </div>

        <!-- Coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg sm:text-xl md:text-lg font-semibold text-gray-500">
            Canais de Vendas
          </h3>
          <div
            class="text-[#262a27] text-[40px] sm:text-[30px] md:text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ {{ valorInsumos }}
          </div>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
import InputModel from '@/Components/Inputs/InputModel.vue';

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
