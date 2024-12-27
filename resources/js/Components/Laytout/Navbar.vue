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
                alt="logo tipo verde"
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
            <div class="user-name">{{ user?.name }}</div>
            <div class="user-location">
              {{ unidade?.cidade || 'Taiksu Franchising' }}
            </div>
            <!-- Exibindo a cidade da unidade -->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="js">
import { ref, onMounted, computed } from 'vue';
import { useUserStore } from '@/stores/user'; // Importando o store

export default {
  setup() {
    const userStore = useUserStore();

    // Função para atualizar a unidade e a cidade
    const updateUnitData = async () => {
      // Faça uma chamada para atualizar os dados da unidade do servidor
      const updatedUnit = await userStore.fetchUnitData(); // Implemente essa função no seu store ou serviço

      // Verifique se a cidade mudou
      if (updatedUnit?.cidade !== userStore.user?.unidade?.cidade) {
        // Se a cidade foi alterada, atualize os dados no store
        userStore.user.unidade.cidade = updatedUnit.cidade;
      }
    };

    // Aguardar o carregamento do perfil
    onMounted(async () => {
      if (!userStore.user) {
        await userStore.fetchUserProfile();
      }

      // Chame a função para atualizar a unidade e cidade após carregar o perfil
      await updateUnitData();
    });

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
      if (!name) return 'NN'; // Se não houver nome, retorna NN
      const nameParts = name.split(' ');
      const initials = nameParts.map(part => part.charAt(0).toUpperCase()).join('');
      return initials;
    }

    return { user, profilePhoto, unidade };
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
</style>
