<template>
  <div class="navbar">
    <div class="navbar-background"></div>

    <div class="navbar-content">
      <!-- Logo e ícones de navegação -->
      <div class="navbar-left">
          <!-- Botão hamburguer — visível apenas no mobile -->
          <button
            @click="toggleSidebar"
            class="md:hidden mr-3 p-2 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none"
            aria-label="Abrir menu"
          >
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <a href="https://login.taiksu.com.br/" class="flex flex-row gap-1.5 px-2 py-1 sm:px-3 rounded-full bg-green-100 shadow-md hover:shadow-xl transition-all duration-300">
            <img src="https://login.taiksu.com.br/frontend/img/seta.png" alt="Menu" class="w-4 h-4 sm:w-5 sm:h-5 rotate-180 shrink-0 self-center" />
            <span class="text-xs sm:text-sm font-semibold text-green-700 leading-tight">Voltar para o <span class="text-green-500 font-bold">Office</span></span>
          </a>
      </div>
      <a v-if="canAccessDre" href="https://admin.taiksu.com.br/franqueado/dre-gerencial" class="flex flex-row gap-1.5 px-2 py-1 sm:px-3 rounded-full bg-green-100 shadow-md hover:shadow-xl transition-all duration-300">
        <img src="https://login.taiksu.com.br/frontend/img/seta.png" alt="Menu" class="w-4 h-4 sm:w-5 sm:h-5 rotate-180 shrink-0 self-center" />
        <span class="text-xs sm:text-sm font-semibold text-green-700 leading-tight">DRE Gerencial</span>
      </a>
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
const canAccessDre = computed(() => {
  return Boolean(user.value?.franqueado) && !Boolean(user.value?.colaborador);
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
