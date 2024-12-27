<template>
  <div v-if="message" class="notification" :class="type">
    {{ message }}
  </div>
</template>

<script setup>
import { ref, defineProps, watch } from 'vue';

const props = defineProps({
  message: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'success', // tipo 'success' ou 'error'
  },
});

const notificationVisible = ref(false);

// Torna a notificação visível
watch(
  () => props.message,
  (newMessage) => {
    if (newMessage) {
      notificationVisible.value = true;
      setTimeout(() => {
        notificationVisible.value = false;
      }, 3000); // A notificação desaparece após 3 segundos
    }
  }
);
</script>

<style scoped>
.notification {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 10px 20px;
  border-radius: 5px;
  color: white;
  font-size: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: opacity 0.3s ease;
}

.notification.error {
  background-color: #f44336; /* Cor vermelha para erro */
}

.notification.success {
  background-color: #4caf50; /* Cor verde para sucesso */
}
</style>
