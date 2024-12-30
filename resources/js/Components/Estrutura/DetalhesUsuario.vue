<template>
  <div
    v-if="!isEditMode"
    class="w-full h-[350px] bg-white rounded-[20px] p-12 relative"
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
          <div
            class="absolute top-4 right-4 cursor-pointer"
            @click="deleteUsuario"
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
        <p class="p-3">E-mail</p>
        <div class="flex items-center bg-[#f3f8f3] p-4 rounded-lg">
          <div
            class="text-[#262a27] text-base font-semibold font-['Figtree'] leading-[13px] tracking-tight"
          >
            {{ usuario.email || 'N/A' }}
          </div>
        </div>
      </div>

      <!-- Botão de Edição -->
      <div v-if="usuario.id" class="absolute bottom-0 right-0">
        <ButtonEditeMedio
          text="Editar Usuário"
          icon-path="/storage/images/border_color.svg"
          @click="toggleEditMode"
          class="px-4 py-2 bg-[#F8F8F8] text-white rounded-lg"
        />
      </div>
    </div>
  </div>

  <!-- Exibe o formulário de edição quando isEditMode é true -->
  <EditarUnidade
    v-if="isEditMode"
    :isVisible="isEditMode"
    :unidade="usuario"
    @cancelar="cancelEdit"
  />
</template>

<script setup>
import { defineProps, ref } from 'vue';
import EditarUnidade from './EditarUnidade.vue';
import ButtonEditeMedio from '../Button/ButtonEditeMedio.vue';

const props = defineProps({
  usuario: {
    type: Object,
    required: true,
  },
});

const isEditMode = ref(false);

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value;
};

const cancelEdit = () => {
  isEditMode.value = false;
};
</script>

<style scoped>
.owner {
  font-size: 13px;
  font-family: Figtree;
  font-weight: 500;
  line-height: 18px;
  color: #6d6d6e;
}
</style>
