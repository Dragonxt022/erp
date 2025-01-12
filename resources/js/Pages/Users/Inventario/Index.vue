<template>
  <LayoutFranqueado>
    <!-- Cabeçalho da página -->
    <Head title="Insumos" />

    <!-- Container principal da grade com 2 colunas -->
    <div
      class="grid grid-cols-1 gap-4 mt-3 sm:grid-cols-2 overflow-hidden card-container h-full overflow-y-scroll"
      @scroll.passive
    >
      <!-- Coluna 1: Listar Unidades -->
      <div v-if="!showCadastroProduto">
        <ListarInsumos
          ref="listarUnidades"
          @unidade-cadastrada="fetchUnidades"
          @produto-selecionado="ProdutoSelecionado"
        />
      </div>

      <!-- Coluna 2: Alternar entre Detalhes e Cadastro -->
      <div class="flex flex-col gap-4">
        <template v-if="!showCadastroProduto">
          <!-- Passa os dados do usuário selecionado apenas se existirem -->
          <template v-if="produtoSelecionado">
            <DetalhesProduto :produto="produtoSelecionado" />
          </template>

          <div class="absolute bottom-4 right-4">
            <ButtonPrimaryMedio
              text="Nova entrada"
              iconPath="/storage/images/arrow_left_alt.svg"
              @click="toggleCadastro"
            />
          </div>
        </template>
        <template v-else>
          <div class="mt-4">
            <CadastroProduto
              :isVisible="showCadastroProduto"
              @unidade-cadastrada="handleCadastro"
              @cancelar="toggleCadastro"
            />
          </div>
        </template>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import CadastroProduto from '@/Components/EstruturaFranqueado/CadastroProduto.vue';
import DetalhesProduto from '@/Components/EstruturaFranqueado/DetalhesProduto.vue';
import ListarInsumos from '@/Components/EstruturaFranqueado/ListarInsumos.vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';

// Dados do usuário selecionado
const produtoSelecionado = ref(null);

const showCadastroProduto = ref(false);

// Alterna a visibilidade entre Cadastro e Detalhes
const toggleCadastro = () => {
  showCadastroProduto.value = !showCadastroProduto.value;
};

// Atualiza a lista de unidades após o cadastro
const handleCadastro = () => {
  fetchUnidades();
  showCadastroProduto.value = false;
};

// Atualiza a lista de unidades
const fetchUnidades = () => {
  const listarUnidades = ref('listarUnidades');
  listarUnidades.value?.fetchUnidades();
};

// Define os dados da unidade selecionada
const ProdutoSelecionado = (produto) => {
  if (produto && typeof produto === 'object') {
    produtoSelecionado.value = produto;
  } else {
    console.warn(
      'Dados inválidos recebidos no evento produto-selecionado:',
      produto
    );
  }
};
</script>

<style lang="css" scoped>
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
