<template>
  <div
    class="w-full mt-1 mb-4 px-4 opacity-80 bg-white rounded-lg border-2 border-[#d7d7db] justify-center items-center inline-flex overflow-hidden focus-within:border-blue-500 focus-within:shadow-lg"
  >
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      class="w-full bg-transparent border-none outline-none text-base text-center font-['Figtree']"
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
/* Ajuste de transições para foco */
div:focus-within {
  transition: all 0.3s ease;
}
</style>
