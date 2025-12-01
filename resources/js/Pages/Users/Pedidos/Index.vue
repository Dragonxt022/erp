<template>
  <LayoutFranqueado>
    <!-- Cabeçalho da página -->
    <Head title="Novo Pedido" />

    <!-- Container do carrinho de pedidos -->
    <div v-if="showCadastroProduto" class="mt-6">
      <ResumoCarrinhoPedidos
        :carrinho="carrinho"
        :isVisible="showCadastroProduto"
        @cancelar="toggleCadastro"
      />
    </div>

    <!-- Container principal -->
    <div
      class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2 card-container h-full overflow-hidden"
    >
      <!-- Coluna 1: Listar Unidades -->
      <div v-if="!showCadastroProduto">
        <ListarInsumosPedidos
          :key="listaKey"
          ref="listarUnidades"
          @unidade-cadastrada="fetchUnidades"
          @produto-selecionado="ProdutoSelecionado"
        />
      </div>

      <!-- Coluna 2: Alternar entre Detalhes e Cadastro -->
      <div class="flex flex-col gap-4">
        <template v-if="!showCadastroProduto">
          <template v-if="produtoSelecionado">
            <DetalhesProdutoPedidos
              @atualizar="atualizarLista"
              :produto="produtoSelecionado"
              @finalizar="toggleCadastro"
              @adicionarAoCarrinho="handleAdicionarAoCarrinho"
            />
          </template>
        </template>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import DetalhesProdutoPedidos from '@/Components/EstruturaFranqueado/DetalhesProdutoPedidos.vue';
import ListarInsumosPedidos from '@/Components/EstruturaFranqueado/ListarInsumosPedidos.vue';
import ResumoCarrinhoPedidos from '@/Components/EstruturaFranqueado/ResumoCarrinhoPedidos.vue';

// Dados do carrinho e visibilidade do cadastro
const carrinho = ref([]);
const produtoSelecionado = ref(null);
const listaKey = ref(0);
const showCadastroProduto = ref(false);
const isVisible = ref(false);

// Alterna a visibilidade entre Cadastro e Detalhes
const toggleCadastro = () => {
  showCadastroProduto.value = !showCadastroProduto.value;
};

// Função para adicionar produto ao carrinho
const handleAdicionarAoCarrinho = (item) => {
  carrinho.value.push(item);
  // produtoSelecionado.value = null;
};

// Atualzia o componet de inusmos apos atualizar as quantidades
const atualizarLista = () => {
  console.log('atualizando a lista');
  produtoSelecionado.value = null;
  // Atualiza a chave, forçando a reinicialização do componente
  listaKey.value += 1;
};

// Atualiza a lista de inumos
const fetchUnidades = () => {
  const listarUnidades = ref('listarUnidades');
  listarUnidades.value?.fetchUnidades();
};

// Define os dados da Inusmos  selecionada
const ProdutoSelecionado = (produto) => {
  produtoSelecionado.value = produto;
  console.info('Dados recebidos produto-selecionado:', produto);
};
</script>

<style lang="css" scoped>
.botao-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  display: flex;
  gap: 10px;
}
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  line-height: 80%;
}

.painel-subtitle {
  font-size: 17px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}

/* Wrapper com altura total */
.card-container-wrapper {
  height: 100vh; /* Ocupa 100% da altura da viewport */
  position: relative; /* Necessário para scroll oculto */
  overflow: hidden; /* Barra de rolagem oculta */
}

/* Container interno com rolagem */
.card-container {
  height: 100%; /* Garante altura completa dentro do wrapper */
  overflow-y: scroll; /* Habilita rolagem vertical */
  scrollbar-width: none; /* Oculta barra no Firefox */
  -ms-overflow-style: none; /* Oculta barra no IE e Edge */
}

/* Ocultar barra de rolagem no Chrome, Safari e Edge moderno */
.card-container::-webkit-scrollbar {
  display: none;
}
</style>
