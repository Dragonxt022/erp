<template>
  <button
    :type="type"
    @click="onClick"
    class="h-[46px] px-[38px] py-3 bg-[#6db631] rounded-[10px] shadow-[2px_2px_10px_0px_rgba(0,0,0,0.04)] flex justify-center items-center gap-2.5"
  >
    <span
      class="text-center text-[#f3f8f3] text-[15px] font-semibold font-['Figtree'] leading-tight"
    >
      {{ text }}
    </span>
  </button>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

// Propriedades do componente
defineProps({
  text: {
    type: String,
    required: true,
    default: 'Cadastrar',
  },
  type: {
    type: String,
    default: 'button', // Define o tipo do botão
  },
});

// Emissão de evento para ações de clique
const emit = defineEmits(['click']);

// Função de clique
const onClick = () => {
  emit('click');
};
</script>

<style scoped>
/* Hover e transições */
button {
  transition: all 0.2s ease;
}

button:hover {
  background-color: #5a9f28;
  box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.08);
}
</style>
