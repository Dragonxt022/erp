<template>
  <div v-if="!isEditMode" class="w-full h-[350px] bg-white rounded-[20px] p-12">
    <!-- Exibe informações da unidade apenas quando não está no modo de edição -->
    <div class="relative w-full h-full">
      <!-- Exibe o ID e CNPJ -->
      <div class="flex justify-between items-center absolute left-0 top-[50px] w-full">
        <div class="w-[135px] h-[43px] px-2.5 py-3 bg-[#f3f8f3] rounded-lg flex justify-between items-center">
          <div class="w-6 h-6 rounded-full">
            <img src="/storage/images/storefront-bleck.svg" alt="" class="inline-block" />
          </div>
          <div class="text-[#262a27] text-base font-semibold font-['Figtree'] leading-[13px] tracking-tight">
            ID# {{ (unidade.unidade?.id ?? 0).toString().padStart(4, '0') }}
          </div>
        </div>
        <div class="w-[219px] h-[43px] px-2.5 py-3 bg-[#f3f8f3] rounded-lg flex justify-between items-center">
          <div class="w-6 h-6 rounded-full">
            <img src="/storage/images/assured_workload.svg" alt="" class="inline-block" />
          </div>
          <div class="text-[#262a27] text-base font-semibold font-['Figtree'] leading-[13px] tracking-tight">
            {{ unidade.unidade?.cnpj || 'N/A' }}
          </div>
        </div>
      </div>

      <!-- Nome da Unidade -->
      <div
        class="absolute left-0 top-0 text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[34px] tracking-tight">
        {{ unidade.unidade?.cidade || unidade.cidade || 'N/A' }}
      </div>

      <!-- Propriedade da Unidade -->
      <div class="absolute bottom-0 left-0 w-full min-h-[100px] flex flex-col gap-1">
        <div class="text-[#6d6d6d] text-[15px] font-semibold font-['Figtree'] leading-tight">
          Colaboradores nesta unidade
        </div>

        <div class="pl-4 pr-3 py-3 bg-[#f3f8f3] rounded-lg">
          <!-- Título com botão -->
          <div class="flex justify-between items-center mb-1">
            <div class="text-[#6db631] text-base font-semibold font-['Figtree'] leading-[13px] tracking-tight">
              {{ unidade?.quantidade_usuarios || 0 }} usuário(s)
            </div>
            <button @click="expandirUsuarios = true" class="text-[#6db631] text-sm font-medium underline">
              Mostrar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Exibe o formulário de edição quando isEditMode é true -->
  <div v-if="unidade.unidade.id && !isEditMode" class="mt-4">
    <ButtonEditeMedio text="Editar Unidade" icon-path="/storage/images/border_color.svg" @click="toggleEditMode"
      class="px-4 py-2 bg-[#F8F8F8] text-white rounded-lg" />
  </div>
  <EditarUnidade v-if="isEditMode" ref="dadosUnidade" :isVisible="isEditMode" :unidade="unidade"
    @dadosUnidade="fetchUnidades" @cancelar="cancelEdit" />

  <ModalUsuariosUnidade v-if="expandirUsuarios" :usuarios="unidade.usuarios" @close="expandirUsuarios = false" />

</template>

<script setup>
import { defineProps, ref } from 'vue';
import ButtonEditeMedio from '../Button/ButtonEditeMedio.vue';
import EditarUnidade from './EditarUnidade.vue';
import ModalUsuariosUnidade from './ModalUsuariosUnidade.vue';

const props = defineProps({
  unidade: {
    type: Object,
    required: true,
  },
});

const showCadastroUnidade = ref(false);
const expandirUsuarios = ref(false)

const isEditMode = ref(false);

const fetchUnidades = () => {
  const dadosUnidade = ref.dadosUnidade;
  dadosUnidade.fetchUnidades();
};

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value;
  showCadastroUnidade.value = false;
};

// Função de cancelamento que será emitida pelo componente de edição
const cancelEdit = () => {
  isEditMode.value = false;
  showCadastroUnidade.value = true;
};
</script>

<style scoped></style>
