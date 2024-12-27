<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Nossas Unidades</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Listagem de todas as unidades da franquia</p>
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
        placeholder="Pesquisar unidades"
        class="search-input pl-10 w-full py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
      />
    </div>

    <!-- Container de cards -->
    <div class="card-container">
      <div
        v-for="item in filteredUnidades"
        :key="item.unidade.id"
        class="card cursor-pointer transform transition-transform duration-200 hover:shadow-lg"
        @click="selecionarUnidade(item)"
      >
        <div class="card-inner">
          <div class="icon-container">
            <div class="icon-bg"></div>
            <div class="icon-leaf">
              <img
                src="/storage/images/storefront-verde.svg"
                alt="Ícone da Unidade"
              />
            </div>
          </div>
          <div class="text-container">
            <!-- Cidade da unidade -->
            <div class="city">{{ item.unidade.cidade }}</div>

            <!-- Usuários ou mensagem de unidade sem franqueado -->
            <div class="owner">
              <template v-if="item.usuarios.length > 0">
                {{ item.usuarios.map((user) => user.name).join(', ') }}
              </template>
              <template v-else>Unidade sem Franqueado</template>
            </div>
          </div>
          <div class="action-icon"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
const unidades = ref([]);

export default {
  data() {
    return {
      unidades: [], // Armazena as unidades com usuários
      searchQuery: '', // Consulta para pesquisa
    };
  },
  mounted() {
    this.fetchUnidades(); // Chama a API ao montar o componente
  },
  methods: {
    // Busca as unidades com seus usuários
    async fetchUnidades() {
      try {
        const response = await axios.get('/api/unidades');
        this.unidades = response.data;
      } catch (error) {
        console.error('Erro ao carregar unidades:', error);
      }
    },

    selecionarUnidade(item) {
      // Emite o evento para o componente pai, passando tanto a unidade quanto os usuários
      this.$emit('unidade-selecionada', {
        unidade: item.unidade,
        usuarios: item.usuarios,
      });
    },
  },
  computed: {
    // Filtra as unidades pelo nome da cidade
    filteredUnidades() {
      return this.unidades.filter((item) =>
        item.unidade.cidade
          .toLowerCase()
          .includes(this.searchQuery.toLowerCase())
      );
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
  margin-top: 20px;
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
  padding: 13px;
}

.icon-container {
  position: relative;
  width: 32px;
  height: 32px;
}

.icon-bg {
  width: 32px;
  height: 32px;
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
