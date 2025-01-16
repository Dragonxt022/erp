<template>
  <LayoutFranqueado>
    <Head title="Controle de Ponto" />
    <!-- Título principal -->
    <div class="painel-title">Controle de Ponto</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Controle de ponto</p>
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
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}
</style>
