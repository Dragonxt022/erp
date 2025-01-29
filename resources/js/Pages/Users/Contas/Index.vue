<template>
  <LayoutFranqueado>
    <Head title="Painel" />
    <div class="painel-title">Cadastrar Contas</div>
    <div class="painel-subtitle">
      <p>Cadastrar contas a pagar da sua operação</p>
    </div>

    <CadastroContas />
  </LayoutFranqueado>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import CadastroContas from '@/Components/EstruturaFranqueado/CadastroContas.vue';
</script>

<style lang="css" scoped>
.painel-title {
  font-size: 34px;
  line-height: 15px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  margin-bottom: 10px; /* Espaçamento inferior */
}

.painel-subtitle {
  font-size: 17px;
  line-height: 25px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}
</style>
