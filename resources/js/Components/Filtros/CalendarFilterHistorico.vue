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

// Locale PT-BR
import 'dayjs/locale/pt-br';
dayjs.locale('pt-br');

const emit = defineEmits(['update-filters']);

// 🔹 Padrão: mês anterior ao atual
const currentDate = ref(dayjs().subtract(1, 'month'));

const prevMonth = () => {
    currentDate.value = currentDate.value.subtract(1, 'month');
    emitDates();
};

const nextMonth = () => {
    // 🔹 Se não quiser permitir avançar pro mês atual, coloca um bloqueio aqui:
    if (currentDate.value.add(1, 'month').isBefore(dayjs().startOf('month'))) {
        currentDate.value = currentDate.value.add(1, 'month');
        emitDates();
    }
};

const emitDates = () => {
    const startDate = currentDate.value.startOf('month').format('DD-MM-YYYY');
    const endDate   = currentDate.value.endOf('month').format('DD-MM-YYYY');

    emit('update-filters', { startDate, endDate });
};

onMounted(() => {
    emitDates();
});
</script>
