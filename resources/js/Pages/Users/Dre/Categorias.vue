<template>
  <AppLayout>
    <!-- Cabeçalho da página -->
    <Head title="Nova Categoria" />

    <!-- Container principal da grade com 2 colunas -->
    <div
      class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2 overflow-hidden card-container h-full overflow-y-scroll"
      @scroll.passive
    >
      <!-- Coluna 1: Listar Unidades -->
      <div>
        <ListaDeCategoriaCusto
          :key="listaUnidadesKey"
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
            <DetalhesCategoriaDeCusto :produto="produtoSelecionado" />
          </template>

          <div class="absolute bottom-4 right-4">
            <ButtonPrimaryMedio
              text="Nova Categoria"
              iconPath="/storage/images/arrow_left_alt.svg"
              @click="toggleCadastro"
            />
          </div>
        </template>
        <template v-else>
          <div class="mt-4">
            <CadastroCategoriaCusto
              :isVisible="showCadastroProduto"
              @unidade-cadastrada="handleCadastro"
              @cancelar="toggleCadastro"
              @atualizar="atualizaComponet"
            />
          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import ListaDeCategoriaCusto from '@/Components/EstruturaFranqueado/ListaDeCategoriaCusto.vue';
import DetalhesCategoriaDeCusto from '@/Components/EstruturaFranqueado/DetalhesCategoriaDeCusto.vue';
import CadastroCategoriaCusto from '@/Components/EstruturaFranqueado/CadastroCategoriaCusto.vue';

// Dados do usuário selecionado
const produtoSelecionado = ref(null);

const showCadastroProduto = ref(false);
const listaUnidadesKey = ref(0);

// Reinicia o componet
const atualizaComponet = () => {
  // Atualiza a chave para reiniciar o componente
  listaUnidadesKey.value += 1;
};

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

<style lang="css">
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
