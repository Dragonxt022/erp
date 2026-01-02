<template>
  <div class="relative inline-block w-full">
    <button
      @click="toggleDropdown"
      class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2 text-left flex items-center justify-between hover:border-gray-400 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500"
    >
      <div class="flex items-center gap-2">
        <img src="/storage/images/storefrontb.svg" alt="Unidade" class="w-5 h-5" />
        <span class="text-gray-900 text-[15px] font-semibold">
          {{ selectedUnitName }}
        </span>
      </div>
      <svg
        class="w-5 h-5 text-gray-500 transition-transform"
        :class="{ 'rotate-180': isOpen }"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M19 9l-7 7-7-7"
        />
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
      >
        <!-- Opção "Todas as Unidades" -->
        <button
          @click="selectUnit(null)"
          class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-center gap-3"
          :class="{
            'bg-green-50 text-green-700': selectedUnitId === null,
            'text-gray-700': selectedUnitId !== null,
          }"
        >
          <div
            class="w-2 h-2 rounded-full"
            :class="{
              'bg-green-500': selectedUnitId === null,
              'bg-gray-300': selectedUnitId !== null,
            }"
          ></div>
          <div>
            <p class="font-semibold text-[15px]">Todas as Unidades</p>
            <p class="text-xs text-gray-500">Dados agregados</p>
          </div>
        </button>

        <div class="border-t border-gray-200"></div>

        <!-- Lista de Unidades -->
        <button
          v-for="unit in units"
          :key="unit.id"
          @click="selectUnit(unit.id)"
          class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-center gap-3"
          :class="{
            'bg-green-50 text-green-700': selectedUnitId === unit.id,
            'text-gray-700': selectedUnitId !== unit.id,
          }"
        >
          <div
            class="w-2 h-2 rounded-full"
            :class="{
              'bg-green-500': selectedUnitId === unit.id,
              'bg-gray-300': selectedUnitId !== unit.id,
            }"
          ></div>
          <div>
            <p class="font-semibold text-[15px]">{{ unit.cidade }}</p>
            <p class="text-xs text-gray-500">
              ID: {{ String(unit.id).padStart(4, '0') }}
            </p>
          </div>
        </button>

        <!-- Estado vazio -->
        <div
          v-if="units.length === 0 && !loading"
          class="px-4 py-6 text-center text-gray-500 text-sm"
        >
          Nenhuma unidade encontrada
        </div>

        <!-- Loading -->
        <div
          v-if="loading"
          class="px-4 py-6 text-center text-gray-500 text-sm"
        >
          Carregando unidades...
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  defaultUnitId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['unit-selected']);

const isOpen = ref(false);
const units = ref([]);
const selectedUnitId = ref(props.defaultUnitId);
const loading = ref(false);

const selectedUnitName = computed(() => {
  if (selectedUnitId.value === null) {
    return 'Todas as Unidades';
  }
  const unit = units.value.find((u) => u.id === selectedUnitId.value);
  return unit ? unit.cidade : 'Selecione uma unidade';
});

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const selectUnit = (unitId) => {
  selectedUnitId.value = unitId;
  isOpen.value = false;
  emit('unit-selected', unitId);
};

const fetchUnits = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/painel/unidades');
    units.value = response.data.unidades || [];
  } catch (error) {
    console.error('Erro ao buscar unidades:', error);
  } finally {
    loading.value = false;
  }
};

// Fechar dropdown ao clicar fora
const handleClickOutside = (event) => {
  const dropdown = event.target.closest('.relative');
  if (!dropdown) {
    isOpen.value = false;
  }
};

onMounted(() => {
  fetchUnits();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Esconder scrollbar mas manter funcionalidade */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
