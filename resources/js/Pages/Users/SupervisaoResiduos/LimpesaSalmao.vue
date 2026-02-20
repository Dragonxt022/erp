<template>
  <LayoutFranqueado>
    <Head title="Limpeza de Salmão" />
    <div class="w-full px-32 py-16">
      <div class="mx-auto flex flex-col items-center justify-center w-full bg-white rounded-lg px-12 py-24 border border-gray-200 group">
          <img src="https://login.taiksu.com.br/applications/6993c89d90364.png" alt="Logo" class="w-16 h-16 mb-4 group-hover:scale-110 transition-all ease-in-out duration-300">
          <h2 class="text-2xl font-semibold text-gray-700 text-center">Estamos migrando para o Resíduos</h2>
          <p class="text-lg text-gray-500 text-center">Registre a limpeza de salmão no novo app.</p>
          <div class="flex flex-col items-center justify-center gap-2 mt-2">
            <a href="https://login.taiksu.com.br/?redirect_uri=https%3A%2F%2Fresiduos.taiksu.com.br%2Fcallback" class="text-md text-white bg-green-500 px-12 shadow-xl hover:shadow-sm transition-shadow duration-400 py-2 rounded-full hover:bg-green-600 transition-all ease-in-out duration-300 mt-4 text-center">Acessar</a>
            <a href="https://ajuda.taiksu.com.br/artigos/08a4bed4-0b3d-11f1-a9cb-3ae8d4b024df" class="text-sm text-gray-500 hover:text-green-600 hover:underline mt-1">Aprender a usar</a>
          </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
</script>

<style scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
  line-height: 80%;
}

.painel-subtitle {
  font-size: 17px;
  color: #6d6d6e;
  max-width: 600px;
}

.input-text {
  width: 100%;
  padding: 8px;
  background: transparent;
  border: 1px solid #262a27;
  border-radius: 8px;
  text-align: center;
  font-size: 16px;
  color: #262a27;
  font-family: 'Figtree', sans-serif;
}

.input-select {
  width: 100%;
  height: 44px;
  background: #f3f8f3;
  border: 2px solid #d7d7db;
  padding: 8px;
  font-size: 16px;
  font-weight: bold;
  color: #6db631;
  border-radius: 8px;
  outline: none;
}

.botao-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  display: flex;
  gap: 10px;
}
</style>
