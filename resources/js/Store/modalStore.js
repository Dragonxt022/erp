// src/stores/modalStore.js
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useModalStore = defineStore('modal', () => {
  const isCadastroVisible = ref(false); // Estado do modal de cadastro
  const isDetalhesVisible = ref(false); // Estado do modal de detalhes

  // Alterna a visibilidade do modal de cadastro
  const toggleCadastro = () => {
    isCadastroVisible.value = !isCadastroVisible.value;
    isDetalhesVisible.value = false; // Oculta os detalhes quando o cadastro é exibido
  };

  // Exibe o modal de cadastro
  const showCadastro = () => {
    isCadastroVisible.value = true;
    isDetalhesVisible.value = false;
  };

  // Exibe o modal de detalhes
  const showDetalhes = () => {
    isCadastroVisible.value = false;
    isDetalhesVisible.value = true;
  };

  // Fecha todos os modais
  const hideAll = () => {
    isCadastroVisible.value = false;
    isDetalhesVisible.value = false;
  };

  return {
    isCadastroVisible,
    isDetalhesVisible,
    toggleCadastro,
    showCadastro,
    showDetalhes,
    hideAll,
  };
});
