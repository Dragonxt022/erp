<template>
  <LayoutFranqueado>
    <Head title="Painel" />
    <div class="painel-title">Histórico de despesas</div>
    <div class="painel-subtitle">
      <p>Visualize despesas antigas da sua operação</p>
    </div>

    <!-- Conteúdo da página -->

    <div class="mt-5">
      <div class="grid grid-cols-2 grid-rows-1 gap-4">
        <!-- Segunda coluna -->
        <div class="bg-white rounded-lg p-2">
          <!--  -->
        </div>

        <div class="bg-white rounded-lg p-7">
          <div
            class="text-[#262a27] text-[12px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            <!--  -->
          </div>
        </div>

        <!-- Terceira coluna -->
        <div class="bg-white rounded-lg p-7">
          <div
            class="text-[#262a27] text-[12px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            <!--  -->
          </div>
        </div>
        <div class="bg-white rounded-lg p-4">
          <div
            class="text-[#262a27] text-[12px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            <!--  -->
          </div>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
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
</style>
