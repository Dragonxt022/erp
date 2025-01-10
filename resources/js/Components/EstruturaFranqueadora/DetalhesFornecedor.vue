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
          {{
            fornecedorData?.razao_social || fornecedorData.razao_social || 'N/A'
          }}
        </div>
        <div class="w-1/1">
          <ConfirmDialog
            :isVisible="isConfirmDialogVisible"
            :motivo="motivo"
            @confirm="handleConfirm"
            @cancel="handleCancel"
          />
          <div
            class="absolute top-3 right-4 cursor-pointer"
            @click="showConfirmDialog('Excluir esse Fornecedor?')"
          >
            <img
              src="/storage/images/delete.svg"
              alt="Deletar Usuário"
              class="w-6 h-6"
            />
          </div>
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
                ID# {{ (fornecedorData?.id ?? 0).toString().padStart(4, '0') }}
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
                {{ fornecedorData?.cnpj || 'N/A' }}
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
            {{ fornecedorData.razao_social || 'N/A' }}
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
            {{ fornecedorData.email || 'N/A' }}
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
            {{ fornecedorData.whatsapp || 'N/A' }}
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
            {{ fornecedorData.estado || 'N/A' }}
          </div>
        </div>
      </div>
    </div>
    <div v-if="fornecedor.id && !isEditMode" class="mt-7">
      <ButtonEditeMedio
        text="Editar insumos"
        icon-path="/storage/images/border_color.svg"
        @click="toggleEditMode"
        class="px-4 py-2 bg-[#F8F8F8] text-white rounded-lg"
      />
    </div>
  </div>
  <EditarFornecedor
    v-if="isEditMode"
    ref="dadosfornecedor"
    :isVisible="isEditMode"
    :fornecedor="fornecedor"
    @dadosfornecedor="fetchfornecedors"
    @cancelar="cancelEdit"
  />
</template>

<script setup>
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { useToast } from 'vue-toastification';
import LabelModel from '../Label/LabelModel.vue';
import ButtonEditeMedio from '../Button/ButtonEditeMedio.vue';
import EditarFornecedor from './EditarFornecedor.vue';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';

const toast = useToast();

const isConfirmDialogVisible = ref(false);
const motivo = ref('');
const showCadastrofornecedor = ref(false);

const isEditMode = ref(false);
const isLoading = ref(false);

const props = defineProps({
  fornecedor: {
    type: Object,
    required: true,
  },
});

// Dados do fornecedor em modo editável
const fornecedorData = ref({ ...props.fornecedor });

const deleteFornecedor = async () => {
  try {
    isLoading.value = true;

    await axios.delete(`/api/fornecedores/${props.fornecedor.id}`);

    Inertia.replace(route('franqueadora.fornecedores'), {
      preserveState: true,
    });

    toast.success('Fornecedor deletado com sucesso!');
  } catch (error) {
    toast.error(`Erro ao deletar o fornecedor: ${error.message}`);
  } finally {
    isLoading.value = false;
  }
};

const fetchfornecedors = () => {
  const dadosfornecedor = ref.dadosfornecedor;
  dadosfornecedor.fetchfornecedor();
};

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value;
  console.log('isEditMode:', isEditMode.value); // Para depuração
  showCadastrofornecedor.value = false;
};

const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmDialogVisible.value = true; // Exibe o diálogo de confirmação
};

const handleConfirm = () => {
  deleteFornecedor();
  isConfirmDialogVisible.value = false;
};

const handleCancel = () => {
  isConfirmDialogVisible.value = false;
};

const cancelEdit = () => {
  isEditMode.value = false;
  showCadastrofornecedor.value = true;
};
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
