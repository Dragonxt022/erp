<template>
  <div class="elemento-fixo">
    <div v-if="!isEditMode">
      <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
      </div>
      <div class="mt-[80px]"></div>
      <div
        class="w-full h-[100px] p-8 bg-white rounded-[20px] flex flex-col justify-center items-center gap-6"
      >
        <!-- Ícone e Nome lado a lado -->
        <div class="flex items-center gap-4">
          <img
            :src="getProfilePhotoUrl(dados.img_icon)"
            alt="Ícone"
            class="w-[50px] h-[50px]"
          />
          <div
            class="text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[34px] tracking-tight"
          >
            {{ dados.nome }}
          </div>
        </div>
      </div>

      <div
        class="mt-4 w-full p-8 bg-white rounded-[20px] flex justify-between items-center gap-5"
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
    <div v-if="dados.id && !isEditMode" class="mt-4">
      <ButtonEditeMedio
        text="Editar método"
        icon-path="/storage/images/border_color.svg"
        @click="toggleEditMode"
        class="px-4 py-2 bg-[#F8F8F8] text-white rounded-lg"
      />
    </div>
    <!-- Componete de editar colaborador! -->
    <EditarMetodoPagamento
      v-if="isEditMode"
      :metodo="dados"
      :isVisible="isEditMode"
      @voltar="cancelEdit"
    />
  </div>
</template>

<script setup>
import { defineProps, ref, watch } from 'vue';
import { defineEmits } from 'vue';
import LabelModel from '../Label/LabelModel.vue';
import Deslizante from '../Button/Deslizante.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { onMounted } from 'vue';
import ButtonEditeMedio from '../Button/ButtonEditeMedio.vue';
import EditarMetodoPagamento from '../EstruturaFranqueadora/EditarMetodoPagamento.vue';

const toast = useToast();

const props = defineProps({
  dados: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['atualizar']);

const isEditMode = ref(false);

const isLoading = ref(false);

// Define o status inicial com base nos dados recebidos
const status = ref(Boolean(props.dados.status));

// Observa mudanças nos dados para manter o status atualizado
watch(
  () => props.dados.status,
  (newStatus) => {
    status.value = Boolean(newStatus);
  }
);

// Método para alternar entre modo de edição e visualização
const cancelEdit = () => {
  isEditMode.value = false;
  emit('atualizar');
};

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value;
};

// Método para gerar a URL correta da imagem
const getProfilePhotoUrl = (img_icon) => {
  if (!img_icon) {
    return '/storage/images/no_imagem.svg'; // Caminho para imagem padrão
  }
  return new URL(img_icon, window.location.origin).href;
};

// Atualiza o status do método de pagamento
const atualizarMetodo = async (newStatus) => {
  try {
    isLoading.value = true;

    const payload = {
      default_payment_method_id: props.dados.id,
      status: newStatus ? 1 : 0,
    };

    await axios.post('/api/admin-metodos-pagamentos/alternar-status', payload);

    status.value = newStatus;
    toast.success('Método atualizado');
    emit('atualizar');
  } catch (error) {
    console.error('Erro ao atualizar o método de pagamento:', error);
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}
.icon-leaf {
  width: 55px;
  height: 55px;
  position: absolute;
  left: 5px;
  top: 10px;
}
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
.elemento-fixo {
  position: -webkit-sticky; /* Para navegadores que exigem o prefixo */
  position: sticky;
  top: 0; /* Defina o valor para o topo de onde o elemento ficará fixo */
  z-index: 10; /* Para garantir que o elemento fique sobre outros */
}
</style>
