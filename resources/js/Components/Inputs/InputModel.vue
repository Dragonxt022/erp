<template>
  <div class="relative flex items-center w-full">
    <!-- Input de texto -->
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
    />
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

// Propriedades do componente
defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Digite aqui...',
  },
});
</script>

<style scoped>
/* Adiciona transições para foco no div do input */
div:focus-within {
  transition: all 0.3s ease;
}
</style>
