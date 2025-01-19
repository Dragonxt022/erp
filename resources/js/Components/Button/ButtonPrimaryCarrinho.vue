<template>
  <button class="login-btn-text w-full">
    <img v-if="iconPath" :src="iconPath" alt="icon" class="h-6 w-6 mr-2" />
    <span
      class="text-center text-[15px] font-semibold font-['Figtree'] leading-tight"
    >
      {{ text }}
    </span>
  </button>
</template>

<script setup>
import { defineProps } from 'vue';

// Propriedades do componente
defineProps({
  text: {
    type: String,
    default: 'Label', // Texto padrão para o label
  },
  iconPath: {
    type: String,
    required: false, // Torna o ícone opcional
  },
});
</script>

<style scoped>
.login-btn-text {
  height: 47px;
  background: #d6ecc5;
  border-radius: 10px;
  color: #6db631;
  font-size: 16px;
  font-weight: 600;
  text-align: center;
  line-height: 22px;
  cursor: pointer;
  transition: 0.5s;
  display: flex;
  justify-content: center;
  align-items: center; /* Alinha o conteúdo centralmente */
}

.login-btn-text img {
  margin-right: 8px; /* Adiciona um pequeno espaço entre o ícone e o texto */
}

.login-btn-text:hover {
  background-color: #152310; /* Alterando a cor de fundo no hover */
}
</style>
