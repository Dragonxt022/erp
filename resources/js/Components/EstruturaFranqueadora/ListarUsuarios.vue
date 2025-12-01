<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Franqueados</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Visualize os gestores das unidades</p>
    </div>

    <!-- Campo de pesquisa -->
    <div class="search-container relative flex items-center w-full mb-4">
      <!-- Ícone de pesquisa -->
      <div class="absolute left-3">
        <img
          src="/storage/images/search.svg"
          alt="Ícone de pesquisa"
          class="w-5 h-5 text-gray-500"
        />
      </div>
      <!-- Campo de pesquisa -->
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Buscar um fornecedor"
        class="search-input pl-10 w-full py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
      />
    </div>

    <!-- Container de cards -->
    <div class="card-container">
      <div
        v-for="usuario in filteredUsuarios"
        :key="usuario.id"
        class="card cursor-pointer transform transition-transform duration-200 hover:shadow-lg"
        @click="selecionarUsuario(usuario)"
      >
        <div class="card-inner">
          <div class="icon-container">
            <div class="icon-bg"></div>
            <div class="icon-leaf">
              <img
                :src="usuario.profilePhoto"
                alt="Foto de perfil"
                class="w-12 h-12 rounded-lg"
              />
            </div>
          </div>
          <div class="text-container">
            <!-- Nome do usuário -->
            <div class="city">{{ usuario.name }}</div>

            <!-- CPF do usuário -->
            <div class="owner">{{ usuario.cpf }}</div>
          </div>
          <div class="action-icon"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

export default {
  data() {
    return {
      usuarios: [], // Armazena os usuários
      searchQuery: '', // Campo de pesquisa
    };
  },
  mounted() {
    this.fetchUsuarios(); // Busca os dados da API ao montar o componente
  },
  methods: {
    // Busca os usuários da API
    async fetchUsuarios() {
      try {
        const response = await axios.get('/api/usuarios');

        // Filtra os usuários que NÃO são colaboradores
        this.usuarios = response.data.filter((user) => !user.colaborador);
      } catch (error) {
        console.error('Erro ao carregar usuários:', error);
      }
    },

    // Seleciona um usuário e emite o evento para o pai
    selecionarUsuario(usuario) {
      this.$emit('usuario-selecionado', usuario);
    },

    // Gera as iniciais do nome
    getInitials(name) {
      if (!name) return 'N/A';
      const initials = name
        .split(' ')
        .map((n) => n[0])
        .join('');
      return initials.slice(0, 2).toUpperCase();
    },
  },
  computed: {
    // Computed para cada usuário para verificar e definir a foto de perfil
    usuariosComFotos() {
      return this.usuarios.map((usuario) => {
        return {
          ...usuario,
          profilePhoto:
            usuario.profile_photo_url ||
            `https://ui-avatars.com/api/?name=${this.getInitials(
              usuario.name
            )}&color=7F9CF5&background=EBF4FF&size=55`,
        };
      });
    },

    // Filtra usuários com base em nome ou CPF
    filteredUsuarios() {
      return this.usuariosComFotos.filter((usuario) => {
        const query = this.searchQuery.toLowerCase();
        return (
          usuario.name.toLowerCase().includes(query) ||
          usuario.cpf.toLowerCase().includes(query)
        );
      });
    },
  },
};
</script>

<style scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}

.button-container {
  margin-top: 15px;
  text-align: right;
}

.card-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.card {
  width: 100%;
  height: 63px;
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0px 0px 1px rgba(142.11, 142.11, 142.11, 0.08);
}

.card-inner {
  display: flex;
  align-items: center;
  padding: 8px;
}

.icon-container {
  position: relative;
  width: 55px;
  height: 55px;
}

.icon-bg {
  width: 55px;
  height: 55px;
  position: absolute;
  left: 0;
  top: 1.33px;
}

.text-container {
  margin-left: 14px;
  flex-grow: 1;
}

.city {
  font-size: 17px;
  font-family: Figtree;
  font-weight: 600;
  line-height: 22px;
  color: #262a27;
}

.owner {
  font-size: 13px;
  font-family: Figtree;
  font-weight: 500;
  line-height: 18px;
  color: #6d6d6e;
}

.action-icon {
  width: 24px;
  height: 24px;
}
</style>
