<template>
  <AppLayout>
    <!-- Cabeçalho da página -->
    <Head title="Unidades" />

    <!-- Container principal da grade com 2 colunas -->
    <div class="grid grid-cols-1 gap-4 mt-3 sm:grid-cols-2">
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
          <DetalhesUnidade :unidade="unidadeSelecionada" />
          <div class="flex justify-end">
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
import CadastroUnidade from '@/Components/Estrutura/CadastroUnidade.vue';
import ListarUnidades from '@/Components/Estrutura/ListarUnidades.vue';
import DetalhesUnidade from '@/Components/Estrutura/DetalhesUnidade.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';

const unidadeSelecionada = ref({ unidade: [], usuarios: [] });
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
  unidadeSelecionada.value = dados; // Atribui a unidade e os usuários
};
</script>

<style scoped></style>
