<template>
  <div v-if="!isEditMode">
    <div class="w-full h-[180px] bg-white rounded-[20px] p-12 mb-4">
      <!-- Exibe informações da informacoes apenas quando não está no modo de edição -->
      <div class="flex items-center">
        <!-- Coluna da Imagem -->
        <div class="w-1/1 flex justify-center">
          <img :src="getProfilePhotoUrl(informacoes.profile_photo)" alt="Imagem"
            class="w-20 h-20 p-2 rounded-md shadow-lg" />
        </div>
        <div class="w-2/3 pl-5">
          <div class="text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[30px] tracking-tight">
            {{ informacoes.name || 'N/A' }}
          </div>
        </div>
        <div class="w-1/1">
          <ConfirmDialog :isVisible="isConfirmDialogVisible" :motivo="motivo" @confirm="handleConfirm"
            @cancel="handleCancel" />
          <div class="absolute top-[110px] right-[70px] cursor-pointer"
            @click="showConfirmDialog('excluir setor operacional')">
            <img src="/storage/images/delete.svg" alt="excluir" class="w-6 h-6" />
          </div>
        </div>
      </div>
    </div>

    <div class="w-full h-[280px] bg-white rounded-[20px] p-12">
      <div class="flex items-center gap-2 mb-[10px]">
        <!-- Ícone com fundo mais escuro -->
        <div class="w-6 h-6 rounded-full flex items-center justify-center">
          <!-- Substitua pelo ícone desejado -->
          <img src="/storage/images/campaign_bleck.svg" alt="icone" />
        </div>

        <!-- Texto de descrição -->
        <div class="text-gray-700 text-[15px] font-semibold font-['Figtree']">
          Emitir comunicado
        </div>
      </div>
      <textarea
        class="w-full h-[100px] bg-[#F8F8F8] rounded-lg p-4 text-gray-700 text-[15px] font-['Figtree'] focus:outline-none focus:ring-2 focus:ring-green-500"
        placeholder="Digite sua mensagem aqui......" rows="4" v-model="comunicado"></textarea>

      <p class="text-right text-xs mt-1" :class="{
        'text-gray-500': comunicado.length <= 255,
        'text-red-500': comunicado.length > 255,
      }">
        {{ comunicado.length }}/255 caracteres
      </p>

      <ButtonPrimaryMedio text="Enviar comunicado" class="w-full mt-2" @click="enviarComunicado" />


    </div>

  </div>

  <!-- Exibe o formulário de edição quando isEditMode é true -->
  <div v-if="informacoes.id && !isEditMode" class="mt-4">
    <ButtonEditeMedio text="Editar informacoes" icon-path="/storage/images/border_color.svg" @click="toggleEditMode"
      class="px-4 py-2 bg-[#F8F8F8] text-white rounded-lg" />
  </div>
  <EditarSetorOperacional v-if="isEditMode" ref="dadosinformacoes" :isVisible="isEditMode" :informacoes="informacoes"
    @dadosinformacoes="fetchinformacoes" @cancelar="cancelEdit" />


</template>

<script setup>
import { defineProps, defineEmits, ref } from 'vue';
import ButtonEditeMedio from '../Button/ButtonEditeMedio.vue';
import EditarSetorOperacional from './EditarSetorOperacional.vue';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const emit = defineEmits(['cancelar']);

const toast = useToast();

const props = defineProps({
  informacoes: {
    type: Object,
    required: true,
  },
});

const isConfirmDialogVisible = ref(false);
const motivo = ref('');
const comunicado = ref('');


const showConfirmDialog = (action) => {
  motivo.value = action;
  isConfirmDialogVisible.value = true;
};
const isLoading = ref(false);
const handleConfirm = () => {
  deleteSelecionado();
  isConfirmDialogVisible.value = false;
  emit('cancelar');
};
const handleCancel = () => {
  // Lógica para cancelar a ação
  isConfirmDialogVisible.value = false;
};

const deleteSelecionado = async () => {
  try {
    isLoading.value = true;
    const response = await axios.delete(`/api/admin-operacionais/${props.informacoes.id}`);
    toast.success('setor excluído com sucesso!');
    isLoading.value = false;
    window.location.reload();

  } catch (error) {
    console.error('Erro ao excluir setor:', error);
    toast.error('Erro ao excluir setor.');
    isLoading.value = false;
  }
};


const getProfilePhotoUrl = (profilePhoto) => {
  return profilePhoto ? `/storage/${profilePhoto}` : '/storage/images/default_profile.png';
};
const showCadastroinformacoes = ref(false);
const isEditMode = ref(false);

const fetchinformacoes = () => {
  const dadosinformacoes = ref.dadosinformacoes;
  dadosinformacoes.fetchinformacoes();
};

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value;
  showCadastroinformacoes.value = false;
};

// Função de cancelamento que será emitida pelo componente de edição
const cancelEdit = () => {
  isEditMode.value = false;
  showCadastroinformacoes.value = true;
};

// Função para enviar o comunicado
const enviarComunicado = async () => {
  if (comunicado.value.length === 0 || comunicado.value.length > 255) {
    toast.error('A mensagem deve ter entre 1 e 255 caracteres.');
    return;
  }

  try {
    await axios.post('/api/notificacoes/setor', {
      mensagem: comunicado.value,
      setor_id: props.informacoes.id,
    });
    toast.success('Comunicado enviado com sucesso!');
    comunicado.value = '';
  } catch (error) {
    console.error('Erro ao enviar comunicado:', error);
    toast.error('Erro ao enviar comunicado.');
  }
};

</script>

<style scoped></style>
