<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div
        class="bg-white rounded-xl w-full max-w-lg max-h-[90vh] p-6 shadow-lg relative overflow-hidden"
      >
        <h2 class="text-xl font-bold text-[#262a27] mb-4">Colaboradores nesta unidade</h2>
  
        <!-- Lista com scroll invisível -->
        <div class="space-y-4 overflow-y-auto pr-2 max-h-[60vh]" style="scrollbar-width: none; -ms-overflow-style: none;">
          <div
            v-for="usuario in usuarios"
            :key="usuario.id"
            class="flex items-center gap-4 border-b pb-3"
          >
            <img
              :src="usuario.profile_photo_url"
              alt="Foto do usuário"
              class="w-12 h-12 rounded-full object-cover"
            />
            <div class="flex flex-col">
              <span class="text-[#262a27] font-semibold">{{ usuario.name }}</span>
              <span class="text-sm text-gray-500">{{ usuario.cargo?.nome || 'Cargo não informado' }}</span>
            </div>
          </div>
          <p v-if="!usuarios.length" class="text-sm text-gray-500">Sem informações</p>
        </div>
  
        <!-- Botão de fechar -->
        <button
          @click="$emit('close')"
          class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-xl"
        >
          &times;
        </button>
      </div>
    </div>
  </template>
  
  <script setup>
  defineProps({
    usuarios: {
      type: Array,
      required: true,
    },
  });
  </script>
  
  <style scoped>
  /* Oculta scrollbar apenas visualmente, mantendo funcionalidade */
  div::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }
  </style>
  