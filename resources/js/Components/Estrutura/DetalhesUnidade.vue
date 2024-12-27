<template>
  <div v-if="!isEditMode" class="w-full h-[350px] bg-white rounded-[20px] p-12">
    <!-- Exibe informações da unidade apenas quando não está no modo de edição -->
    <div class="relative w-full h-full">
      <!-- Exibe o ID e CNPJ -->
      <div
        class="flex justify-between items-center absolute left-0 top-[50px] w-full"
      >
        <div
          class="w-[135px] h-[43px] px-2.5 py-3 bg-[#f3f8f3] rounded-lg flex justify-between items-center"
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
            ID# {{ (unidade.unidade?.id ?? 0).toString().padStart(4, '0') }}
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
            {{ unidade.unidade?.cnpj || 'N/A' }}
          </div>
        </div>
      </div>

      <!-- Nome da Unidade -->
      <div
        class="absolute left-0 top-0 text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[34px] tracking-tight"
      >
        {{ unidade.unidade?.cidade || unidade.cidade || 'N/A' }}
      </div>

      <!-- Propriedade da Unidade -->
      <div
        class="absolute bottom-0 left-0 w-full h-[100px] flex flex-col gap-1"
      >
        <div
          class="text-[#6d6d6d] text-[15px] font-semibold font-['Figtree'] leading-tight"
        >
          Esta unidade pertence a
        </div>
        <div
          class="h-[43px] pl-4 pr-3 py-3 bg-[#f3f8f3] rounded-lg flex justify-between items-center"
        >
          <!-- Itera sobre os usuários e exibe o nome -->
          <div
            class="text-[#6db631] text-base font-semibold font-['Figtree'] leading-[13px] tracking-tight"
          >
            <template v-if="unidade?.usuarios?.length">
              <div v-for="usuario in unidade.usuarios" :key="usuario.id">
                {{ usuario.name }}
              </div>
            </template>
            <template v-else>Sem informações</template>
          </div>
        </div>

        <!-- Exibe o botão de edição apenas se uma unidade for selecionada -->
        <div v-if="unidade.unidade.id" class="mt-4">
          <ButtonEditeMedio
            text="Editar Unidade"
            icon-path="/storage/images/border_color.svg"
            @click="toggleEditMode"
            class="px-4 py-2 bg-[#F8F8F8] text-white rounded-lg"
          />
        </div>
      </div>
    </div>
  </div>

  <!-- Exibe o formulário de edição quando isEditMode é true -->
  <EditarUnidade
    v-if="isEditMode"
    ref="dadosUnidade"
    :isVisible="isEditMode"
    :unidade="unidade"
    @dadosUnidade="fetchUnidades"
    @cancelar="cancelEdit"
  />
</template>

<script setup>
import { defineProps, ref } from 'vue';
import EditarUnidade from './EditarUnidade.vue';
import ButtonEditeMedio from '../Button/ButtonEditeMedio.vue';

const props = defineProps({
  unidade: {
    type: Object,
    required: true, // A unidade precisa ser passada
  },
});

const showCadastroUnidade = ref(false);

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
