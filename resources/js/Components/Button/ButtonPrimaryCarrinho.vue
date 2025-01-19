<template>
  <button class="login-btn-text">
    {{ text }}
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
});
</script>

<style css>
.login-btn-text {
  width: 100%;
  height: 47px;
  background: #d6ecc5;
  border-radius: 10px;
  color: #6db631;
  font-size: 17px;
  font-weight: 600;
  text-align: center;
  line-height: 22px;
  cursor: pointer;
  transition: 0.5s;
}

.login-btn-text:hover {
  background-color: #152310; /* Alterando a cor de fundo no hover */
}
</style>
