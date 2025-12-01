<template>
  <button
    :type="type"
    @click="onClick"
    class="h-[46px] px-[38px] py-3 bg-white rounded-[133.33px] flex justify-center items-center gap-2.5"
  >
    <span
      class="text-center uppercase text-[#5a9f28] upp text-[15px] font-semibold font-['Figtree'] leading-tight"
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
.uppercase {
  text-transform: uppercase;
}
button {
  background: scroll;
  transition: all 0.2s ease;
}

button:hover {
  background-color: #e5ffd2;
  box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.08);
}
</style>
