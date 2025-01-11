<template>
  <LayoutFranqueado>
    <Head title="Controle de estoque" />
    <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
      Controle de estoque
    </div>
    <div class="painel-subtitle">
      <p class="text-sm sm:text-base md:text-lg">
        Acompanhe seu negócio em tempo real
      </p>
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
            R$ 217.113,95
          </div>
        </div>

        <!-- Coluna -->
        <div class="bg-white rounded-lg p-7">
          <h3 class="text-lg sm:text-xl md:text-lg font-semibold text-gray-500">
            Valor em insumos
          </h3>
          <div
            class="text-[#262a27] text-[40px] sm:text-[30px] md:text-[40px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            R$ 65.113,90
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
            R$ 154,30
          </div>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
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
</style>
