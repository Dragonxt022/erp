<template>
  <div class="navbar">
    <div class="navbar-background"></div>

    <div class="navbar-content">
      <!-- Logo e ícones de navegação -->
      <div class="navbar-left">
        <div class="navbar-logo">
          <div class="mr-4">
            <a href="https://www.taiksu.com.br/office/" target="_blank">
              <img src="/storage/images/quadrados_verdes.svg" alt="Quadrados verdes" class="w-full h-full" />
            </a>
          </div>
          <div>
            <a :href="route('franqueado.painel')">
              <img src="/storage/images/logo_tipo_verde.svg" alt="logo tipo verde" class="w-full h-full" />
            </a>
          </div>
        </div>
      </div>

      <!-- Avatar e informações do usuário -->
      <div class="navbar-right">
        <!-- Ícone de Notificações -->
        <div class="notification-container">
          <div class="notification-icon" @click="toggleNotifications">

            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <mask id="mask0_4631_13059" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24"
                height="24">
                <rect width="24" height="24" fill="#D9D9D9" />
              </mask>
              <g mask="url(#mask0_4631_13059)">
                <path
                  d="M2.40039 20.4V18H4.80039V9.6C4.80039 7.94 5.30039 6.465 6.30039 5.175C7.30039 3.885 8.60039 3.04 10.2004 2.64V1.8C10.2004 1.3 10.3754 0.875 10.7254 0.525C11.0754 0.175 11.5004 0 12.0004 0C12.5004 0 12.9254 0.175 13.2754 0.525C13.6254 0.875 13.8004 1.3 13.8004 1.8V2.64C15.4004 3.04 16.7004 3.885 17.7004 5.175C18.7004 6.465 19.2004 7.94 19.2004 9.6V18H21.6004V20.4H2.40039ZM12.0004 24C11.3404 24 10.7754 23.765 10.3054 23.295C9.83539 22.825 9.60039 22.26 9.60039 21.6H14.4004C14.4004 22.26 14.1654 22.825 13.6954 23.295C13.2254 23.765 12.6604 24 12.0004 24ZM7.20039 18H16.8004V9.6C16.8004 8.28 16.3304 7.15 15.3904 6.21C14.4504 5.27 13.3204 4.8 12.0004 4.8C10.6804 4.8 9.55039 5.27 8.61039 6.21C7.67039 7.15 7.20039 8.28 7.20039 9.6V18Z"
                  fill="#6DB631" />
              </g>
            </svg>
            <div v-if="unreadNotifications > 0" class="notification-badge"></div>
          </div>

          <!-- Bandeja de Notificações -->
          <div v-if="showNotifications" class="notification-tray">
            <div class="notification-header">
              <h3>Notificações</h3>
              <span v-if="notifications.length > 0" @click="markAllAsRead" class="mark-all-read">Marcar todas como
                lidas</span>
            </div>

            <div class="notification-list">
              <div v-if="notificationsLoading" class="notification-loading">
                <div class="notification-skeleton"></div>
                <div class="notification-skeleton"></div>
              </div>
              <div v-else-if="notifications.length === 0" class="no-notifications">
                Nenhuma notificação no momento
              </div>
              <div v-else v-for="notification in notifications" :key="notification.id" class="notification-item"
                :class="{ 'unread': notification.lida === 0 }" @click="markAsRead(notification.id)">
                <div class="notification-content">
                  <div class="notification-title">{{ notification.titulo }}</div>
                  <div class="notification-message">{{ notification.mensagem }}</div>
                  <div class="notification-time">{{ notification.tempo }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <Link v-if="user" :href="route('franqueado.perfil')" class="user-info" :class="{ loading: !user }">
        <div class="user-info" v-if="user">
          <img :src="profilePhoto" alt="Avatar" class="avatar" />
          <div class="user-details">
            <div class="user-name">{{ user.name }}</div>
            <div class="user-location">
              {{ unidade?.cidade || 'Taiksu Franchising' }}
            </div>
          </div>
        </div>
        <div class="user-info loading" v-else>
          <div class="avatar-skeleton"></div>
          <div class="user-details-skeleton">
            <div class="name-skeleton"></div>
            <div class="location-skeleton"></div>
          </div>
        </div>
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const user = ref(null);
const unidade = computed(() => user.value?.unidade);
const notifications = ref([]);
const notificationsLoading = ref(false);
const showNotifications = ref(false);
const unreadNotifications = computed(() => {
  return notifications.value.filter(notification => notification.lida === 0).length;
});

const profilePhoto = computed(() => {
  return (
    user.value?.profile_photo_url ||
    `https://ui-avatars.com/api/?name=${getInitials(
      user.value?.name
    )}&color=7F9CF5&background=EBF4FF`
  );
});

// Função para pegar as iniciais do nome
const getInitials = (name) => {
  if (!name) return '*';
  const nameParts = name.split(' ');
  return nameParts.map((part) => part.charAt(0).toUpperCase()).join('');
};

// Função para buscar os dados do perfil
const fetchUserProfile = async () => {
  try {
    const response = await fetch('/api/navbar-profile', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Erro ao carregar perfil do usuário');
    }

    const data = await response.json();
    user.value = data.data;
  } catch (error) {
    console.error('Erro ao buscar perfil:', error);
    user.value = null;
  }
};

// Função para buscar notificações
const fetchNotifications = async () => {
  notificationsLoading.value = true;
  try {
    const response = await fetch('/api/notificacoes', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Erro ao carregar notificações');
    }

    const data = await response.json();
    notifications.value = data.data;
  } catch (error) {
    console.error('Erro ao buscar notificações:', error);
    notifications.value = [];
  } finally {
    notificationsLoading.value = false;
  }
};

// Função para marcar notificação como lida
const markAsRead = async (id) => {
  try {
    const response = await axios.post(`/api/notificacoes/${id}/ler`);

    if (response.status === 200) {
      // Atualiza o estado das notificações localmente
      const index = notifications.value.findIndex(n => n.id === id);
      if (index !== -1) {
        notifications.value[index].lida = 1;
        notifications.value[index].lida_em = new Date().toISOString();
      }
    }
  } catch (error) {
    console.error('Erro ao marcar notificação como lida:', error);
  }
};

// Função para marcar todas notificações como lidas
const markAllAsRead = async () => {
  try {
    const response = await axios.post('/api/notificacoes/ler-todas');

    if (response.status === 200) {
      // Atualiza todas as notificações como lidas
      notifications.value.forEach(notification => {
        notification.lida = 1;
        notification.lida_em = new Date().toISOString();
      });
    }
  } catch (error) {
    console.error('Erro ao marcar todas notificações como lidas:', error);
  }
};
// Função para alternar a visibilidade da bandeja de notificações
const toggleNotifications = (event) => {
  // Impede que o clique se propague para o documento
  event.stopPropagation();

  showNotifications.value = !showNotifications.value;
  if (showNotifications.value) {
    fetchNotifications();
  }
};

// Função para fechar as notificações ao clicar fora
const handleClickOutside = (event) => {
  const container = document.querySelector('.notification-container');
  if (container && !container.contains(event.target)) {
    showNotifications.value = false;
  }
};

// Intervalo para verificar novas notificações periodicamente
let checkNotificationsInterval;

onMounted(() => {
  fetchUserProfile();
  fetchNotifications(); // Busca notificações inicialmente

  // Adiciona listener para fechar o dropdown ao clicar fora
  document.addEventListener('click', handleClickOutside);

  // Configura verificação periódica de notificações (a cada 5 minutos)
  checkNotificationsInterval = setInterval(() => {
    if (!showNotifications.value) { // Só busca se o painel estiver fechado
      fetchNotifications();
    }
  }, 300000); // 5 minutos
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
  clearInterval(checkNotificationsInterval);
});
</script>

<style scoped>
a {
  text-decoration: none;
  cursor: pointer;
}

.navbar {
  width: 100%;
  height: 70px;
  user-select: none;
  position: relative;
  background: white;
  z-index: 9000;
}

.navbar-content {
  display: flex;
  justify-content: space-between;
  padding: 0 23px;
  position: absolute;
  width: 100%;
  top: 50%;
  transform: translateY(-50%);
}

.navbar-left {
  display: flex;
  align-items: center;
}

.navbar-logo {
  display: flex;
  position: relative;
}

.navbar-right {
  display: flex;
  align-items: center;
}

/* Estilos do usuário */
.user-info {
  display: flex;
  align-items: center;
  margin-left: 20px;
  cursor: pointer;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  margin-right: 10px;
}

.user-details {
  line-height: 16px;
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: black;
}

.user-location {
  font-size: 13px;
  font-weight: 500;
  color: #528429;
}

/* Notificações */
.notification-container {
  position: relative;
}

.notification-icon {
  cursor: pointer;
  position: relative;
  padding: 5px;
  color: #555;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-icon:hover {
  color: #333;
}

.notification-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 10px;
  height: 10px;
  background-color: #e63946;
  border-radius: 50%;
}

.notification-tray {
  position: fixed;
  top: 50px;
  right: calc(15% - 350px + 175px);
  width: 350px;
  max-height: 400px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 9999;
  overflow: hidden;
  border: 1px solid #eaeaea;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #eaeaea;
}

.notification-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.mark-all-read {
  font-size: 12px;
  color: #528429;
  cursor: pointer;
  font-weight: 500;
}

.mark-all-read:hover {
  text-decoration: underline;
}

.notification-list {
  max-height: 350px;
  overflow-y: auto;
}

.no-notifications {
  padding: 20px 16px;
  text-align: center;
  color: #888;
  font-size: 14px;
}

.notification-item {
  padding: 12px 16px;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background-color 0.2s;
}

.notification-item:hover {
  background-color: #f9f9f9;
}

.notification-item.unread {
  background-color: #f0f7eb;
}

.notification-item.unread:hover {
  background-color: #e8f4e0;
}

.notification-content {
  display: flex;
  flex-direction: column;
}

.notification-title {
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 4px;
}

.notification-message {
  font-size: 13px;
  color: #555;
  margin-bottom: 8px;
}

.notification-time {
  font-size: 12px;
  color: #888;
}

/* Esquelestos de carregamento */
.notification-loading {
  padding: 12px 16px;
}

.notification-skeleton {
  height: 80px;
  margin-bottom: 12px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 3px;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

/* Estilos de loading do perfil */
.user-info.loading {
  pointer-events: none;
}

.avatar-skeleton {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #e0e0e0;
  margin-right: 10px;
  animation: pulse 1.5s infinite;
}

.user-details-skeleton {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.name-skeleton {
  width: 100px;
  height: 14px;
  background: #e0e0e0;
  animation: pulse 1.5s infinite;
}

.location-skeleton {
  width: 80px;
  height: 12px;
  background: #e0e0e0;
  animation: pulse 1.5s infinite;
}

.mr-4 {
  margin-right: 1rem;
}

.w-full {
  width: 100%;
}

.h-full {
  height: 100%;
}

@keyframes pulse {
  0% {
    opacity: 1;
  }

  50% {
    opacity: 0.6;
  }

  100% {
    opacity: 1;
  }
}
</style>