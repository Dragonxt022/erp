<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Categorias de Custos</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Controle de categorias de curstos.</p>
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
        placeholder="Buscar um item"
        class="search-input pl-10 w-full py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
      />
    </div>

    <!-- Container de cards -->
    <div class="card-container">
      <div
        v-for="item in filteredDados"
        :key="item.id"
        class="card cursor-pointer transform transition-transform duration-200 hover:shadow-lg"
        @click="selecionarItem(item)"
      >
        <div class="card-inner">
          <div class="icon-container">
            <div class="icon-bg"></div>
            <div class="icon-leaf">
              <img
                src="/storage/images/categoria.svg"
                alt="Imagem do item"
                class="w-10 h-10 rounded-lg"
              />
            </div>
          </div>
          <div class="text-container">
            <!-- Nome do item -->
            <div class="city">
              {{ item.nome }}
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
  data() {
    return {
      dados: [], // Armazena os dados genéricos
      searchQuery: '', // Campo de pesquisa
    };
  },
  mounted() {
    this.fetchDados();
  },
  methods: {
    // Busca os dados da API
    async fetchDados() {
      try {
        const response = await axios.get(
          '/api/categorias/lista-categoria-custo'
        );
        console.log('Dados carregados:', response.data);
        this.dados = response.data; // Atualiza os dados
      } catch (error) {
        console.error('Erro ao carregar os dados:', error);
      }
    },
    // Método para gerar a URL correta da imagem
    getProfilePhotoUrl(profilePhoto) {
      if (!profilePhoto) {
        return '/storage/images/no_imagem.svg'; // Caminho para imagem padrão
      }
      return new URL(profilePhoto, window.location.origin).href;
    },

    selecionarItem(item) {
      this.$emit('item-selecionado', item);
    },
  },
  computed: {
    filteredDados() {
      const query = this.searchQuery.toLowerCase();
      return this.dados.filter((item) =>
        item.nome.toLowerCase().includes(query)
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
  font-size: 20px;
  font-family: Figtree;
  font-weight: 600;
  line-height: 0px;
  color: #262a27;
  margin-bottom: 10px;
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
