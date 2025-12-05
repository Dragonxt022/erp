<template>
  <a 
    class="notification-link" 
    :href="notificationUrl"
    target="_blank"
    rel="noopener noreferrer"
  >
    <img 
      :src="iconSrc" 
      alt="Notificações" 
      class="notification-icon"
    />
  </a>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';

// Estado reativo
const userId = ref(null);
const userToken = ref(null);
const hasNotifications = ref(false);
const isLoading = ref(true);

// URLs computadas
const notificationUrl = computed(() => {
  return userToken.value 
    ? `https://alertas.taiksu.com.br/callback?token=${userToken.value}`
    : 'https://alertas.taiksu.com.br';
});

const iconSrc = computed(() => {
  if (isLoading.value) {
    return 'https://login.taiksu.com.br/frontend/img/sinale0.png';
  }
  return hasNotifications.value
    ? 'https://login.taiksu.com.br/frontend/img/sino-alerta-com-mensagem.png'
    : 'https://login.taiksu.com.br/frontend/img/sinale0.png';
});

// Função para buscar dados do usuário
const fetchUserData = async () => {
  try {
    const response = await fetch('/api/navbar-profile', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Erro ao carregar perfil do usuário');
    }

    const data = await response.json();
    
    if (data.status === 'success' && data.data) {
      userId.value = data.data.id;
      // Usa o rh_token se disponível, caso contrário usa o ID
      userToken.value = data.data.rh_token || data.data.id;
      
      // Após obter o userId, buscar status das notificações
      await checkNotificationStatus();
    }
  } catch (error) {
    console.error('Erro ao buscar dados do usuário:', error);
  } finally {
    isLoading.value = false;
  }
};

// Função para verificar status das notificações
const checkNotificationStatus = async () => {
  if (!userId.value) return;

  try {
    const url = `https://alertas.taiksu.com.br/api/status/${userId.value}`;
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Erro ao verificar status das notificações');
    }

    const data = await response.json();
    hasNotifications.value = data.status === true;
  } catch (error) {
    console.error('Erro ao consultar status das notificações:', error);
    hasNotifications.value = false;
  }
};

// Intervalo para verificar notificações periodicamente
let checkInterval;

onMounted(async () => {
  await fetchUserData();
  
  // Verifica notificações a cada 5 minutos
  checkInterval = setInterval(() => {
    checkNotificationStatus();
  }, 300000); // 5 minutos
});

onBeforeUnmount(() => {
  if (checkInterval) {
    clearInterval(checkInterval);
  }
});
</script>

<style scoped>
.notification-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem;
  border-radius: 9999px;
  transition: background-color 0.3s ease;
  text-decoration: none;
}

.notification-link:hover {
  background-color: #e5e7eb;
}

.notification-icon {
  height: 1.75rem;
  width: 1.75rem;
  margin-left: 0.5rem;
}

@media (min-width: 768px) {
  .notification-icon {
    margin-left: 0;
  }
}
</style>