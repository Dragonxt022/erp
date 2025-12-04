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
      <!-- Navegação do mês e ano -->
      <div class="flex items-center justify-between mb-3">
        <button @click="previousMonth" class="flex items-center justify-between mb-3">
            <img src="/storage/images/arrow_drop_down_circle.svg" alt="icone drop" class="w-5 h-5" />
        </button>
        <span class="font-semibold">
          {{ currentDate.format('MMMM YYYY') }}
        </span>
        <button @click="nextMonth" class="text-indigo-600 hover:underline">
            <img src="/storage/images/arrow_drop_direita_circle.svg" alt="icone drop" class="w-5 h-5" />
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
            day.day === '' ? 'invisible' : '',
            isSelected(day.date)
              ? 'bg-[#6db631] text-white'
              : isInRange(day.date)
              ? 'bg-[#b3dca6]'
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
import 'dayjs/locale/pt-br';
dayjs.locale('pt-br');

const emit = defineEmits(['update-filters']);

// Estado para o dropdown
const isDropdownOpen = ref(false);

// Variáveis reativas para controlar as datas de início e fim
// Padrão: uma semana (7 dias atrás até hoje)
const selectedStartDate = ref(dayjs().subtract(7, 'day').startOf('day'));
const selectedEndDate = ref(dayjs().endOf('day'));
const currentDate = ref(dayjs()); // Data atual para navegação no calendário

// Controle de seleção de datas (início e fim)
const selectingStart = ref(true); // Controla se estamos escolhendo a data de início ou fim

// Dados para o calendário
const daysOfWeek = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];

const daysInMonth = computed(() => {
  const days = [];
  const startOfMonth = currentDate.value.startOf('month');
  const endOfMonth = currentDate.value.endOf('month');
  const daysInMonthCount = endOfMonth.date();

  // Adiciona espaços vazios antes do primeiro dia do mês
  const firstDayOfMonth = startOfMonth.day();
  for (let i = 0; i < firstDayOfMonth; i++) {
    days.push({ day: '', date: null });
  }

  // Adiciona os dias do mês
  for (let day = 1; day <= daysInMonthCount; day++) {
    const date = startOfMonth.date(day);
    days.push({ day, date });
  }

  return days;
});

// Funções para navegar entre meses
const previousMonth = () => {
  currentDate.value = currentDate.value.subtract(1, 'month');
};

const nextMonth = () => {
  currentDate.value = currentDate.value.add(1, 'month');
};

// Verifica se o dia está selecionado
const isSelected = (date) => {
  if (!date) return false;
  return (
    date.isSame(selectedStartDate.value, 'day') ||
    date.isSame(selectedEndDate.value, 'day')
  );
};

// Verifica se o dia está dentro do intervalo selecionado
const isInRange = (date) => {
  if (!date || !selectedStartDate.value || !selectedEndDate.value) return false;
  return (
    date.isAfter(selectedStartDate.value, 'day') &&
    date.isBefore(selectedEndDate.value, 'day')
  );
};

// Função para selecionar uma data
const selectDate = (date) => {
  if (!date) return;

  if (selectingStart.value) {
    // Se estamos selecionando o início, definimos a data de início
    selectedStartDate.value = date.startOf('day');
    selectingStart.value = false; // Agora vamos selecionar a data final
  } else {
    // Se já selecionamos o início, a data atual será o final
    selectedEndDate.value = date.endOf('day');
    // Garantir que a data final não seja anterior à inicial
    if (selectedEndDate.value.isBefore(selectedStartDate.value)) {
      const temp = selectedStartDate.value;
      selectedStartDate.value = selectedEndDate.value;
      selectedEndDate.value = temp;
    }
    selectingStart.value = true; // Reset para iniciar a seleção novamente
  }
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
  emit('update-filters', {
    startDate,
    endDate,
  });

  isDropdownOpen.value = false; // Fecha o dropdown após aplicar os filtros
};

// Função para limpar os filtros
const clearFilters = () => {
  selectedStartDate.value = dayjs().subtract(7, 'day').startOf('day');
  selectedEndDate.value = dayjs().endOf('day');
  currentDate.value = dayjs();
  selectingStart.value = true;
};

// Função para cancelar
const cancelar = () => {
  clearFilters();
  isDropdownOpen.value = false;
};
</script>
