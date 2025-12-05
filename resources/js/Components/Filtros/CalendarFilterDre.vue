<template>
    <div class="relative flex items-center space-x-2">
        <!-- Botão de mês anterior -->
        <button @click="prevMonth" class="text-indigo-600 hover:underline">
            <img src="/storage/images/arrow_drop_down_circle.svg" alt="icone drop" class="w-5 h-5" />
        </button>

        <!-- Mês e ano (clicável) -->
        <div class="relative">
            <span 
                @click="togglePicker" 
                class="font-semibold text-gray-900 text-[17px] cursor-pointer hover:text-[#6db631] transition-colors"
            >
                {{ currentDate.format('MMMM YYYY') }}
            </span>

            <!-- Dropdown para seleção de mês e ano -->
            <div 
                v-if="showPicker" 
                class="absolute right-[-33%] z-50 bg-white border rounded-lg shadow-lg p-4 mt-2 min-w-[250px]"
                @click.stop
            >
                <div class="space-y-3">
                    <!-- Seletor de Mês -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mês</label>
                        <select 
                            v-model="selectedMonth" 
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#6db631]"
                        >
                            <option v-for="(month, index) in months" :key="index" :value="index">
                                {{ month }}
                            </option>
                        </select>
                    </div>

                    <!-- Seletor de Ano -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
                        <select 
                            v-model="selectedYear" 
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#6db631]"
                        >
                            <option v-for="year in years" :key="year" :value="year">
                                {{ year }}
                            </option>
                        </select>
                    </div>

                    <!-- Botões -->
                    <div class="flex justify-end gap-2 pt-2">
                        <button 
                            @click="cancelPicker" 
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium"
                        >
                            Cancelar
                        </button>
                        <button 
                            @click="applyPicker" 
                            class="px-4 py-2 text-sm bg-[#6db631] text-white rounded-md hover:bg-[#5a9929] font-medium"
                        >
                            Aplicar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão de próximo mês -->
        <button @click="nextMonth" class="text-indigo-600 hover:underline">
            <img src="/storage/images/arrow_drop_direita_circle.svg" alt="icone drop" class="w-5 h-5" />
        </button>

    </div>
</template>

<script setup>
import { ref, defineEmits, onMounted, computed } from 'vue';
import dayjs from 'dayjs';

// Define locale para português se necessário
import 'dayjs/locale/pt-br';
dayjs.locale('pt-br');

const emit = defineEmits(['update-filters']);

// Data atual para navegação (começa no mês corrente)
const currentDate = ref(dayjs());

// Estado do picker
const showPicker = ref(false);
const selectedMonth = ref(dayjs().month());
const selectedYear = ref(dayjs().year());

// Lista de meses em português
const months = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
];

// Gera lista de anos (10 anos atrás até 2 anos à frente)
const years = computed(() => {
    const currentYear = dayjs().year();
    const yearList = [];
    for (let i = currentYear - 10; i <= currentYear + 2; i++) {
        yearList.push(i);
    }
    return yearList;
});

// Funções para navegar entre os meses
const prevMonth = () => {
    currentDate.value = currentDate.value.subtract(1, 'month');
    emitDates();
};

const nextMonth = () => {
    currentDate.value = currentDate.value.add(1, 'month');
    emitDates();
};

// Funções do picker
const togglePicker = () => {
    showPicker.value = !showPicker.value;
    if (showPicker.value) {
        selectedMonth.value = currentDate.value.month();
        selectedYear.value = currentDate.value.year();
    }
};

const cancelPicker = () => {
    showPicker.value = false;
};

const applyPicker = () => {
    currentDate.value = dayjs().year(selectedYear.value).month(selectedMonth.value);
    showPicker.value = false;
    emitDates();
};

// Função para emitir as datas inicial e final do mês
const emitDates = () => {
    const startDate = currentDate.value.startOf('month').format('DD-MM-YYYY');
    const endDate = currentDate.value.endOf('month').format('DD-MM-YYYY');

    emit('update-filters', {
        startDate,
        endDate,
    });
};

// Emite as datas iniciais assim que o componente é montado
onMounted(() => {
    emitDates();
});

// Fecha o picker ao clicar fora
const handleClickOutside = (event) => {
    if (showPicker.value && !event.target.closest('.relative')) {
        showPicker.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});
</script>
