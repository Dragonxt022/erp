<template>
  <button
    :type="type"
    @click="onClick"
    class="h-[46px] px-[38px] py-3 mr-2 bg-[#f5f5f5] rounded-[10px] shadow-[2px_2px_10px_0px_rgba(0,0,0,0.04)] flex justify-center items-center gap-2.5"
  >
    <span
      class="text-center text-[#ff0000] text-[15px] font-semibold font-['Figtree'] leading-tight"
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
  background-color: #ffbbbb; /* Altera o cinza para mais escuro ao passar o mouse */
  box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.08);
}
</style>
