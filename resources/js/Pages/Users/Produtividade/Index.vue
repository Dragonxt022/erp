<template>
  <LayoutFranqueado>
    <Head title="Painel" />
    <div class="painel-title">Gestão de produção</div>
    <div class="painel-subtitle">
      <p>Visão geral da produtividade da sua unidade</p>
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
              <img src="/storage/images/em_execucao.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#5856D6] text-[21px] font-semibold font-['Figtree']"
            >
                Em execução
            </div>
          </div>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[20px] tracking-wide"
          >
            6 Blocos
          </div>

          <div class="flex items-center gap-2 mt-[20px]">

            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[8px]"
            >
            1 Bloco iniciou atrasado
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-7">
          <div class="flex items-center gap-2 mb-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/blocos_concluidos.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6DB631] text-[21px] font-semibold font-['Figtree']"
            >
                Concluídos
            </div>
          </div>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[20px] tracking-wide"
          >
            14 Blocos
          </div>

          <div class="flex items-center gap-2 mt-[20px]">


            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[8px]"
            >
              3 blocos para confirmar
            </div>
          </div>
        </div>

        <!-- coluna -->
        <div class="bg-white rounded-lg p-7">
          <div class="flex items-center gap-2 mb-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/blocoParical.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#FF9500] text-[21px] font-semibold font-['Figtree']"
            >
                Parciais
            </div>
          </div>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[20px] tracking-wide"
          >
            2 Blocos
          </div>

          <div class="flex items-center gap-2 mt-[20px]">


            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[8px]"
            >
              Verificar motivo
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg p-4 ">
          <div class="flex items-center gap-2 mb-[17px] mt-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/blocoCancelados.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#FF2D55] text-[21px] font-semibold font-['Figtree']"
            >
                Não executados
            </div>
          </div>

          <div
            class="text-[#262a27] text-[40px] font-bold font-['Figtree'] leading-[20px] tracking-wide"
          >
            2 Blocos
          </div>

          <div class="flex items-center gap-2 mt-[20px]">
            <!-- Texto de descrição -->
            <div
              class="text-[#6d6d6d] text-[13px] font-semibold font-['Figtree'] leading-[8px]"
            >
              2 Blocos sen justificativa
            </div>
          </div>
        </div>

        <!-- coluna -->
        <div class="bg-white rounded-lg p-4 col-span-2">
          <div class="flex items-center gap-2 mb-[15px]">
            <!-- Ícone com fundo mais escuro -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center">
              <!-- Substitua pelo ícone desejado -->
              <img src="/storage/images/crownBlack.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6D6D6E] text-[21px] font-semibold font-['Figtree']"
            >
            Resolvedores
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
              <img src="/storage/images/caracolBleck.svg" alt="icone" />
            </div>

            <!-- Texto de descrição -->
            <div
              class="text-[#6D6D6E] text-[21px] font-semibold font-['Figtree']"
            >
            Mais atrasados
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
            Histórico de operações
          </h3>
          <div class="w-full h-[220px]">

          </div>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';


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
