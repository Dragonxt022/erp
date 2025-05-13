<template>
  <AppLayout>
    <Head title="Gestão de Atividades" />

    <!-- Quando não está em modo de cadastro, exibe a listagem e os detalhes -->
    <Transition name="fade" mode="out-in">
      <div v-if="!showView" key="listagem" class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2">
        <!-- Coluna 1: Listagem -->
        <div>
          <ListarGestaoAtividade ref="listar" @cadastrar="fetch" @selecionado="dadoSelecionado" />
        </div>

        <!-- Coluna 2: Detalhes e botão -->
        <div class="flex flex-col gap-4">
          <template v-if="DadosSelecionados">
            <DetalhesGestaoOperacional :informacoes="DadosSelecionados" />
          </template>
          <div class="absolute bottom-4 right-4">
            <ButtonPrimaryMedio
              text="Cadastrar nova atividade"
              iconPath="/storage/images/arrow_left_alt.svg"
              @click="toggleCadastro"
              :class="{ hidden: isEditMode }"
            />
          </div>
        </div>
      </div>

      <!-- Quando está em modo de cadastro, exibe o formulário ocupando a tela -->
      <div v-else key="cadastro" class="mt-4">
        <CadastroGestaoAtividade
          :isVisible="showView"
          @cadastrar="handleCadastro"
          @cancelar="toggleCadastro"
        />
      </div>
    </Transition>
  </AppLayout>
</template>


<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/LayoutFranqueadora.vue';
import { Head } from '@inertiajs/vue3';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import DetalhesGestaoOperacional from '@/Components/EstruturaFranqueadora/DetalhesGestaoOperacional.vue';
import ListarGestaoAtividade from '@/Components/EstruturaFranqueadora/ListarGestaoAtividade.vue';
import CadastroGestaoAtividade from '@/Components/EstruturaFranqueadora/CadastroGestaoAtividade.vue';

const DadosSelecionados = ref(null);
const showView = ref(false);
const isEditMode = ref(false); // Para controle de edição, se necessário

const toggleCadastro = () => {
  showView.value = !showView.value;
};

const handleCadastro = () => {
  fetch();
  showView.value = false;
};

const fetch = () => {
  const listar = ref.listar;
  listar.fetch();
};

const dadoSelecionado = (dados) => {
  DadosSelecionados.value = dados;
};
</script>


<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
