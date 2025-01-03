<template>
  <div class="w-full h-[400px] bg-white rounded-[20px] p-7 relative">
    <div class="relative w-full h-full">
      <div class="flex items-center">
        <!-- Informações principais do Fornecedor -->
        <div class="w-full">
          <div class="text-[#262a27] text-[28px] font-['Figtree']">
            <LabelModel text="Fornecedor" />
            <input
              v-model="fornecedorData.nome_completo"
              class="w-full text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="Nome Completo"
            />
          </div>
          <div class="text-sm text-gray-500">
            <LabelModel text="CNPJ" />
            <input
              v-model="fornecedorData.cnpj"
              class="w-full text-center py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="CNPJ"
            />
          </div>
          <div class="text-sm text-gray-500">
            <LabelModel text="WhatsApp" />
            <input
              v-model="fornecedorData.whatsapp"
              class="w-full text-center py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="WhatsApp"
            />
          </div>
          <div class="text-sm text-gray-500">
            <LabelModel text="Estado" />
            <input
              v-model="fornecedorData.estado"
              class="w-full text-center py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="Estado"
            />
          </div>
          <LabelModel text="E-mail" />
          <input
            v-model="fornecedorData.email"
            class="w-full text-center py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            placeholder="E-mail"
          />
        </div>
      </div>

      <!-- Botão de atualização, exibido somente se houver alterações -->
      <div class="mt-4 text-right" v-if="isChanged">
        <ButtonPrimaryMedio text="Atualizar" @click="updateFornecedor" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import LabelModel from '../Label/LabelModel.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';

const notify = useToast();

const props = defineProps({
  fornecedor: {
    type: Object,
    required: true,
  },
});

// Dados do fornecedor em modo editável
const fornecedorData = ref({ ...props.fornecedor });

// Computed para verificar se houve alguma alteração
const isChanged = computed(() => {
  // Compara os dados originais com os dados editados
  return (
    JSON.stringify(fornecedorData.value) !== JSON.stringify(props.fornecedor)
  );
});

// Atualiza os dados do fornecedor
const updateFornecedor = async () => {
  try {
    // Envia os dados para o controller com PUT
    const response = await axios.put(
      `/api/fornecedores/${fornecedorData.value.id}`,
      fornecedorData.value
    );
    // Atualiza os dados do fornecedor no componente após sucesso
    Object.assign(props.fornecedor, response.data.data);
    notify.success('Fornecedor atualizado com sucesso!');
  } catch (error) {
    notify.error('Erro ao atualizar fornecedor.');
    console.error(error);
  }
};

// Sincroniza fornecedorData com props.fornecedor
watch(
  () => props.fornecedor,
  (newVal) => {
    fornecedorData.value = { ...newVal };
  }
);
</script>

<style scoped>
/* Tornando a lista rolável com barra de rolagem invisível */
.scrollbar-hidden::-webkit-scrollbar {
  display: none;
}

.scrollbar-hidden {
  -ms-overflow-style: none; /* Para o IE e Edge */
  scrollbar-width: none; /* Para o Firefox */
}
.owner {
  font-size: 13px;
  font-family: Figtree;
  font-weight: 500;
  line-height: 18px;
  color: #6d6d6e;
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
</style>
