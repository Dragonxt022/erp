<template>
  <LayoutFranqueadora>
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
      <div class="grid grid-cols-4 grid-rows-1 gap-4">
        <!--coluna -->
        <div class="bg-white rounded-lg p-7">
          
          <div class="flex items-center gap-2 mb-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/rocket.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6DB631] text-[21px] font-semibold font-['Figtree']"
            >
                Maior produção
            </div>
          </div>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[20px] tracking-wide"
          >
            JI - Paraná
          </div>

          <div class="flex items-center gap-2 mt-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/trending_up.svg" alt="" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[8px]"
            >
            12% acima da média
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-7">
          <div class="flex items-center gap-2 mb-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/target.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#FF2D55] text-[21px] font-semibold font-['Figtree']"
            >
                Mais Faltas
            </div>
          </div>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[20px] tracking-wide"
          >
            Ariquemes
          </div>

          <div class="flex items-center gap-2 mt-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/trending_up.svg" alt="" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[8px]"
            >
              5% abaixo da média
            </div>
          </div>
        </div>

        <!-- coluna -->
        <div class="bg-white rounded-lg p-7">
          <div class="flex items-center gap-2 mb-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/crown.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#FF9500] text-[21px] font-semibold font-['Figtree']"
            >
                Mais atrasados
            </div>
          </div>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[20px] tracking-wide"
          >
            Vilhena
          </div>

          <div class="flex items-center gap-2 mt-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/trending_down.svg" alt="" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[8px]"
            >
              9% abaixo da média
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-4 ">
          <div class="flex items-center gap-2 mb-[17px] mt-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/crown-1.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#5856D6] text-[21px] font-semibold font-['Figtree']"
            >
                Talento Operacional
            </div>
          </div>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[20px] tracking-wide"
          >
            Joaquim
          </div>

          <div class="flex items-center gap-2 mt-[20px]">
            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[8px]"
            >
              Auxiliador, ágil e pontual
            </div>
          </div>
        </div>

        <!-- coluna -->
        <div class="bg-white rounded-lg p-4 col-span-2">
          <div class="flex items-center gap-2 mb-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/thumb_down.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6D6D6E] text-[21px] font-semibold font-['Figtree']"
            >
            Top atividades abaixo do esperado
            </div>
          </div>
          <div class="w-full h-[220px]">
            <div class="flex flex-col gap-2 compromissos-container">
            <div
              class="flex justify-between items-center bg-[#F5FAF4] px-5 py-2 rounded-lg hover:bg-gray-100 transition-all duration-300"
            >
              <div class="flex items-center gap-3">
                <p class="text-[20px] font-medium text-gray-900">
                  #1
                </p>
                
                <p class="text-[20px] font-medium text-[#262a27]">
                  Hot Filadelfia
                </p>
              </div>
              
            </div>
            <div
              class="flex justify-between items-center bg-[#F5FAF4] px-5 py-2 rounded-lg hover:bg-gray-100 transition-all duration-300"
            >
              <div class="flex items-center gap-3">
                <p class="text-[20px] font-medium text-gray-900">
                  #2
                </p>
                
                <p class="text-[20px] font-medium text-[#262a27] text-gray-900">
                  Montar Combo Taiksu
                </p>
              </div>
              
            </div>
          </div>
          </div>
        </div>
        <!-- coluna -->
        <div class="bg-white rounded-lg p-4 col-span-2">
          <div class="flex items-center gap-2 mb-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/arrow_outward.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6D6D6E] text-[21px] font-semibold font-['Figtree']"
            >
            Top dias com mais produção
            </div>
          </div>
          
          <div class="w-full h-[220px]">
            <div class="flex flex-col gap-2 compromissos-container">
            <div
              class="flex justify-between items-center bg-[#F5FAF4] px-5 py-2 rounded-lg hover:bg-gray-100 transition-all duration-300"
            >
              <div class="flex items-center gap-3">
                <p class="text-[20px] font-medium text-gray-900">
                  #1
                </p>
                
                <p class="text-[20px] font-medium text-[#262a27]">
                  Domingo
                </p>
              </div>
              
            </div>
            <div
              class="flex justify-between items-center bg-[#F5FAF4] px-5 py-2 rounded-lg hover:bg-gray-100 transition-all duration-300"
            >
              <div class="flex items-center gap-3">
                <p class="text-[20px] font-medium text-gray-900">
                  #2
                </p>
                
                <p class="text-[20px] font-medium text-[#262a27] text-gray-900">
                  Sexta-feira
                </p>
              </div>
              
            </div>
          </div>
          </div>
        </div>

        <!-- coluna -->
        <div class="bg-white rounded-lg p-4 col-span-4">
          <h3 class="text-lg font-semibold text-gray-700">
            Produção diária
          </h3>
          <div class="w-full h-[220px]">
            <canvas ref="barChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </LayoutFranqueadora>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';
import LayoutFranqueadora from '@/Layouts/LayoutFranqueadora.vue';

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
            label: 'Faturamento diário',
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

.compromissos-container {
  max-height: 170px; /* Defina a altura máxima desejada para a coluna */
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
