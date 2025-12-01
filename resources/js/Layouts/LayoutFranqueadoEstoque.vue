<script setup>
import { Head } from '@inertiajs/vue3';
import NavbarControleEstoque from '@/Components/LaytoutFranqueado/NavbarControleEstoque.vue';

defineProps({
  title: String,
});
</script>

<style lang="css" scoped>
.content {
  position: fixed; /* Garante que o conteúdo se posicione de forma fixa na área visível */
  top: 70px; /* Desconta a altura da navbar */
  left: 0; /* Desconta a largura da sidebar */
  width: 100%; /* Largura máxima considerando a sidebar */
  height: 100vh; /* Altura máxima considerando a navbar */
  padding: 20px; /* Espaço interno */
  background-color: #f8f8f8; /* Define um fundo para destaque */
  overflow-y: auto; /* Habilita rolagem vertical */
}
</style>

<template>
  <div>
    <Head :title="title" />
    <NavbarControleEstoque />
    <main class="content">
      <slot />
    </main>
  </div>
</template>
