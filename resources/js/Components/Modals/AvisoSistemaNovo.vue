<template>
  <div v-if="show" class="fixed inset-0 z-[100000] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="relative w-full max-w-lg overflow-hidden bg-white rounded-2xl shadow-2xl transition-all border border-gray-100">
      <!-- Decorative background -->
      <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#6db631] to-[#164110]"></div>
      
      <div class="p-8 space-y-6 text-center">
        <!-- Icon Container -->
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-[#6db631]/10 mb-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-[#6db631]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>

        <div class="space-y-3">
          <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
            Transição para o <span class="text-[#6db631]">Novo Sistema</span>
          </h2>
          <p class="text-gray-600 text-lg leading-relaxed px-4">
            Este sistema de caixa foi desativado. Para continuar sua operação com mais agilidade e segurança, utilize nossa nova plataforma dedicada.
          </p>
        </div>

        <div class="pt-4 pb-2">
          <button
            @click="redirecionar"
            class="group relative inline-flex items-center justify-center w-full px-8 py-4 text-lg font-bold text-white transition-all duration-300 bg-[#6db631] rounded-xl hover:bg-[#5da02a] focus:ring-4 focus:ring-[#6db631]/50 shadow-lg hover:shadow-[#6db631]/30 active:scale-[0.98]"
          >
            <span>Voltar para Página Inicial</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 ml-3 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </button>
        </div>

        <!-- botão para ir para pagina inicial -->
      <div class="pt-4 pb-2">
        <button
          @click="redirecionarHome"
          class="group relative inline-flex items-center justify-center w-full px-8 py-4 text-lg font-bold text-white transition-all duration-300 bg-[#6db631] rounded-xl hover:bg-[#5da02a] focus:ring-4 focus:ring-[#6db631]/50 shadow-lg hover:shadow-[#6db631]/30 active:scale-[0.98]"
        >
          <span>Ir para a Página Inicial</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 ml-3 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </button>
      </div>
      </div>

      <!-- Subtle background texture -->
      <div class="absolute bottom-0 right-0 opacity-5 pointer-events-none -mr-16 -mb-16">
        <svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="100" cy="100" r="100" fill="#6db631"/>
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: true
  }
});

const redirecionar = () => {
  window.location.href = 'https://login.taiksu.com.br/?redirect_uri=https%3A%2F%2Fcaixa.taiksu.com.br%2Fcallback';
};

const redirecionarHome = () => {
  window.location.href = 'https://admin.taiksu.com.br/franqueado/painel';
};
</script>

<style scoped>
/* Adicionando Figtree caso não esteja sendo carregada globalmente de forma confiável no componente */
h2, p, button, div {
  font-family: 'Figtree', sans-serif;
}
</style>
