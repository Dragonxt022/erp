<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Navbar from '@/Components/Laytout/Navbar.vue';
import Slidebar from '@/Components/Laytout/Slidebar.vue';

defineProps({
  title: String,
});

const logout = () => {
  router.post(route('logout'));
};
</script>

<style lang="css" scoped>
.content {
  position: fixed; /* Garante que o conteúdo se posicione de forma fixa na área visível */
  top: 70px; /* Desconta a altura da navbar */
  left: 249px; /* Desconta a largura da sidebar */
  width: calc(100vw - 249px); /* Largura máxima considerando a sidebar */
  height: calc(100vh - 70px); /* Altura máxima considerando a navbar */
  padding: 20px; /* Espaço interno */
  background-color: #f8f8f8; /* Define um fundo para destaque */
  overflow-y: auto; /* Habilita rolagem vertical */
}
</style>

<template>
  <div>
    <Head :title="title" />
    <Navbar />
    <Slidebar />
    <main class="content">
      <slot />
    </main>
  </div>
</template>
