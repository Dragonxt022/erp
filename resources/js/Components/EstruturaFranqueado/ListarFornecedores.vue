<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Fornecedores</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Fornecedores de insumos homologados</p>
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
        v-for="fornecedor in filteredFornecedores"
        :key="fornecedor.id"
        class="card cursor-pointer transform transition-transform duration-200 hover:shadow-lg"
        @click="selecionarFornecedor(fornecedor)"
      >
        <div class="card-inner">
          <div class="icon-container">
            <div class="icon-bg"></div>
            <div class="icon-leaf">
              <img
                src="/storage/images/servicos_verde.svg"
                alt="Ícone do fornecedor"
                class="w-5 h-5 rounded-lg"
              />
            </div>
          </div>
          <div class="text-container">
            <!-- Nome do fornecedor -->
            <div class="city">{{ fornecedor.razao_social }}</div>

            <!-- CNPJ do fornecedor -->
            <div class="owner">
              {{ fornecedor.cnpj }} / {{ fornecedor.estado }}
            </div>
          </div>
          <div class="action-icon"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

// Defina os eventos emitidos
const emit = defineEmits(['fornecedor-selecionado']);

// Dados reativos

const fornecedores = ref([]);
const searchQuery = ref('');

// Função para buscar fornecedores
const fetchFornecedores = async () => {
  try {
    const response = await axios.get('/api/estoque/fornecedores');
    fornecedores.value = response.data.data;
  } catch (error) {
    console.error('Erro ao carregar fornecedores:', error);
  }
};

// Função para selecionar fornecedor
const selecionarFornecedor = (fornecedor) => {
  emit('fornecedor-selecionado', fornecedor);
};

// Computed para filtrar fornecedores
const filteredFornecedores = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return fornecedores.value.filter(
    (fornecedor) =>
      fornecedor.razao_social.toLowerCase().includes(query) ||
      fornecedor.cnpj.toLowerCase().includes(query)
  );
});

// Chamar fetchFornecedores ao montar o componente
fetchFornecedores();
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
