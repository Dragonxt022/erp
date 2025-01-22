<template>
  <div>
    <div
      class="toggle-switch"
      :class="{ 'toggle-on': isActive }"
      @click="toggle"
    >
      <div class="toggle-knob"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineEmits, defineProps, watch } from 'vue';

// Define as propriedades que o componente recebe do pai
const props = defineProps({
  status: {
    type: Boolean,
    default: false, // Definindo um valor padrão para status
  },
});

// Emite o evento de mudança do estado
const emit = defineEmits(['update:status']);

// Estado do toggle (inicia com o valor passado via props)
const isActive = ref(props.status);

// Observa mudanças na propriedade 'status' passada pelo componente pai
watch(
  () => props.status,
  (newStatus) => {
    // Garantindo que o valor de isActive seja atualizado para true ou false
    isActive.value = newStatus;
  }
);

// Alterna o estado do toggle
const toggle = () => {
  isActive.value = !isActive.value;
  emit('update:status', isActive.value); // Passa o valor para o componente pai
};
</script>

<style scoped>
/* Estilo do contêiner do toggle */
.toggle-switch {
  width: 40px;
  height: 24px;
  background-color: #ccc;
  border-radius: 12px;
  position: relative;
  cursor: pointer;
  transition: background-color 0.3s;
}

/* Quando o toggle está ativo */
.toggle-on {
  background-color: #6db631;
}

/* Botão deslizante (knob) */
.toggle-knob {
  width: 20px;
  height: 20px;
  background-color: #fff;
  border-radius: 50%;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: left 0.3s;
}

/* Move o botão para a direita quando ativo */
.toggle-on .toggle-knob {
  left: 18px;
}
</style>
