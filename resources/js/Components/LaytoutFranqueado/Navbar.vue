<template>
  <div class="navbar">
    <div class="navbar-background"></div>

    <div class="navbar-content">
      <!-- Logo e ícones de navegação -->
      <div class="navbar-left">
        <div class="navbar-logo">
          <div class="mr-4">
            <a href="https://www.taiksu.com.br/office/" target="_blank">
              <img
                src="/storage/images/quadrados_verdes.svg"
                alt="Quadrados verdes"
                class="w-full h-full"
              />
            </a>
          </div>
          <div>
            <a :href="route('franqueado.painel')">
              <img
                src="/storage/images/logo_tipo_verde.svg"
                alt="logo tipo verde"
                class="w-full h-full"
              />
            </a>
          </div>
        </div>
      </div>

      <!-- Avatar e informações do usuário -->
      <div class="navbar-right">
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
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

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
    user.value = data.data; // Ajuste conforme a estrutura da sua API
  } catch (error) {
    console.error('Erro ao buscar perfil:', error);
    user.value = null; // Define como null em caso de erro
  }
};

// Carrega os dados ao montar o componente
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
  position: relative;
  background: white;
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

.user-info {
  display: flex;
  align-items: center;
  margin-right: 20px;
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

.mr-4 {
  margin-right: 1rem;
}

.w-full {
  width: 100%;
}

.h-full {
  height: 100%;
}

/* Estilos de loading */
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
