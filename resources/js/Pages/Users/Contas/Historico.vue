<template>
  <LayoutFranqueado>
    <Head title="Painel" />

    <div v-if="!showCadastro" class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-3 h-full">
      <!-- Componente de Listagem -->
      <ListarContasApagarHistorico
        :key="listaKey"
        ref="listaDados"
        @dado-selecionado="dadoSelecionado"
        @dados-carregados="atualizaGastos"
      />

      <!-- Componente de Detalhes (mostra apenas se houver um item selecionado) -->
      <div v-if="dadosSelecionado">
        <DetalhesContasApagarHistorico
          :dados="dadosSelecionado"
          @voltar="atualizaConponetes"
          @atualiza="atualizaConponetes"
        />
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import ListarContasApagarHistorico from '@/Components/EstruturaFranqueado/ListarContasApagarHistorico.vue';
import DetalhesContasApagarHistorico from '@/Components/EstruturaFranqueado/DetalhesContasApagarHistorico.vue';
import ResumoGastos from '@/Components/EstruturaFranqueado/ResumoGastos.vue';

const listaKey = ref(0);
const dadosSelecionado = ref(null);
const showCadastro = ref(false);

// Array que será passado para o gráfico
const gastos = ref([]);

// Atualiza os dados do gráfico quando a listagem carrega os dados
const atualizaGastos = (grupos) => {
  gastos.value = grupos.map(grupo => ({
    categoria: grupo.label,
    valor: grupo.total,            // valor numérico
    valor_formatado: grupo.total_formatado // já em Real
  }));
};

const dadoSelecionado = (dados) => {
  dadosSelecionado.value = dados;
};

const toggleCadastro = () => {
  showCadastro.value = !showCadastro.value;
  if (showCadastro.value) {
    dadosSelecionado.value = null;
  }
};

const cancelarCadastro = () => {
  showCadastro.value = false;
  listaKey.value += 1;
};

const atualizaConponetes = () => {
  dadosSelecionado.value = null;
  listaKey.value += 1;
};
</script>
