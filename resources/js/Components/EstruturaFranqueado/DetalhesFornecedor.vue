<template>
  <div
    v-if="!isEditMode"
    class="w-full h-[530px] bg-white rounded-[20px] p-7 relative"
  >
    <div class="relative w-full h-full">
      <div class="flex items-center">
        <!-- Informações principais do Fornecedor -->
        <div
          class="absolute left-0 top-0 text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[50px] tracking-tight"
        >
          {{ fornecedor?.razao_social || fornecedor.razao_social || 'N/A' }}
        </div>
        <div class="relative w-full h-full">
          <!-- Exibe o ID e CNPJ -->
          <div
            class="flex justify-between items-center absolute left-0 top-[50px] w-full"
          >
            <div
              class="w-[150px] h-[43px] px-2.5 py-3 bg-[#f3f8f3] rounded-lg flex justify-between items-center"
            >
              <div class="w-6 h-6 rounded-full">
                <img
                  src="/storage/images/storefront-bleck.svg"
                  alt=""
                  class="inline-block"
                />
              </div>
              <div
                class="text-[#262a27] text-base font-semibold font-['Figtree'] leading-[13px] tracking-tight"
              >
                ID# {{ (fornecedor?.id ?? 0).toString().padStart(4, '0') }}
              </div>
            </div>
            <div
              class="w-[219px] h-[43px] px-2.5 py-3 bg-[#f3f8f3] rounded-lg flex justify-between items-center"
            >
              <div class="w-6 h-6 rounded-full">
                <img
                  src="/storage/images/assured_workload.svg"
                  alt=""
                  class="inline-block"
                />
              </div>
              <div
                class="text-[#262a27] text-base font-semibold font-['Figtree'] leading-[13px] tracking-tight"
              >
                {{ fornecedor?.cnpj || 'N/A' }}
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-[130px]">
        <LabelModel text="Razão Social" />
        <div
          class="w-full p-4 bg-transparent border border-gray-300 rounded-lg outline-none text-center text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        >
          <div
            class="text-[#262a27] text-base font-['Figtree'] leading-[13px] tracking-tight"
          >
            {{ fornecedor.razao_social || 'N/A' }}
          </div>
        </div>
      </div>

      <div class="mt-4">
        <LabelModel text="E-mail" />
        <div
          class="w-full p-4 bg-transparent border border-gray-300 rounded-lg outline-none text-center text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        >
          <div
            class="text-[#262a27] text-base font-['Figtree'] leading-[13px] tracking-tight"
          >
            {{ fornecedor.email || 'N/A' }}
          </div>
        </div>
      </div>

      <div class="mt-4">
        <LabelModel text="WhatsApp" />
        <div
          class="w-full p-4 bg-transparent border border-gray-300 rounded-lg outline-none text-center text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        >
          <div
            class="text-[#262a27] text-base font-['Figtree'] leading-[13px] tracking-tight"
          >
            {{ fornecedor.whatsapp || 'N/A' }}
          </div>
        </div>
      </div>

      <div class="mt-4">
        <LabelModel text="Estado" />
        <div
          class="w-full p-4 bg-transparent border border-gray-300 rounded-lg outline-none text-center text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        >
          <div
            class="text-[#262a27] text-base font-['Figtree'] leading-[13px] tracking-tight"
          >
            {{ fornecedor.estado || 'N/A' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

import { useToast } from 'vue-toastification';
import LabelModel from '../Label/LabelModel.vue';
const isEditMode = ref(false);
const props = defineProps({
  fornecedor: {
    type: Object,
    required: true,
  },
});
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
