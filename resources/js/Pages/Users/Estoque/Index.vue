<template>
  <LayoutFranqueado>
    <Head title="Controle de estoque" />
    <div class="flex justify-between items-center mb-4">
      <!-- Coluna 1: Título e subtítulo -->
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
          Controle de estoque
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

      <table class="min-w-full table-auto">
        <thead>
          <tr>
            <th
              class="px-6 py-3 text-xs font-semibold text-gray-500 TrRedonEsquerda"
            >
              OPERAÇÃO
            </th>
            <th
              class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              QTD.
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Item
            </th>
            <th
              class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Quando
            </th>
            <th
              class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider TrRedonDireita"
            >
              Responsável
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(movimentacao, index) in historicoMovimentacoes"
            :key="index"
          >
            <td
              class="px-6 py-4 text-[16px] font-semibold text-gray-900 flex text-center"
            >
              <img
                :src="
                  movimentacao.operacao === 'Entrada'
                    ? '/storage/images/arrow_back_verde.svg'
                    : '/storage/images/arrow_back_red.svg'
                "
                alt="icon indicativo"
                class="mr-5 text-center"
              />
              {{ movimentacao.operacao === 'Entrada' ? 'Entrada' : 'Retirada' }}
            </td>
            <td
              class="px-6 py-4 text-[16px] text-gray-900 font-semibold text-center"
            >
              {{ movimentacao.quantidade }}
            </td>
            <td class="px-6 py-4 text-[16px] text-gray-900 font-semibold">
              {{ movimentacao.item }}
            </td>
            <td
              class="px-6 py-4 text-[16px] text-gray-900 font-semibold text-center"
            >
              {{ movimentacao.data }}
            </td>
            <td class="px-6 py-4 text-[16px] text-gray-500 text-center">
              {{ movimentacao.responsavel }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';

const valorInicial = ref('0.00');
const valorInsumos = ref('');
const itensNoEstoque = ref('');
const historicoMovimentacoes = ref([]);

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/estoque/incial'); // Ajuste a rota conforme necessário
    // valorInicial.value = data.valorInicial;
    valorInsumos.value = data.valorInsumos;
    itensNoEstoque.value = data.itensNoEstoque;
    historicoMovimentacoes.value = data.historicoMovimentacoes;
  } catch (error) {
    console.error('Erro ao carregar dados do painel:', error);
  }
});
</script>

<style lang="css" scoped>
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
table {
  width: 100%;
  margin-top: 20px;

  border-collapse: collapse;
}

th,
td {
  padding: 12px;
}

th {
  background-color: #164110;
  color: #ffffff;
  margin-bottom: 10px;
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
  cursor: pointer;
}
</style>
