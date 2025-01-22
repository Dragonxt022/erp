<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Métodos de pagamento</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Ative formas de pagamento para o fluxo de caixa</p>
    </div>

    <!-- Campo de pesquisa -->
    <!-- <div class="search-container relative flex items-center w-full mb-4">
      <div class="absolute left-3">
        <img
          src="/storage/images/search.svg"
          alt="Ícone de pesquisa"
          class="w-5 h-5 text-gray-500"
        />
      </div>
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Buscar de pedidos"
        class="search-input pl-10 w-full py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
      />
    </div> -->

    <!-- Lista de dados -->
    <div class="card-container">
      <div
        v-for="dados in filteredDados"
        :key="dados.id"
        class="card cursor-pointer transform transition-transform duration-200 hover:shadow-lg"
        @click="selecionarDados(dados)"
      >
        <div class="card-inner">
          <div class="icon-container">
            <div class="icon-bg"></div>
            <div class="icon-leaf">
              <img :src="getProfilePhotoUrl(dados.img_icon)" alt="Ícone" />
            </div>
          </div>
          <div class="text-container">
            <!-- Cidade da unidade -->
            <div class="city">{{ dados.nome }}</div>

            <!-- Usuários ou mensagem de unidade sem franqueado -->
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

const emit = defineEmits(['dado-selecionado']);

// Estado reativo
const dados = ref([]); // Armazena os pedidos
const searchQuery = ref(''); // Campo de pesquisa

// Função para buscar os pedidos da API
const fetchDados = async () => {
  try {
    const response = await axios.get('/api/metodos-pagamentos/lista');
    dados.value = Object.values(response.data); // Converte o objeto em array
  } catch (error) {
    console.error('Erro ao carregar os dados:', error);
  }
};

// Método para gerar a URL correta da imagem
const getProfilePhotoUrl = (img_icon) => {
  if (!img_icon) {
    return '/storage/images/no_imagem.svg'; // Caminho para imagem padrão
  }
  return new URL(img_icon, window.location.origin).href;
};

// Chamada da função de busca quando o componente é montado
onMounted(fetchDados);

// Método para selecionar o pedido
const selecionarDados = (dados) => {
  emit('dado-selecionado', dados);
};

// Computed para filtrar os pedidos com base na pesquisa
const filteredDados = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return dados.value.filter((pedido) => {
    return (
      String(pedido.id).toLowerCase().includes(query) || // Verifica se o ID inclui a pesquisa
      pedido.fornecedor_nome.toLowerCase().includes(query) // Verifica se o nome do fornecedor inclui a pesquisa
    );
  });
});
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
