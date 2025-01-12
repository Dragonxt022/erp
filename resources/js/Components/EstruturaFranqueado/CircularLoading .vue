<template>
  <div v-if="isLoading" class="flex items-center justify-center">
    <div
      class="w-8 h-8 border-4 border-green-500 border-t-transparent rounded-full animate-spin"
      role="status"
      aria-label="Carregando"
    ></div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

// Propriedade para controle do estado de loading
defineProps({
  isLoading: {
    type: Boolean,
    default: false,
  },
});
</script>

<style scoped>
/* Caso queira ajustar a animação manualmente */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
