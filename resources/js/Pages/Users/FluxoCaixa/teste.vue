<template>
  <LayoutFranqueado>
    <Head title="Fluxo do Caixa" />

    <div class="flex justify-between items-center mb-4">
      <!-- Título e subtítulo -->
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
          Abertura de caixa
        </div>
        <div class="painel-subtitle">
          <p class="text-sm sm:text-base md:text-lg">
            Iniciar a operação de faturamento diário
          </p>
        </div>
      </div>
    </div>

    <div class="mt-5">
      <!-- Grid responsivo -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-6">
        <!-- Bloco 1: Saudação inicial -->
        <div
          v-if="!valorDigitado"
          class="w-[427px] h-[174px] px-11 py-9 bg-white rounded-[10px] flex flex-col justify-center items-start"
        >
          <div class="text-[17px] font-semibold font-['Figtree'] leading-snug">
            <span class="text-[#6db631]">{{ usuarioNome }}</span>
            <span class="text-[#6d6d6d]">
              , com quantos reais
              <br />
              você vai abrir o caixa?
            </span>
          </div>
          <div class="w-full mt-4">
            <input
              v-model="valorDigitado"
              type="number"
              class="w-full h-10 px-4 border border-gray-300 rounded-lg focus:outline-none focus:border-[#6db631]"
              placeholder="Digite o valor"
            />
          </div>
        </div>

        <!-- Bloco 2: Confirmação do valor -->
        <div
          v-else
          class="w-[427px] h-[174px] px-11 py-9 bg-white rounded-[10px] flex flex-col justify-center items-start"
        >
          <div class="text-[17px] font-semibold font-['Figtree'] leading-snug">
            <span class="text-[#6db631]">{{ usuarioNome }}</span>
            <span class="text-[#6d6d6d]">, vamos abrir o caixa com</span>
          </div>
          <div
            class="text-[#262a27] text-[50px] font-bold font-['Figtree'] leading-[69.98px] tracking-wide"
          >
            R$ {{ parseFloat(valorDigitado).toFixed(2) }}
          </div>
          <div
            class="text-[#6d6d6d] text-[17px] font-semibold font-['Figtree'] leading-snug"
          >
            de saldo em gaveta
          </div>
          <button
            @click="abrirCaixa"
            class="mt-6 w-full py-2 text-white bg-[#6db631] rounded-lg hover:bg-[#548c25] focus:outline-none"
          >
            Confirmar e Abrir Caixa
          </button>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { ref } from 'vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

// Nome do usuário autenticado
const usuarioNome = 'Karol';

// Estado do valor digitado
const valorDigitado = ref('');

// Função para abrir o caixa
const abrirCaixa = async () => {
  if (!valorDigitado.value || parseFloat(valorDigitado.value) <= 0) {
    alert('Por favor, insira um valor válido para abrir o caixa.');
    return;
  }

  try {
    const response = await axios.post('/api/caixa/abrir', {
      valor_inicial: parseFloat(valorDigitado.value),
    });
    alert('Caixa aberto com sucesso!');
    console.log(response.data);
  } catch (error) {
    console.error(error);
    alert('Ocorreu um erro ao abrir o caixa.');
  }
};
</script>

<style lang="css" scoped>
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

input:focus {
  outline: none;
}
</style>
