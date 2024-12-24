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
            <a :href="route('painel')">
              <img
                src="/storage/images/logo_tipo_verde.svg"
                alt="loto tipo verde"
                class="w-full h-full"
              />
            </a>
          </div>
        </div>
      </div>

      <!-- Avatar e informações do usuário -->
      <div class="navbar-right">
        <div class="notification">
          <div class="circle">
            <img
              src="/storage/images/icon_notification.svg"
              alt="icone notificação"
            />
          </div>
          <div class="inner-circle"></div>
        </div>
        <div class="user-info">
          <img :src="profilePhoto" alt="Avatar" class="avatar" />
          <div class="user-details">
            <div class="user-name">{{ user.name }}</div>
            <div class="user-location">
              {{ user.user_details?.cidade }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Navbar',
  data() {
    return {
      user: {},
    };
  },
  computed: {
    // Computed property para determinar a URL da foto de perfil
    profilePhoto() {
      // Verifica se há uma imagem de perfil, senão usa a imagem padrão
      return this.user.profile_photo_path
        ? `/storage/images/${this.user.profile_photo_path}`
        : '/storage/images/user.png'; // Caminho da imagem padrão
    },
  },
  mounted() {
    this.fetchUserProfile();
  },
  methods: {
    async fetchUserProfile() {
      try {
        const response = await axios.get('/api/profile');
        if (response.data.status === 'success') {
          this.user = response.data.data; // Atualiza o perfil do usuário com os dados retornados
        } else {
          console.error(
            'Erro ao carregar os dados do usuário:',
            response.data.message
          );
        }
      } catch (error) {
        console.error('Erro ao buscar perfil:', error);
      }
    },
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
  padding: 0 40px;
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
  line-height: 1.2px;
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
</style>
