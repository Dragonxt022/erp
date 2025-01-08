<template>
  <AppLayout>
    <Head title="Painel" />
    <div class="painel-title">Visão geral da loja</div>
    <div class="painel-subtitle">
      <p>Acompanhe o desempenho geral</p>
    </div>

    <!-- Conteúdo da página -->
    <!-- <div class="mt-5 col-span-3">
      <Calendario />
    </div> -->
    <div class="mt-5">
      <div class="grid grid-cols-3 grid-rows-1 gap-4">
        <!-- Primeira coluna -->
        <div class="row-span-2">
          <div class="bg-white rounded-lg p-7">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">
              Compromissos
            </h3>
            <div class="flex bg-[#F5FAF4] items-center gap-4 p-3 rounded-lg">
              <!-- Container para o ícone com fundo mais escuro -->
              <div
                class="bg-[#E1FCDE] p-3 rounded-lg flex items-center justify-center shadow-inner"
              >
                <img src="/storage/images/groups_2.svg" alt=" " />
              </div>
              <!-- Texto ao lado do ícone -->
              <div>
                <p
                  class="text-[#262a27] text-[15px] font-semibold leading-tight"
                >
                  Holerites
                </p>
                <p class="text-sm text-gray-500">3 outubro</p>
              </div>
              <!-- Valor à direita -->
              <p class="text-[#3B622B] ml-auto font-semibold leading-tight">
                R$ 17.620,85
              </p>
            </div>
            <!-- Segundo item -->
            <div
              class="flex bg-[#F5FAF4] items-center gap-4 p-3 rounded-lg mt-4"
            >
              <!-- Container para o ícone com fundo mais escuro -->
              <div
                class="bg-[#FCF0DE] p-3 rounded-lg flex items-center justify-center shadow-inner"
              >
                <img src="/storage/images/electric_bolt.svg" alt=" " />
              </div>
              <!-- Texto ao lado do ícone -->
              <div>
                <p
                  class="text-[#262a27] text-[15px] font-semibold leading-tight"
                >
                  Energia Elétrica
                </p>
                <p class="text-sm text-gray-500">10 outubro</p>
              </div>
              <!-- Valor à direita -->
              <p class="text-[#3B622B] ml-auto font-semibold leading-tight">
                R$ 720,86
              </p>
            </div>

            <!-- Terceiro item -->
            <div
              class="flex bg-[#F5FAF4] items-center gap-4 p-3 rounded-lg mt-4"
            >
              <!-- Container para o ícone com fundo mais escuro -->
              <div
                class="bg-[#F3DEFC] p-3 rounded-lg flex items-center justify-center shadow-inner"
              >
                <img src="/storage/images/handshake.svg" alt=" " />
              </div>
              <!-- Texto ao lado do ícone -->
              <div>
                <p
                  class="text-[#262a27] text-[15px] font-semibold leading-tight"
                >
                  Fornecedor
                </p>
                <p class="text-sm text-gray-500">20 outubro</p>
              </div>
              <!-- Valor à direita -->
              <p class="text-[#3B622B] ml-auto font-semibold leading-tight">
                R$ 10.250,40
              </p>
            </div>

            <!-- Quarto item -->
            <div
              class="flex bg-[#F5FAF4] items-center gap-4 p-3 rounded-lg mt-4"
            >
              <!-- Container para o ícone com fundo mais escuro -->
              <div
                class="bg-[#BFE1EB] p-3 rounded-lg flex items-center justify-center shadow-inner"
              >
                <img src="/storage/images/propane_tank.svg" alt=" " />
              </div>
              <!-- Texto ao lado do ícone -->
              <div>
                <p
                  class="text-[#262a27] text-[15px] font-semibold leading-tight"
                >
                  Gás
                </p>
                <p class="text-sm text-gray-500">25 outubro</p>
              </div>
              <!-- Valor à direita -->
              <p class="text-[#3B622B] ml-auto font-semibold leading-tight">
                R$ 1.210,00
              </p>
            </div>
          </div>
        </div>
        <!-- Segunda coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Faturamento</h3>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ 217.113,95
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/trending_up.svg" alt="" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              12% maior que no período anterior
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">CMV</h3>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ 65.113,90
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/trending_up.svg" alt="" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              7% maior que no período anterior
            </div>
          </div>
        </div>

        <!-- Terceira coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Ticket médio</h3>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ 154,30
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/trending_down.svg" alt="" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              3% menor que no período anterior
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg p-4">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Pedidos</h3>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            1142
          </div>

          <div class="flex items-center gap-2 mt-[35px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/trending_up.svg" alt="" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[18px]"
            >
              10% maior que no período anterior
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
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

import { ref, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';
import Calendario from '@/Components/Estrutura/Calendario.vue';

// Registrar todos os componentes necessários do Chart.js
Chart.register(...registerables);

const barChart = ref(null);

onMounted(() => {
  if (barChart.value) {
    new Chart(barChart.value.getContext('2d'), {
      type: 'bar',
      data: {
        labels: Array.from({ length: 31 }, (_, i) => i + 1), // Labels de 1 a 31
        datasets: [
          {
            label: 'Faturamento',
            data: [
              12000, 19000, 3000, 5000, 20000, 15000, 8000, 11000, 7000, 9000,
              18000, 16000, 14000, 17000, 20000, 13000, 21000, 22000, 23000,
              24000, 25000, 26000, 27000, 28000, 29000, 30000, 31000, 32000,
              33000, 34000, 35000,
            ],
            backgroundColor: 'rgba(75, 192, 75, 0.6)', // Tom de verde
            borderColor: 'rgba(75, 192, 75, 1)', // Cor da borda verde
            borderWidth: 2,
            borderRadius: 8, // Arredondar as pontas
            borderSkipped: false, // Aplicar bordas arredondadas em todas as extremidades
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false, // Permite que o gráfico se ajuste ao tamanho do contêiner
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
          },
        },
      },
    });
  }
});
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
</style>
