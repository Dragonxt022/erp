<template>
  <LayoutFranqueado>
    <Head title="Fluxo do Caixa" />
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
    </div>
    <div class="flex justify-between items-center mb-4">
      <!-- Coluna 1: Título e subtítulo -->
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
          Fluxo do caixa
        </div>
        <div class="painel-subtitle">
          <p class="text-sm sm:text-base md:text-lg">
            Acompanhe seu negócio em tempo real
          </p>
        </div>
      </div>
    </div>
    <div class="mt-5">
      <!-- Ajuste do grid para ser responsivo -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6">
        <!-- Coluna 1: Métodos de Pagamento -->
        <div>
          <div class="bg-white rounded-lg px-12 py-8">
            <table class="w-full">
              <thead>
                <tr>
                  <th
                    class="text-gray-500 text-left text-sm sm:text-base md:text-lg font-semibold font-['Figtree'] leading-snug"
                  >
                    Método de pagamento
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr class="border-b">
                  <td class="py-2 flex items-center gap-5">
                    <img
                      src="/storage/images/cartao_credito.svg"
                      alt="Ícone do método"
                      class="w-8 h-8"
                    />
                    <div
                      class="w-fill text-[#262a27] text-[17px] font-semibold font-['Figtree'] leading-snug"
                    >
                      Cartão de crédito
                    </div>
                  </td>

                  <td class="py-2 w-ful">
                    <InputModel
                      v-model="porcentagem"
                      placeholder="R$ 0,00"
                      @input="atualizarMetodo"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div
            class="w-full h-[60px] bg-[#d2fac3] rounded-bl-[10px] rounded-br-[10px] px-12 flex justify-between items-center"
          >
            <div
              class="text-[#1d5915] text-xl font-bold font-['Figtree'] leading-snug"
            >
              TOTAL
            </div>
            <div
              class="text-[#1d5915] text-xl font-bold font-['Figtree'] leading-snug"
            >
              R$ 0,00
            </div>
          </div>
        </div>

        <!-- Coluna 2: Canais de Venda, Valor Vendido, Pedidos -->
        <div>
          <div class="bg-white rounded-lg px-12 py-8">
            <table class="w-full">
              <thead>
                <tr>
                  <th
                    class="text-left text-gray-500 text-sm sm:text-base md:text-lg font-semibold font-['Figtree'] leading-snug"
                  >
                    Canais de venda
                  </th>
                  <th
                    class="text-gray-500 text-sm sm:text-base md:text-lg font-semibold font-['Figtree'] leading-snug"
                  >
                    Valor vendido
                  </th>
                  <th
                    class="text-gray-500 text-sm sm:text-base md:text-lg font-semibold font-['Figtree'] leading-snug"
                  >
                    Pedidos
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr class="border-b">
                  <td class="py-2 flex items-center gap-5">
                    <img
                      src="/storage/images/cartao_credito.svg"
                      alt="Ícone do método"
                      class="w-8 h-8"
                    />
                    <div
                      class="w-fill text-[#262a27] text-[17px] font-semibold font-['Figtree'] leading-snug w-[170px]"
                    >
                      Canal de vendas
                    </div>
                  </td>
                  <td class="py-2">
                    <InputModel
                      v-model="porcentagem"
                      placeholder="R$ 0,00"
                      class="w-[130px]"
                      @input="atualizarMetodo"
                    />
                  </td>
                  <td class="py-2">
                    <InputModel
                      v-model="porcentagem"
                      placeholder="0"
                      class="w-[120px]"
                      @input="atualizarMetodo"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div
            class="w-full h-[60px] bg-[#d2fac3] rounded-bl-[10px] rounded-br-[10px] px-12 flex justify-between items-center"
          >
            <div
              class="text-[#1d5915] text-xl font-bold font-['Figtree'] leading-snug"
            >
              TOTAL
            </div>
            <div
              class="text-[#1d5915] text-xl font-bold font-['Figtree'] leading-snug"
            >
              R$ 0,00
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      class="mt-5 fixed bottom-0 left-[250px] w-full max-w-screen-xl mx-auto"
    >
      <div
        class="w-full h-[85px] p-5 bg-white flex justify-between items-center"
      >
        <!-- Grupo de botões "Suprimento" e "Sangria" -->
        <div class="flex gap-[13px]">
          <!-- Botão Suprimento -->
          <div
            class="w-[159px] h-[34px] px-[14.67px] py-1 bg-white rounded-[133.33px] flex justify-center items-center"
          >
            <div
              class="text-center text-[#548c25] text-[15px] font-semibold font-['Figtree'] uppercase leading-tight"
            >
              <ButtonSuave class="w-full" text="Suprimento" iconPath="" />
            </div>
          </div>

          <!-- Botão Sangria -->
          <div
            class="w-[159px] h-[34px] px-[14.67px] py-1 bg-white rounded-[133.33px] flex justify-center items-center"
          >
            <div
              class="text-center text-[#548c25] text-[15px] font-semibold font-['Figtree'] uppercase leading-tight"
            >
              <ButtonSuave class="w-full" text="Sangria" iconPath="" />
            </div>
          </div>
        </div>

        <!-- Botão Fechar Caixa -->
        <div class="flex justify-center items-center gap-2.5">
          <ButtonPrimaryMedio
            class="w-full max-w-[200px]"
            text="Fechar caixa"
            iconPath="/storage/images/arrow_left_alt.svg"
          />
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
import InputModel from '@/Components/Inputs/InputModel.vue';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import ButtonSuave from '@/Components/Button/ButtonSuave.vue';
import { Inertia } from '@inertiajs/inertia';
import axios from 'axios';
import { onMounted, ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const isLoading = ref(false);

// Função para verificar se já existe um caixa aberto
const verificarCaixaAberto = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/caixas/abertos');
    if (response.data && response.data.aberto) {
      // Se existir caixa aberto, redireciona para fluxo de caixa
      console.log('caixa aberto, permanecendo na pagina');
    } else {
      // Se não houver caixa aberto, redireciona para a página de abrir caixa
      toast.warning('Abra o caixa primeiro!');
      Inertia.visit(route('franqueado.abrirCaixa'));
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
.scroll-hidden {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE 10+ */
}

.scroll-hidden::-webkit-scrollbar {
  display: none; /* Chrome, Safari, Edge */
}

.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  line-height: 80%;
}

.painel-subtitle {
  font-size: 17px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}
/* Estilizando a tabela */
</style>
