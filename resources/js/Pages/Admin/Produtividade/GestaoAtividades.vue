<template>
  <AppLayout>
    <!-- Cabeçalho da página -->

    <Head title="Gestão de Atividades" />

    <!-- Container principal da grade com 2 colunas -->
    <div class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2">
      <!-- Coluna 1: Listar informacoess -->
      <div>
        <!-- Componente para listar informacoess -->
        <ListarGestaoAtividade ref="listar" @cadastrar="fetch"
          @selecionado="dadoSelecionado" />
      </div>

      <!-- Coluna 2: Alternar entre Detalhes e Cadastro -->
      <div class="flex flex-col gap-4">
        <!-- Mostrar Detalhes da informacoes Selecionada ou Cadastro -->
        <template v-if="!showView">
          <template v-if="DadosSelecionados">
            <!-- Mostrar Detalhes da informacoes Selecionada -->
            <DetalhesGestaoOperacional :informacoes="DadosSelecionados" />
          </template>
          <div class="absolute bottom-4 right-4">


            <ButtonPrimaryMedio text="Cadastrar nova atividade" iconPath="/storage/images/arrow_left_alt.svg"
              @click="toggleCadastro" :class="{ hidden: isEditMode }" />
          </div>
        </template>
        <template v-else>
          <div class="mt-4">
            <!-- Formulário de Cadastro de informacoes -->
            <CadastroSetorOpeperacional :isVisible="showView" @cadastrar="handleCadastro"
              @cancelar="toggleCadastro" />
          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/LayoutFranqueadora.vue';
import { Head } from '@inertiajs/vue3';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import DetalhesGestaoOperacional from '@/Components/EstruturaFranqueadora/DetalhesGestaoOperacional.vue';
import ListarGestaoAtividade from '@/Components/EstruturaFranqueadora/ListarGestaoAtividade.vue';
import CadastroSetorOpeperacional from '@/Components/EstruturaFranqueadora/CadastroSetorOpeperacional.vue';



const DadosSelecionados = ref(null);
const showView = ref(false);

// Alterna a visibilidade entre Cadastro e Detalhes
const toggleCadastro = () => {
  showView.value = !showView.value;
};

// Função para atualizar informacoess após o cadastro
const handleCadastro = () => {
  fetch();
  showView.value = false;
};

// Atualiza a lista de informacoess
const fetch = () => {
  const listar = ref.listar;
  listar.fetch();
};

// Define a informacoes selecionada
const dadoSelecionado = (dados) => {
  DadosSelecionados.value = dados; // Atribui a informacoes selecionada
};
</script>

<style scoped></style>
