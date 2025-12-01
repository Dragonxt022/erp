<template>
  <div class="navbar">
    <div class="navbar-background">
      <!-- Navbar container background -->
    </div>

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

      <!-- Avatar, informações do usuário e botão de logout -->
      <div class="navbar-right">
        <div class="user-info">
          <img :src="profilePhoto" alt="Avatar" class="avatar" />
          <div class="user-details">
            <div class="user-name">{{ user?.name }}</div>
            <div class="user-location">
              {{ unidade?.cidade || 'Taiksu Franchising' }}
            </div>
          </div>
        </div>
        <button @click="logout" class="logout-button">
          <img
            src="/storage/images/botao_sair.svg"
            alt="Logout"
            class="logout-icon"
          />
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="js">
import { ref, onMounted, computed } from 'vue';
import { useUserStore } from '@/stores/user';
import { router } from '@inertiajs/vue3';

export default {
  setup() {
    const userStore = useUserStore();


    // Aguardar o carregamento do perfil
    onMounted(async () => {
      if (!userStore.user) {
        await userStore.fetchUserProfile();
      }
    });
    // Função de Logout usando Inertia.js
    async function logout() {
      router.post('/logout', {}, {
        onFinish: () => {
          userStore.user = null; // Limpa os dados do usuário no store
          router.visit('/login-estoque'); // Redireciona para login
        }
      });
    }

    // Variáveis reativas para o template
    const user = computed(() => userStore.user);
    const unidade = computed(() => userStore.user?.unidade); // Acessando unidade diretamente do userStore
    const profilePhoto = computed(() => {
      // Verifica se existe foto do perfil, senão gera uma imagem com as iniciais
      return user.value?.profile_photo_url ||
             `https://ui-avatars.com/api/?name=${getInitials(user.value?.name)}&color=7F9CF5&background=EBF4FF`;
    });

    // Função para pegar as iniciais do nome
    function getInitials(name) {
      if (!name) return '*'; // Se não houver nome, retorna NN
      const nameParts = name.split(' ');
      const initials = nameParts.map(part => part.charAt(0).toUpperCase()).join('');
      return initials;
    }

    return { user, profilePhoto, unidade, logout };


  },
};
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

.small-rectangle {
  width: 22px;
  height: 22px;
  background: #5fc338;
  margin: 0 5px;
}

.circle-icon {
  width: 22px;
  height: 22px;
  background: #5fc338;
  border-radius: 50%;
  margin-right: 5px;
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

.notification {
  width: 46px;
  height: 46px;
  background: rgba(247.62, 255, 240.98, 0);
  border-radius: 50%;
  cursor: pointer;
  position: relative;
}

.notification .circle {
  width: 24px;
  height: 24px;
  position: absolute;
  left: 11px;
  top: 11px;
  border-radius: 50%;
}

.notification .inner-circle {
  width: 7px;
  height: 7px;
  /* background: #ff2d55; */
  border-radius: 50%;
  position: absolute;
  left: 13px;
  top: 13px;
}

/* Estilo do botão de logout */
.logout-button {
  display: flex;
  justify-content: center;
  align-items: center;
  border: none;
  cursor: pointer;
}

.logout-icon {
  width: 85px; /* Ajuste conforme necessário */
  height: 85px;
}
</style>
