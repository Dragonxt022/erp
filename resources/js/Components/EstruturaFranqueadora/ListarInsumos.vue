<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Insumos</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Adicione os itens que você recebeu ao estoque</p>
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
        placeholder="Buscar um produto"
        class="search-input pl-10 w-full py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
      />
    </div>

    <!-- Container de cards -->
    <div class="card-container">
      <div
        v-for="produto in filteredProdutos"
        :key="produto.id"
        class="card cursor-pointer transform transition-transform duration-200 hover:shadow-lg"
        @click="selecionarProduto(produto)"
      >
        <div class="card-inner">
          <div class="icon-container">
            <div class="icon-bg"></div>
            <div class="icon-leaf">
              <img
                :src="getProfilePhotoUrl(produto.profile_photo)"
                alt="Imagem do produto"
                class="w-12 h-12 rounded-lg"
              />
            </div>
          </div>
          <div class="text-container">
            <!-- Nome do fornecedor -->
            <div class="city">
              {{ produto.nome }}
              <span v-if="produto.estrela" class="estrela">★</span>
            </div>
          </div>
          <div class="action-icon"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Variáveis reativas
const produtos = ref([]); // Armazena os produtos
const searchQuery = ref(''); // Campo de pesquisa

// Método para buscar os produtos da API
const fetchProdutos = async () => {
  try {
    const response = await axios.get('/api/produtos/lista-insumos');
    console.log('Produtos carregados:', response.data);

    // Garante que os produtos sejam sempre um array
    produtos.value = Array.isArray(response.data) ? response.data : [];
  } catch (error) {
    console.error('Erro ao carregar os produtos:', error);
    produtos.value = []; // Garante um array vazio em caso de erro
  }
};

// Método para gerar a URL correta da imagem
const getProfilePhotoUrl = (profilePhoto) => {
  if (!profilePhoto) {
    return '/storage/images/no_imagem.svg'; // Caminho para imagem padrão
  }
  return new URL(profilePhoto, window.location.origin).href;
};

// Computed para filtrar os produtos com base na pesquisa
const filteredProdutos = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return produtos.value.filter((produto) =>
    produto.nome.toLowerCase().includes(query)
  );
});

// Método para selecionar o produto
const selecionarProduto = (produto) => {
  // Emitir o evento para o componente pai
  console.log('Produto selecionado:', produto);
};

// Monta o componente e busca os produtos
onMounted(fetchProdutos);
</script>

<style scoped>
.estrela {
  color: gold;
  font-size: 20px;
  margin-left: 15px;
}
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
