<template>
  <div
    class="w-full h-[18.63px] text-[#6d6d6d] text-[15px] font-semibold font-['Figtree'] leading-tight mt-3 mb-2"
  >
    {{ text }}
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

// Propriedades do componente
defineProps({
  text: {
    type: String,
    default: 'Label', // Texto padrão para o label
  },
});
</script>
