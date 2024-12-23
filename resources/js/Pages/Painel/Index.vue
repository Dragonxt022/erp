<template>
  <Head title="Painel" />
  <div class="flex h-screen">
    <!-- Barra lateral (Slidebar) -->
    <div class="w-64 bg-gray-800 text-white p-5">
      <h2 class="text-2xl mb-8">Dashboard</h2>
      <ul>
        <li>
          <a href="#" class="block py-2 px-4 hover:bg-gray-600">Home</a>
        </li>
        <li>
          <a href="#" class="block py-2 px-4 hover:bg-gray-600">
            Configurações
          </a>
        </li>
        <li>
          <a href="#" class="block py-2 px-4 hover:bg-gray-600">Mensagens</a>
        </li>
        <li>
          <a href="#" class="block py-2 px-4 hover:bg-gray-600">Perfil</a>
        </li>
      </ul>
    </div>

    <!-- Conteúdo -->
    <div class="flex-1 flex flex-col">
      <!-- Navbar (Barra superior) -->
      <nav class="bg-gray-800 text-white p-4 flex justify-between items-center">
        <div class="text-lg">Logo</div>
        <div>
          <form @submit.prevent="logout">
            <button
              type="submit"
              class="bg-red-600 px-4 py-2 rounded hover:bg-red-700"
            >
              Sair
            </button>
          </form>
        </div>
      </nav>

      <!-- Corpo do painel -->
      <div class="flex-1 p-8 bg-gray-100">
        <div class="bg-white p-6 rounded shadow-xl">
          <h1 class="text-2xl mb-4">Você está autenticado!</h1>
          <p>
            Bem-vindo ao painel administrativo. Explore as opções do menu à
            esquerda.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

// Função de logout, que pode ser implementada de acordo com sua lógica
const logout = () => {
  router.post(route('logout'));
};
</script>

<style lang="css" scoped>
/* Personalização adicional de estilos */
body {
  font-family: 'Arial', sans-serif;
}

ul {
  list-style-type: none;
  padding: 0;
}

ul li a {
  text-decoration: none;
}

ul li a:hover {
  background-color: #4a5568;
}
</style>
