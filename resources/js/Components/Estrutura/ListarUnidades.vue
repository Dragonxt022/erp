<template>
  <div>
    <!-- Campo de pesquisa -->
    <div class="search-container">
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Pesquisar unidades"
        class="search-input"
      />
    </div>

    <!-- Container de cards -->
    <div class="card-container">
      <div v-for="item in filteredUnidades" :key="item.unidade.id" class="card">
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
import axios from 'axios';

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
.search-container {
  margin-bottom: 16px;
  display: flex;
  justify-content: center;
}

.search-input {
  padding: 8px 12px;
  border-radius: 4px;
  border: 1px solid #ccc;
  width: 100%;
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
