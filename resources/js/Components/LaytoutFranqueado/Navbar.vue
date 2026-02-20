<template>
  <div class="navbar">
    <div class="navbar-background"></div>

    <div class="navbar-content">
      <!-- Logo e ícones de navegação -->
      <div class="navbar-left">
          <a href="https://login.taiksu.com.br/" class="flex flex-row gap-2 px-3 py-1 rounded-full bg-green-100 shadow-md hover:shadow-xl transition-all duration-300">
            <img src="https://login.taiksu.com.br/frontend/img/seta.png" alt="Menu" class="w-5 h-5 rotate-180" />
            <h2 class="group text-md font-semibold text-green-700">Voltar para o <span class="text-green-500 font-bold">Office</span></h2>
          </a>
      </div>

      <!-- Avatar e informações do usuário -->
      <div class="navbar-right">
        <!-- Ícone de Notificações Externas -->
        <div class="mr-4">
          <iconeNotificacao />
        </div>

        <div class="view-mobile">
          <button @click="toggleSidebar">
            <img
              src="/storage/images/bx-menuv.svg"
              alt="Menu"
              class="w-full h-full"
            />
          </button>
        </div>

        <Link
          v-if="user"
          href="https://login.taiksu.com.br/perfil"
          class="user-info hide-mobile"
          :class="{ loading: !user }"
        >
          <a href="https://login.taiksu.com.br/perfil" class="user-info" v-if="user">
            <img :src="profilePhoto" alt="Avatar" class="avatar" />
            <div class="user-details">
              <div class="user-name">{{ user.name }}</div>
              <div class="user-location">
                {{ unidade?.cidade || 'Taiksu Franchising' }}
              </div>
            </div>
          </a>

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
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

// Importa o novo componente de notificações externas
import iconeNotificacao from '@/Components/EstruturaFranqueado/iconeNotificacao.vue';

import { useSidebarStore } from '@/stores/sidebar';
const sidebarStore = useSidebarStore();

const toggleSidebar = () => {
  sidebarStore.toggle();
};

const user = ref(null);
const unidade = computed(() => user.value?.unidade);

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

onMounted(() => {
  fetchUserProfile();
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
  position: fixed;
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

.hide-mobile {
  display: none;
}

.view-mobile {
  display: flex;
}

@media (min-width: 768px) {
  .view-mobile {
    display: none;
  }
}

@media (min-width: 768px) {
  .hide-mobile {
    display: block;
    /* ou flex, dependendo do seu layout */
  }
}
</style>
