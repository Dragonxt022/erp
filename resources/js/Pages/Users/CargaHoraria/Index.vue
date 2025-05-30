<template>
  <LayoutFranqueado>
    <Head title="Carga Horária" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-3 h-full">
      <!-- Componente de Listagem -->
      <ListarColaboradoresHorarios
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
        <DetalhesColaboradorHorarios
          :dados="dadosSelecionado"
          @voltar="atualizaConponetes"
          @atualiza="atualizalista"
        />
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import ListarColaboradoresHorarios from '@/Components/EstruturaFranqueado/ListarColaboradoresHorarios.vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import DetalhesColaboradorHorarios from '@/Components/EstruturaFranqueado/DetalhesColaborador.vue';

// Estado para armazenar os dados selecionados
const dadosSelecionado = ref(null);
const listaKey = ref(0);
const showCadastro = ref(false);
const listaDados = ref(null);
const emit = defineEmits(['usuario-selecionado', 'abrir-cadastro']);
const dados = ref(null);
const toggleCadastro = () => {
  showCadastro.value = !showCadastro.value;
};
const atualizaConponetes = () => {
  listaKey.value++;
};
const atualizarLista = () => {
  listaDados.value.fetchUsuarios();
};
const cancelarCadastro = () => {
  showCadastro.value = false;
  listaKey.value++;
};
const dadoSelecionado = (dado) => {
  dadosSelecionado.value = dado;
  listaKey.value++;
};


</script>

<style lang="css" scoped>

</style>
