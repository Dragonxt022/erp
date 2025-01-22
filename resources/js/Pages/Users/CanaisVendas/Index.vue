<template>
  <LayoutFranqueado>
    <!-- Cabeçalho da página -->
    <Head title="Insumos" />
    <!-- Container principal -->
    <div
      class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2 card-container h-full overflow-hidden"
    >
      <!-- Coluna 1: Listar Unidades -->
      <div v-if="!showCadastroProduto">
        <ListadoCanaisVendas
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
  </LayoutFranqueado>
</template>

<script setup>
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import ListadoCanaisVendas from '@/Components/EstruturaFranqueado/ListadoCanaisVendas.vue';
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
