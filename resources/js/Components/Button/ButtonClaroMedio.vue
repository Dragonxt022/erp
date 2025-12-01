<template>
  <button
    :type="type"
    @click="onClick"
    class="h-[46px] px-[38px] py-3 rounded-[10px] shadow-[2px_2px_10px_0px_rgba(0,0,0,0.04)] flex justify-center items-center gap-2.5"
  >
    <span
      class="text-center text-[15px] font-semibold font-['Figtree'] leading-tight"
    >
      {{ text }}
    </span>
    <!-- Ícone opcional -->
    <img v-if="iconPath" :src="iconPath" alt="icon" class="h-6 w-6" />
  </button>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

// Propriedades do componente
defineProps({
  text: {
    type: String,
    required: true,
    default: 'Nome do Botão',
  },
  type: {
    type: String,
    default: 'button', // Define o tipo do botão
  },
  iconPath: {
    type: String,
    required: false, // Torna o ícone opcional
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
</style>
