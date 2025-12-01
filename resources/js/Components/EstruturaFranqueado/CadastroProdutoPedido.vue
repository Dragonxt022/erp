<template>
  <transition name="fade">
    <div v-if="!mostrarResumo">
      <div v-if="isVisible" class="sidebar-container">
        <!-- Animação de Carregamento -->
        <div v-if="isLoading" class="loading-overlay">
          <div class="spinner"></div>
        </div>

        <!-- Conteúdo principal -->
        <div v-else class="flex">
          <!-- Coluna da esquerda (Lista de produtos) -->
          <div class="flex-1 p-4">
            <!-- Título principal -->
            <div class="painel-title">Nova entrada</div>

            <!-- Subtítulo da página -->
            <div class="painel-subtitle">
              <p>Adicione os itens que você recebeu ao estoque</p>
            </div>

            <!-- Campo de pesquisa -->
            <div class="relative flex items-center w-full mb-4">
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
                placeholder="Buscar um produto"
                class="pl-10 w-full py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>

            <!-- Container de cards -->
            <div class="space-y-4 compromissos-container overflow-hidden">
              <div
                v-for="produto in filteredProdutos"
                :key="produto.id"
                class="flex items-center p-4 bg-white rounded-lg cursor-pointer"
                @click="selecionarProduto(produto)"
              >
                <div class="flex-shrink-0">
                  <img
                    :src="getProfilePhotoUrl(produto.profile_photo)"
                    alt="Imagem do produto"
                    class="w-12 h-12 rounded-lg"
                  />
                </div>
                <div class="ml-4 flex-1">
                  <div class="text-lg font-semibold flex items-center">
                    {{ produto.nome }}

                    <span
                      v-if="quantidadeNoCarrinho(produto.id) > 0"
                      class="ml-5 text-xs bg-green-100 text-green-600 font-bold rounded-full px-2 py-1"
                    >
                      {{ quantidadeNoCarrinho(produto.id) }}
                    </span>

                    <span
                      v-if="produto.estrela"
                      class="mr-2 ml-5 text-yellow-500"
                      title="Essa estrela sigifinica que esse insumo e um produto princial"
                    >
                      ★
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Botão para finalizar a entrada e exibir a modal de revisão -->
          <div class="absolute bottom-4 right-4 mt-5 botao-container">
            <ButtonPrimaryMedio
              text="Finalizar"
              @click="mostrarResumo = true"
              iconPath="/storage/images/arrow_left_alt.svg"
            />
          </div>
          <!-- Coluna da direita (Formulário) -->
          <div class="flex-1 p-4">
            <CadastrarInsumoEstoque
              :produtoSelecionado="produtoSelecionado"
              @adicionarAoCarrinho="adicionarAoCarrinho"
            />
          </div>
        </div>
      </div>
    </div>
  </transition>
  <!-- Exibir o componente de resumo quando 'mostrarResumo' for true -->
  <ResumoCarrinho
    v-if="mostrarResumo"
    :carrinho="carrinho"
    @confirmar="confirmarEntrada"
    @cancelar="mostrarResumo = false"
  />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import CadastrarInsumoEstoque from './CadastrarInsumoEstoque.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import ResumoCarrinho from './ResumoCarrinho.vue';

// Props
defineProps({
  isVisible: {
    type: Boolean,
    required: true,
  },
});

// Reativos
const produtos = ref([]);
const searchQuery = ref('');
const isLoading = ref(false);
const produtoSelecionado = ref(null);
const carrinho = ref([]);

const mostrarResumo = ref(false);

// Métodos
const confirmarEntrada = () => {
  // Lógica para confirmar a entrada no banco de dados
  console.log('Entrada confirmada', carrinho.value);
  mostrarModalRevisao.value = false;
  carrinho.value = []; // Limpar carrinho após confirmação
};

const fetchProdutos = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/produtos/lista');
    produtos.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar os produtos:', error);
  } finally {
    isLoading.value = false;
  }
};

const getProfilePhotoUrl = (profilePhoto) => {
  return profilePhoto
    ? new URL(profilePhoto, window.location.origin).href
    : '/storage/images/no_imagem.svg';
};

const selecionarProduto = (produto) => {
  produtoSelecionado.value = produto;
};

const quantidadeNoCarrinho = (produtoId) => {
  return carrinho.value.filter((item) => item.id === produtoId).length;
};

const adicionarAoCarrinho = (produto) => {
  carrinho.value.push(produto);
  produtoSelecionado.value = null; // Fecha o formulário após adicionar
};

// Computados
const filteredProdutos = computed(() =>
  produtos.value.filter((produto) =>
    produto.nome.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
);

// Lifecycle
onMounted(fetchProdutos);
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
.botao-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  display: flex;
  gap: 10px;
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
