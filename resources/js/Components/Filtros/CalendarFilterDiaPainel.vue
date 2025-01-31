<template>
  <div class="relative">
    <!-- Exibição do intervalo de datas selecionadas -->
    <div
      class="flex items-center justify-between p-3 rounded-md cursor-pointer"
      @click="toggleDropdown"
    >
      <span>
        <span v-if="selectedStartDate && selectedEndDate">
          {{ selectedStartDate.format('DD/MM/YYYY') }} -
          {{ selectedEndDate.format('DD/MM/YYYY') }}
        </span>
      </span>
    </div>

    <!-- Dropdown do calendário -->
    <div
      v-if="isDropdownOpen"
      class="absolute z-10 bg-white border rounded-xl shadow-lg w-[300px] p-4 left-[68px] transform -translate-x-1/2"
    >
      <!-- Navegação do mês -->
      <div class="flex justify-between items-center mb-3">
        <button @click="prevMonth" class="p-2 rounded hover:bg-gray-300">
          &lt;
        </button>
        <span class="font-semibold flex items-center">
          <span>{{ currentDate.format('MMM') }}</span>
          <span class="ml-[90px]">{{ currentDate.format('YYYY') }}</span>
        </span>
        <button @click="nextMonth" class="p-2 rounded hover:bg-gray-300">
          &gt;
        </button>
      </div>

      <!-- Grid de dias do calendário -->
      <div class="grid grid-cols-7 gap-1 text-center text-sm">
        <div
          class="font-semibold text-gray-600"
          v-for="day in daysOfWeek"
          :key="day"
        >
          {{ day }}
        </div>
        <div
          v-for="(day, index) in daysInMonth"
          :key="index"
          @click="selectDate(day.date)"
          :class="[
            'p-2 rounded-full cursor-pointer',
            isSelected(day.date)
              ? 'bg-[#6db631] text-white' // Marca o dia inicial e final
              : isInRange(day.date)
              ? 'bg-[#b3dca6]' // Marca os dias entre o intervalo
              : 'hover:bg-gray-200',
          ]"
        >
          {{ day.day }}
        </div>
      </div>

      <!-- Botões do rodapé -->
      <div class="w-full mt-4 flex justify-between items-center">
        <!-- Botão Limpar -->
        <div
          class="h-10 rounded-full flex justify-center items-center px-3 py-2.5 cursor-pointer text-[#6db631] text-[15px] font-semibold font-['Figtree'] leading-tight"
          @click="clearFilters"
        >
          Limpar
        </div>

        <!-- Botões Cancelar e OK -->
        <div class="flex gap-2">
          <div
            class="h-10 rounded-full flex justify-center items-center px-3 py-2.5 cursor-pointer text-[#6db631] text-[15px] font-semibold font-['Figtree'] leading-tight"
            @click="cancelar"
          >
            Cancelar
          </div>
          <div
            class="h-10 rounded-full flex justify-center items-center px-3 py-2.5 cursor-pointer text-[#6db631] text-[15px] font-semibold font-['Figtree'] leading-tight"
            @click="applyFilters"
          >
            OK
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineEmits } from 'vue';
import dayjs from 'dayjs';

const emit = defineEmits();

// Estado para o dropdown
const isDropdownOpen = ref(false);

// Variáveis reativas para controlar as datas de início e fim
const selectedStartDate = ref(dayjs().startOf('month')); // Início: primeiro dia do mês
const selectedEndDate = ref(dayjs().endOf('month')); // Fim: último dia do mês

const currentDate = ref(dayjs());

// Dados para o calendário
const daysOfWeek = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];

const daysInMonth = computed(() => {
  const days = [];
  const startOfMonth = currentDate.value.startOf('month');
  const endOfMonth = currentDate.value.endOf('month');

  for (let i = 0; i < startOfMonth.day(); i++) {
    days.push({ day: '', date: null }); // Espaços vazios antes do primeiro dia
  }

  for (let day = 1; day <= endOfMonth.date(); day++) {
    const date = startOfMonth.date(day);
    days.push({ day, date });
  }

  return days;
});

const isSelected = (date) => {
  return (
    date &&
    (date.isSame(selectedStartDate.value) || date.isSame(selectedEndDate.value))
  );
};

// Função corrigida para verificar o intervalo
const isInRange = (date) => {
  if (!date || !selectedStartDate.value || !selectedEndDate.value) return false;

  // Incluir o dia de início e o dia de fim no intervalo
  return (
    (date.isAfter(selectedStartDate.value, 'day') &&
      date.isBefore(selectedEndDate.value, 'day')) ||
    date.isSame(selectedStartDate.value, 'day') ||
    date.isSame(selectedEndDate.value, 'day')
  );
};

const selectDate = (date) => {
  if (!date) return;

  if (selectedStartDate.value.isAfter(selectedEndDate.value)) {
    selectedStartDate.value = date.startOf('day'); // Define a data de início
  } else {
    selectedEndDate.value = date.endOf('day'); // Define a data de fim
  }
};

const cancelar = () => {
  clearFilters();
  isDropdownOpen.value = false;
};

// Funções para o dropdown
const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

// Função para aplicar os filtros
const applyFilters = () => {
  const startDate = selectedStartDate.value.format('DD-MM-YYYY');
  const endDate = selectedEndDate.value.format('DD-MM-YYYY');

  console.log('Data inicial:', startDate);
  console.log('Data final:', endDate);

  // Emitindo o evento com as datas
  emit('update-filters', { startDate, endDate });

  isDropdownOpen.value = false; // Fecha o dropdown após aplicar os filtros
};

// Função para limpar os filtros
const clearFilters = () => {
  selectedStartDate.value = dayjs().startOf('');
  selectedEndDate.value = dayjs().endOf('');
};
</script>
