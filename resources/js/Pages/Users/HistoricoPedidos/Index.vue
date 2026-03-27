<template>
  <LayoutFranqueado>
      <div class="w-full px-32 py-16">
      <div class="mx-auto flex flex-col items-center justify-center w-full bg-white rounded-lg px-12 py-24 border border-gray-200 group">
          <img src="https://login.taiksu.com.br/applications/69c2e0d08f47c.png" alt="Logo" class="w-16 h-16 mb-4 group-hover:scale-110 transition-all ease-in-out duration-300">
          <h2 class="text-2xl font-semibold text-gray-700 text-center">Estamos migrando para a nova loja</h2>
          <p class="text-lg text-gray-500 text-center">Gerencie seus pedidos no novo app.</p>
          <div class="flex flex-col items-center justify-center gap-2 mt-2">
            <a href="https://login.taiksu.com.br/?redirect_uri=https%3A%2F%2Festoque.taiksu.com.br%2Fcallback%2Fcomprar" class="text-md text-white bg-green-500 px-16 shadow-xl hover:shadow-sm transition-shadow duration-400 py-2 rounded-full hover:bg-green-600 transition-all ease-in-out duration-300 mt-4 text-center">Fazer pedido</a>
            <a target="_blank" href="https://ajuda.taiksu.com.br/artigos/70c5db63-624f-495e-b4b3-8079b54de951" class="text-sm text-gray-500 hover:text-green-600 hover:underline mt-2">Aprender a usar</a>
          </div>
      </div>
    </div>
    <!-- Cabeçalho da página -->
    <Head title="Histórico de Pedidos" />
<div class="hidden">
    <!-- Container principal -->
    <div
      class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2 card-container h-full overflow-hidden"
    >
      <!-- Coluna 1: Listar Unidades -->
      <div v-if="!showCadastroProduto">
        <ListadoHistoricoPedido
          :key="listaKey"
          ref="listaDados"
          @dado-selecionado="dadoSelecionado"
        />
      </div>

      <!-- Coluna 2: Alternar entre Detalhes e Cadastro -->
      <div class="flex flex-col gap-4">
        <template v-if="!showCadastroProduto">
          <template v-if="dadosSelecionado">
            <DetalhesHistoricoPedidos :dados="dadosSelecionado" />
          </template>
        </template>
      </div>
    </div>
</div>
  </LayoutFranqueado>
</template>

<script setup>
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import ListadoHistoricoPedido from '@/Components/EstruturaFranqueado/ListadoHistoricoPedido.vue';
import DetalhesHistoricoPedidos from '@/Components/EstruturaFranqueado/DetalhesHistoricoPedidos.vue';

// Dados do usuário selecionado
const dadosSelecionado = ref(null);

const listaKey = ref(0);
const showCadastroProduto = ref(false);

// Define os dados da Inusmos  selecionada
const dadoSelecionado = (dados) => {
  dadosSelecionado.value = dados;
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
