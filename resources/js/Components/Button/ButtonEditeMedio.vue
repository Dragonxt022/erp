<template>
  <button
    :type="type"
    @click="onClick"
    class="h-[46px] rounded-[10px] flex justify-center items-center gap-2.5"
  >
    <!-- Ícone opcional -->
    <img v-if="iconPath" :src="iconPath" alt="icon" class="h-6 w-6" />
    <span
      class="text-center text-[#262a27] text-[15px] font-semibold font-['Figtree'] leading-tight"
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
