<template>
  <AppLayout>
    <Head title="Unidades" />
    <div class="painel-title">Nossas Unidades</div>
    <div class="painel-subtitle">
      <p>Listagem de todas as unidades da franquia</p>
    </div>

    <div class="grid grid-cols-1 gap-4 mt-3 sm:grid-cols-2">
      <!-- Passando o ref para manipular o componente diretamente -->
      <ListarUnidades
        ref="listarUnidades"
        @unidade-cadastrada="fetchUnidades"
      />

      <div>
        <p>Coluna 2</p>
        <CadastroUnidade @unidade-cadastrada="fetchUnidades" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import CadastroUnidade from '@/Components/Estrutura/CadastroUnidade.vue';
import ListarUnidades from '@/Components/Estrutura/ListarUnidades.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const fetchUnidades = () => {
  const listarUnidades = $refs.listarUnidades;
  listarUnidades.fetchUnidades(); // Atualiza a lista diretamente no componente de listagem
};
</script>

<style lang="css" scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  margin-bottom: 10px; /* Espaçamento inferior */
}

.painel-subtitle {
  font-size: 17px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}
</style>
