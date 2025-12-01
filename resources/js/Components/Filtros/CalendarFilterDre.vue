<template>
    <div class="flex items-center space-x-2">
        <!-- Botão de mês anterior -->
        <button @click="prevMonth" class="text-indigo-600 hover:underline">
            <img src="/storage/images/arrow_drop_down_circle.svg" alt="icone drop" class="w-5 h-5" />
        </button>

        <!-- Mês e ano -->
        <span class="font-semibold text-gray-900 text-[17px]">
            {{ currentDate.format('MMMM YYYY') }}
        </span>

        <!-- Botão de próximo mês -->
        <button @click="nextMonth" class="text-indigo-600 hover:underline">
            <img src="/storage/images/arrow_drop_direita_circle.svg" alt="icone drop" class="w-5 h-5" />
        </button>

    </div>
</template>

<script setup>
import { ref, defineEmits, onMounted } from 'vue';
import dayjs from 'dayjs';

// Define locale para português se necessário
import 'dayjs/locale/pt-br';
dayjs.locale('pt-br');

const emit = defineEmits(['update-filters']);

// Data atual para navegação (começa no mês corrente)
const currentDate = ref(dayjs());

// Funções para navegar entre os meses
const prevMonth = () => {
    // Subtrai um mês
    currentDate.value = currentDate.value.subtract(1, 'month');
    emitDates(); // Emite as datas imediatamente após a mudança
};

const nextMonth = () => {
    // Adiciona um mês
    currentDate.value = currentDate.value.add(1, 'month');
    emitDates(); // Emite as datas imediatamente após a mudança
};

// Função para emitir as datas inicial e final do mês
const emitDates = () => {
    // Calcula a data inicial (primeiro dia do mês) e a data final (último dia do mês)
    const startDate = currentDate.value.startOf('month').format('DD-MM-YYYY');
    const endDate = currentDate.value.endOf('month').format('DD-MM-YYYY');

    // Emite o evento com as datas inicial e final
    emit('update-filters', {
        startDate,
        endDate,
    });
};

// Emite as datas iniciais assim que o componente é montado
onMounted(() => {
    emitDates();
});
</script>
