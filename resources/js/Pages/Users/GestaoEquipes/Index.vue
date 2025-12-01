<template>
  <LayoutFranqueado>
    <Head title="Gestão de Equipe" />

    <!-- Grid de Listagem e Detalhes (visível apenas se showCadastro for false) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-3 h-full">
      <!-- Componente de Listagem -->
      <ListarColaboradores
        :key="listaKey"
        ref="listaDados"
        @usuario-selecionado="dadoSelecionado"
        @abrir-cadastro="toggleCadastro"
      />

      <div v-if="showCadastro" class="mt-3">
        <CadastroColaborador @voltar="cancelarCadastro" />
      </div>

      <!-- Componente de Detalhes (mostra apenas se houver um item selecionado) -->
      <div v-if="dadosSelecionado">
        <DetalhesColaborador
          :dados="dadosSelecionado"
          @voltar="atualizaConponetes"
          @atualiza="atualizalista"
        />
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import DetalhesColaborador from '@/Components/EstruturaFranqueado/DetalhesColaborador.vue';
import ListarColaboradores from '@/Components/EstruturaFranqueado/ListarColaboradores.vue';
import CadastroColaborador from '@/Components/EstruturaFranqueado/CadastroColaborador.vue';

const listaKey = ref(0);
const dadosSelecionado = ref(null);
const showCadastro = ref(false);

// Alterna entre a tela de cadastro e a listagem/detalhes
const toggleCadastro = () => {
  showCadastro.value = !showCadastro.value;
  if (showCadastro.value) {
    dadosSelecionado.value = null; // Reseta os detalhes ao abrir o cadastro
  }
};

// Define os dados selecionados para exibição nos detalhes
const dadoSelecionado = (dados) => {
  dadosSelecionado.value = dados;
};

// Cancela o cadastro e volta para a listagem
const cancelarCadastro = () => {
  showCadastro.value = false;
  listaKey.value += 1; // Força a atualização da listagem
  console.log('cancelarCadastro');
};

const atualizaConponetes = () => {
  dadosSelecionado.value = null;
  listaKey.value += 1;
};

const atualizalista = () => {
  listaKey.value += 1;
  dadoSelecionado.value = null;
};
</script>
