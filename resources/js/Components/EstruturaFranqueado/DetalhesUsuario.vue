<template>
  <div
    v-if="!isEditMode"
    class="w-full h-[270px] bg-white rounded-[20px] p-12 relative overflow-y-auto scrollbar-hidden"
  >
    <div class="relative w-full h-full">
      <!-- Nome do Usuário -->
      <!-- Container das colunas -->
      <div class="flex items-center">
        <!-- Coluna da Imagem -->
        <div class="w-1/1 flex justify-center">
          <img
            :src="usuario.profilePhoto || '/storage/images/default-profile.png'"
            alt="Foto do Usuário"
            class="w-20 h-20 rounded-md shadow-lg"
          />
        </div>

        <!-- Coluna do Nome -->
        <div class="w-2/3 pl-5">
          <div
            class="text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[30px] tracking-tight"
          >
            {{ usuario.name || 'N/A' }}
          </div>
          <div class="owner">CPF: {{ usuario.cpf }}</div>
        </div>

        <div class="w-1/1">
          <ConfirmDialog
            :isVisible="isConfirmDialogVisible"
            :motivo="motivo"
            @confirm="handleConfirm"
            @cancel="handleCancel"
          />
          <div
            class="absolute top-4 right-4 cursor-pointer"
            @click="showConfirmDialog('excluir franqueado')"
          >
            <img
              src="/storage/images/delete.svg"
              alt="Deletar Usuário"
              class="w-6 h-6"
            />
          </div>
        </div>
      </div>

      <!-- E-mail abaixo das colunas -->
      <div class="mt-4">
        <p class="p-2">E-mail</p>
        <div
          class="w-full p-4 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        >
          <div
            class="text-[#262a27] text-base font-semibold font-['Figtree'] leading-[13px] tracking-tight"
          >
            {{ usuario.email || 'N/A' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref } from 'vue';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';
import { useToast } from 'vue-toastification';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';

const toast = useToast();

const props = defineProps({
  usuario: {
    type: Object,
    required: true,
  },
});

const isConfirmDialogVisible = ref(false);
const motivo = ref('');

const isEditMode = ref(false);
const isLoading = ref(false);

const deleteUsuario = async () => {
  try {
    isLoading.value = true;
    const response = await axios.delete(`/api/usuarios/${props.usuario.id}`); // URL para deletar o usuário
    console.log(response.data.message); // Mensagem de sucesso do backend
    toast.success('Usuário deletado com sucesso!'); // Exibe um toast de sucesso

    Inertia.replace(route('franqueadora.franqueados'), {
      preserveState: true, // Preserve o estado atual da página
    });
  } catch (error) {
    toast.error(
      'Erro ao deletar o usuário:',
      error.response?.data?.error || error.message
    );
    console.error(
      'Erro ao deletar o usuário:',
      error.response?.data?.error || error.message
    );
  } finally {
    isLoading.value = false;
  }
};

const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmDialogVisible.value = true; // Exibe o diálogo de confirmação
};

const handleConfirm = () => {
  deleteUsuario();
  isConfirmDialogVisible.value = false;
};

const handleCancel = () => {
  isConfirmDialogVisible.value = false;
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
