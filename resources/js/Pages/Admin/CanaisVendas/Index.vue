<template>
  <LayoutFranqueadora>
    <!-- Cabeçalho da página -->
    <Head title="Canais de Vendas" />

    <!-- Botão para alternar entre Cadastro e Listagem -->
    <div v-if="!showCadastro" class="fixed bottom-6 right-6">
      <ButtonPrimaryMedio
        text="Cadastrar canal de vendas"
        class="w-full"
        iconPath="/storage/images/arrow_left_alt.svg"
        @click="toggleCadastro"
      />
    </div>
    <!-- Container principal -->
    <div
      class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2 card-container h-full overflow-hidden"
    >
      <!-- Coluna 1: Listar Unidades -->
      <div>
        <ListarCanisVendas
          :key="listaKey"
          ref="listaDados"
          @dado-selecionado="dadoSelecionado"
        />
      </div>

      <!-- Coluna 2: Alternar entre Detalhes e Cadastro -->
      <div class="flex flex-col gap-4">
        <template v-if="!showCadastro">
          <template v-if="dadosSelecionado">
            <DetalhesCanaisVendas
              :dados="dadosSelecionado"
              @atualizar="atualizarLista"
            />
          </template>
        </template>

        <template v-if="showCadastro">
          <CadastroCanalVendas @voltar="atualizarLista" />
        </template>
      </div>
    </div>
  </LayoutFranqueadora>
</template>

<script setup>
import LayoutFranqueadora from '@/Layouts/LayoutFranqueadora.vue';
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import ListarCanisVendas from '@/Components/EstruturaFranqueadora/ListarCanisVendas.vue';
import DetalhesCanaisVendas from '@/Components/EstruturaFranqueadora/DetalhesCanaisVendas.vue';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import CadastroCanalVendas from '@/Components/EstruturaFranqueadora/CadastroCanalVendas.vue';

// Dados do usuário selecionado
const dadosSelecionado = ref(null);

const listaKey = ref(0);
const showCadastro = ref(false);

// Define os dados da Inusmos  selecionada
const dadoSelecionado = (dados) => {
  dadosSelecionado.value = dados;
};

// Atualiza a lista de dados
const atualizarLista = () => {
  listaKey.value += 1;
  dadosSelecionado.value = null;
  showCadastro.value = false;
};

// Alterna entre a tela de cadastro e a listagem/detalhes
const toggleCadastro = () => {
  showCadastro.value = !showCadastro.value;
  if (showCadastro.value) {
    dadosSelecionado.value = null; // Reseta os detalhes ao abrir o cadastro
  }
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
