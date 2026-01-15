<template>
  <LayoutFranqueado>
    <Head title="Fluxo do Caixa " />
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
    </div>
    <div class="flex justify-between items-center mb-4">
      <!-- Coluna 1: Título e subtítulo -->
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
      <!-- Ajuste do grid para ser responsivo -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-6">
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
        </div>

        <!-- Coluna 1: Métodos de Pagamento -->
        <div
          v-else
          class="w-[427px] h-[174px] px-11 py-9 bg-white rounded-[10px] flex flex-col justify-center items-start"
        >
          <!-- Saudação e introdução -->
          <div class="text-[17px] font-semibold font-['Figtree'] leading-snug">
            <span class="text-[#6db631]">{{ usuarioNome }}</span>
            <span class="text-[#6d6d6d]">, vamos abrir o caixa com</span>
          </div>

          <!-- Valor em destaque -->
          <div
            class="text-[#262a27] text-[50px] font-bold font-['Figtree'] leading-[69.98px] tracking-wide"
          >
            {{ valorFormatado }}
          </div>

          <!-- Texto adicional -->
          <div
            class="text-[#6d6d6d] text-[17px] font-semibold font-['Figtree'] leading-snug"
          >
            de saldo em gaveta
          </div>
        </div>

        <div class="w-[427px] h-[43px] flex justify-center items-center">
          <!-- Ícone de gaveta -->
          <div class="w-6 h-6">
            <img src="/storage/images/payments_bleck.svg" alt="" />
          </div>

          <!-- Texto "Valor em gaveta" -->
          <div
            class="text-[#262a27] text-[17px] font-semibold font-['Figtree'] leading-snug ml-2"
          >
            Valor em gaveta
          </div>

          <!-- Espaço flexível -->
          <div class="flex-grow h-7"></div>

          <!-- Campo de valor -->
          <div class="px-4 py-2 flex justify-center items-center opacity-80">
            <div
              class="text-[#262a27] text-xl font-bold font-['Figtree'] leading-[25px] tracking-tight"
            >
              <div class="w-full">
                <input
                  v-model="valorDigitado"
                  @input="onInputChange"
                  type="text"
                  class="w-full h-10 px-4 border border-gray-300 rounded-lg focus:outline-none focus:border-[#6db631]"
                  placeholder="R$ 0,00"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="fixed bottom-4 right-4">
        <ButtonPrimaryMedio
          :class="
            valorDigitado && valorDigitado > 0 ? 'bg-[#6db631]' : 'bg-[#6d6d6d]'
          "
          class="w-full max-w-[200px]"
          text="Abrir caixa"
          iconPath="/storage/images/arrow_left_alt.svg"
          :disabled="!valorDigitado || valorDigitado <= 0"
          @click="abrirCaixa"
        />
      </div>
    </div>
    <AvisoSistemaNovo />
  </LayoutFranqueado>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import AvisoSistemaNovo from '@/Components/Modals/AvisoSistemaNovo.vue';
import { usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { useToast } from 'vue-toastification';

const toast = useToast();

const { errors, auth } = usePage().props;

// Nome do usuário autenticado
const usuarioNome = ref(auth.user?.name || 'Usuário');

// Estado do valor digitado
const valorDigitado = ref('');
const isLoading = ref(false);

// Função para formatar o valor como moeda brasileira
const formatarParaBRL2 = (valor) => {
  // Remove tudo que não é número ou vírgula
  let valorFormatado = valor.replace(/\D/g, '');

  // Formatação do valor para moeda
  if (valorFormatado.length > 2) {
    valorFormatado = valorFormatado.replace(/(\d)(\d{2})$/, '$1,$2'); // Adiciona a vírgula para os centavos
  }

  // Adiciona o ponto como separador de milhar
  valorFormatado = valorFormatado.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

  return valorFormatado;
};
// Função para lidar com a digitação do input e formatação
const onInputChange = (event) => {
  let valor = event.target.value;
  valor = formatarParaBRL2(valor); // Formatar o valor digitado
  valorDigitado.value = valor; // Atualizar o valor
};

const converterCentavosParaDecimal = (valorCentavos) => {
  return valorCentavos / 100;
};

const valorFormatado = computed(() => formatarParaBRL2(valorDigitado.value));

// Função para abrir o caixa
const abrirCaixa = async () => {
  isLoading.value = true;

  // Converte o valor digitado para número
  const valorCentavos =
    parseInt(valorDigitado.value.replace(/\D/g, ''), 10) || 0; // Remove caracteres não numéricos
  const valorDecimal = converterCentavosParaDecimal(valorCentavos); // Converte para decimal

  // Valida o valor decimal
  if (!valorCentavos || valorDecimal <= 0) {
    toast.warning('Por favor, insira um valor válido para abrir o caixa.');
    isLoading.value = false;
    return;
  }

  try {
    // Envia o valor no formato decimal para o backend
    const response = await axios.post('/api/caixas/abrir', {
      valor_inicial: valorDecimal, // Enviar valor no formato decimal
    });

    toast.success(
      `Caixa aberto com sucesso! Valor inicial: R$ ${valorDecimal.toFixed(2)}`
    );
    console.log(response.data);

    // Redireciona para o fluxo de caixa
    Inertia.visit(route('franqueado.fluxoCaixa'));
  } catch (error) {
    console.error(error);
    toast.error('Ocorreu um erro ao abrir o caixa.');
  } finally {
    isLoading.value = false;
  }
};

// Função para verificar se já existe um caixa aberto
const verificarCaixaAberto = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/caixas/abertos');
    if (response.data && response.data.aberto) {
      // Se existir caixa aberto, redireciona para fluxo de caixa
      toast.info('O caixa já esta aberto.');
      Inertia.visit(route('franqueado.fluxoCaixa')); // Usando Inertia para redirecionar
    }
  } catch (error) {
    console.error('Erro ao verificar caixa aberto:', error);
  } finally {
    isLoading.value = false;
  }
};

// Verifica se há um caixa aberto ao montar o componente
onMounted(() => {
  verificarCaixaAberto();
});
</script>

<style lang="css" scoped>
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

/* Estilos para o spinner */
.spinner {
  border: 4px solid #f3f3f3; /* Cor de fundo */
  border-top: 4px solid #6db631; /* Cor do topo */
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 2s linear infinite;
}

/* Animação do spinner */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
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
