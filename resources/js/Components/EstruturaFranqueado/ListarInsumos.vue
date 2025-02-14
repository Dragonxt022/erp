<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Inventário da loja</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Verifique a quantidade de cada item</p>
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
    <div class="card-container compromissos-container overflow-hidden">
      <div
        v-for="(categoria, categoriaNome) in filteredProdutos"
        :key="categoriaNome"
        class="categoria-group"
      >
        <!-- Nome da categoria -->
        <div
          class="w-full h-[18.63px] text-[#6d6d6d] text-[15px] font-semibold font-['Figtree'] leading-tight mt-8 mb-1"
        >
          {{ categoriaNome }}
        </div>

        <!-- Produtos dentro de cada categoria -->
        <div class="card-container">
          <div
            v-for="produto in categoria.produtos"
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
              <div class="text-container flex justify-between items-center">
                <!-- Nome do produto e estrela -->
                <div class="city">
                  {{ produto.nome }}
                </div>
                <!-- Quantidade -->
                <div class="quantidade text-gray-600 font-semibold">
                  <div class="text-gray-600 font-semibold">
                    {{ getQuantidadeTotal(produto.lotes) }}
                    {{ produto.unidadeDeMedida === 'unitario' ? 'uni' : 'kg' }}
                  </div>
                </div>
              </div>
              <div class="action-icon"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, defineEmits } from 'vue';
import axios from 'axios';

const emit = defineEmits(['produto-selecionado']);

const produtos = ref([]); // Armazena os produtos
const searchQuery = ref(''); // Campo de pesquisa

// Função para buscar os produtos da API
const fetchProdutos = async () => {
  try {
    const response = await axios.get('/api/estoque/lista');
    console.log('Produtos carregados:', response.data);

    // Verificar se a resposta é um objeto
    if (typeof response.data === 'object' && response.data !== null) {
      produtos.value = groupByCategoria(response.data); // Agrupar os produtos por categoria
    } else {
      console.error(
        'Esperava um objeto de categorias de produtos, mas recebi:',
        response.data
      );
    }
  } catch (error) {
    console.error('Erro ao carregar os produtos:', error);
  }
};

// Método para agrupar os produtos por categoria
function groupByCategoria(produtosData) {
  const categorias = Object.keys(produtosData); // Obtém as categorias (Mercearia, Legumes, etc.)
  const produtosAgrupados = {};

  categorias.forEach((categoria) => {
    produtosAgrupados[categoria] = {
      categoria_nome: produtosData[categoria].categoria_nome,
      produtos: produtosData[categoria].produtos || [], // Garante que os produtos sejam um array
    };
  });

  return produtosAgrupados;
}

// Método para gerar a URL correta da imagem
function getProfilePhotoUrl(profilePhoto) {
  if (!profilePhoto) {
    return '/storage/images/no_imagem.svg'; // Caminho para imagem padrão
  }
  return new URL(profilePhoto, window.location.origin).href;
}

// Método para selecionar o produto
const selecionarProduto = (produto) => {
  // Emite o evento para o componente pai
  emit('produto-selecionado', produto);
};

// Método para arredondar as quantidades de acordo com a unidade de medida
const arredondarQuantidade = (valor, unidadeDeMedida) => {
  const casasDecimais = unidadeDeMedida === 'a_granel' ? 3 : 0; //
  return parseFloat(valor).toFixed(casasDecimais); // Arredonda de acordo com a unidade de medida
};

// Função para calcular a quantidade total de todos os lotes de um produto
const getQuantidadeTotal = (lotes) => {
  if (!Array.isArray(lotes)) {
    console.warn('getQuantidadeTotal recebeu um valor inválido:', lotes);
    return 0; // Retorna 0 caso lotes seja inválido
  }
  // Soma todas as quantidades e arredonda o total de acordo com a unidade de medida
  const total = lotes.reduce(
    (total, lote) => total + (lote.quantidade || 0),
    0
  );
  return arredondarQuantidade(total, lotes[0]?.unidadeDeMedida || 'unitario'); // Arredonda com base na unidade de medida
};

// Computed property para filtrar os produtos conforme a pesquisa
const filteredProdutos = computed(() => {
  const query = searchQuery.value.toLowerCase();
  const resultado = {};

  // Filtra as categorias e seus produtos
  Object.keys(produtos.value).forEach((categoriaNome) => {
    const produtosFiltrados = produtos.value[categoriaNome].produtos.filter(
      (produto) => produto.nome.toLowerCase().includes(query)
    );
    if (produtosFiltrados.length > 0) {
      resultado[categoriaNome] = {
        categoria_nome: produtos.value[categoriaNome].categoria_nome,
        produtos: produtosFiltrados,
      };
    }
  });

  return resultado;
});

// Chama a função de busca ao montar o componente
onMounted(() => {
  fetchProdutos();
});
</script>

<style scoped>
.compromissos-container {
  max-height: 450px; /* Defina a altura máxima desejada para a coluna */
  overflow-y: auto; /* Habilita rolagem vertical */
}

/* Esconde a barra de rolagem */
.compromissos-container::-webkit-scrollbar {
  display: none; /* Esconde a barra de rolagem no Chrome, Safari, e Edge */
}

.compromissos-container {
  -ms-overflow-style: none; /* Esconde a barra de rolagem no Internet Explorer */
  scrollbar-width: none; /* Esconde a barra de rolagem no Firefox */
}
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
