<template>
  <AppLayout>
    <!-- Cabeçalho da página -->
    <Head title="Unidades" />

    <!-- Container principal da grade com 2 colunas -->
    <div class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2">
      <!-- Coluna 1: Listar Unidades -->
      <div>
        <!-- Componente para listar unidades -->
        <ListarUnidades
          ref="listarUnidades"
          @unidade-cadastrada="fetchUnidades"
          @unidade-selecionada="selecionarUnidade"
        />
      </div>

      <!-- Coluna 2: Alternar entre Detalhes e Cadastro -->
      <div class="flex flex-col gap-4">
        <!-- Mostrar Detalhes da Unidade Selecionada ou Cadastro -->
        <template v-if="!showCadastroUnidade">
          <template v-if="unidadeSelecionada">
            <!-- Mostrar Detalhes da Unidade Selecionada -->
            <DetalhesUnidade :unidade="unidadeSelecionada" />
          </template>
          <div class="absolute bottom-4 right-4">
            <ButtonPrimaryMedio
              text="Nova Unidade"
              iconPath="/storage/images/arrow_left_alt.svg"
              @click="toggleCadastro"
              :class="{ hidden: isEditMode }"
            />
          </div>
        </template>
        <template v-else>
          <div class="mt-4">
            <!-- Formulário de Cadastro de Unidade -->
            <CadastroUnidade
              :isVisible="showCadastroUnidade"
              @unidade-cadastrada="handleCadastro"
              @cancelar="toggleCadastro"
            />
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
import ListarUnidades from '@/Components/EstruturaFranqueadora/ListarUnidades.vue';
import DetalhesUnidade from '@/Components/EstruturaFranqueadora/DetalhesUnidade.vue';
import CadastroUnidade from '@/Components/EstruturaFranqueadora/CadastroUnidade.vue';

const unidadeSelecionada = ref(null);
const showCadastroUnidade = ref(false);

// Alterna a visibilidade entre Cadastro e Detalhes
const toggleCadastro = () => {
  showCadastroUnidade.value = !showCadastroUnidade.value;
};

// Função para atualizar unidades após o cadastro
const handleCadastro = () => {
  fetchUnidades();
  showCadastroUnidade.value = false;
};

// Atualiza a lista de unidades
const fetchUnidades = () => {
  const listarUnidades = ref.listarUnidades;
  listarUnidades.fetchUnidades();
};

// Define a unidade selecionada
const selecionarUnidade = (dados) => {
  unidadeSelecionada.value = dados; // Atribui a unidade selecionada
};
</script>

<style scoped></style>
