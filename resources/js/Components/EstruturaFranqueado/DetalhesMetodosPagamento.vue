<template>
  <div class="elemento-fixo">
    <div v-if="!isEditMode">
      <div
        class="w-full h-[237px] p-12 bg-white rounded-[20px] flex-col justify-center items-center gap-10 inline-flex"
      >
        <div
          class="self-stretch text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[34px] tracking-tight"
        >
          {{ dados.nome }}
        </div>
        <div class="w-full h-[63px] relative">
          <LabelModel
            class="font-semibold text-gray-900"
            text="Quanto você paga de taxa?"
          />
          <InputModel
            v-model="porcentagem"
            placeholder="5%"
            @input="atualizarMetodo"
          />
        </div>
      </div>

      <div
        class="mt-8 w-full p-12 bg-white rounded-[20px] flex justify-between items-center gap-5"
      >
        <!-- Coluna 1 -->
        <div class="flex flex-col w-2/3">
          <LabelModel
            class="font-semibold text-gray-900"
            text="Utilizar este método de pagamento?"
          />
          <span class="text-sm font-semibold text-gray-700">
            {{ status ? 'Ativado' : 'Desativado' }}
          </span>
        </div>

        <!-- Coluna 2 -->
        <div class="w-1/2 flex justify-end">
          <Deslizante v-model:status="status" @click="atualizarMetodo" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref, onMounted, watch } from 'vue';
import { defineEmits } from 'vue';
import InputModel from '../Inputs/InputModel.vue';
import LabelModel from '../Label/LabelModel.vue';
import Deslizante from '../Button/Deslizante.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
  dados: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['atualizar']);

const isEditMode = ref(false);
const porcentagem = ref(0); // Inicializa com 0 até a API preencher
const status = ref(false);

let toastActive = false;

// Função para buscar os detalhes do método
const buscarMetodo = async () => {
  try {
    const response = await axios.get(
      `/api/metodos-pagamentos/verificar-pagamentos/${props.dados.id}`
    );

    if (response.status === 200 && response.data) {
      console.log('Dados recebidos:', response.data);
      porcentagem.value = response.data.data.porcentagem || 0;
      status.value = response.data.data.status === 1;
    } else if (response.status === 404 || !response.data) {
      // Caso o método de pagamento não tenha sido encontrado
      console.info('Nenhum método de pagamento encontrado para esta unidade.');
      porcentagem.value = 0; // Resetar o valor
      status.value = false; // Desativar a chave
    }
  } catch (error) {
    // Log de erro quando falhar em buscar o método de pagamento
    console.error('Erro ao buscar o método de pagamento:', error);
    // Resetando valores caso haja erro (exemplo, rede fora)
    porcentagem.value = 0;
    status.value = false;
  }
};

// Atualiza ou cria método de pagamento
const atualizarMetodo = async () => {
  try {
    // Função para simular o atraso de 3 segundos
    await new Promise((resolve) => setTimeout(resolve, 2000));

    const payload = {
      unidade_id: props.dados.id,
      default_payment_method_id: props.dados.id,
      porcentagem: porcentagem.value,
      status: status.value ? 1 : 0,
    };

    const response = await axios.post(
      '/api/metodos-pagamentos/upsert',
      payload
    );

    // Exibe o toast somente se não estiver ativo
    if (!toastActive) {
      toastActive = true;
      toast.success('Método atualizado', {
        onClose: () => {
          toastActive = false; // Marca como inativo quando o toast for fechado
        },
      });
    }
  } catch (error) {
    console.error('Erro ao atualizar o método de pagamento:', error);
  }
};

// Watcher para monitorar o ID da prop e buscar os dados novamente
watch(
  () => props.dados.id,
  () => {
    buscarMetodo();
  }
);

// Busca os dados do método ao carregar o componente
onMounted(() => {
  buscarMetodo();
});
</script>

<style scoped>
.elemento-fixo {
  position: -webkit-sticky; /* Para navegadores que exigem o prefixo */
  position: sticky;
  top: 0; /* Defina o valor para o topo de onde o elemento ficará fixo */
  z-index: 10; /* Para garantir que o elemento fique sobre outros */
}
</style>
