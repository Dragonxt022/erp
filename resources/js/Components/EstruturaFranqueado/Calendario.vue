<template>
  <div class="p-4 bg-gray-800 text-white rounded-lg w-64">
    <header class="flex justify-between items-center mb-2">
      <button @click="prevMonth" class="text-sm font-bold">&lt;</button>
      <span class="text-lg font-semibold">
        {{ currentMonth }} {{ currentYear }}
      </span>
      <button @click="nextMonth" class="text-sm font-bold">&gt;</button>
    </header>

    <div class="grid grid-cols-7 gap-1">
      <div
        v-for="day in daysOfWeek"
        :key="day"
        class="text-center text-sm font-bold"
      >
        {{ day }}
      </div>

      <div
        v-for="date in dates"
        :key="date"
        :class="[
          'text-center py-2 rounded-md cursor-pointer',
          isInRange(date) ? 'bg-green-500' : 'bg-gray-700',
          date === selectedStart ? 'border-2 border-green-700' : '',
        ]"
        @click="selectDate(date)"
      >
        {{ date.getDate() }}
      </div>
    </div>

    <footer class="flex justify-between mt-4">
      <button @click="clearSelection" class="text-sm text-gray-400">
        Limpar
      </button>
      <button class="text-sm text-green-500">OK</button>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const daysOfWeek = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];

const currentDate = new Date();
const currentMonth = ref(
  currentDate.toLocaleString('default', { month: 'short' })
);
const currentYear = ref(currentDate.getFullYear());

const selectedStart = ref(null);
const selectedEnd = ref(null);

const dates = computed(() => {
  const start = new Date(currentYear.value, currentDate.getMonth(), 1);
  const end = new Date(currentYear.value, currentDate.getMonth() + 1, 0);
  const datesArray = [];

  for (let d = start; d <= end; d.setDate(d.getDate() + 1)) {
    datesArray.push(new Date(d));
  }

  return datesArray;
});

const isInRange = (date) => {
  if (!selectedStart.value || !selectedEnd.value) return false;
  return date >= selectedStart.value && date <= selectedEnd.value;
};

const selectDate = (date) => {
  if (!selectedStart.value || selectedEnd.value) {
    selectedStart.value = date;
    selectedEnd.value = null;
  } else if (date > selectedStart.value) {
    selectedEnd.value = date;
  }
};

const clearSelection = () => {
  selectedStart.value = null;
  selectedEnd.value = null;
};

const prevMonth = () => {
  currentDate.setMonth(currentDate.getMonth() - 1);
  currentMonth.value = currentDate.toLocaleString('default', {
    month: 'short',
  });
};

const nextMonth = () => {
  currentDate.setMonth(currentDate.getMonth() + 1);
  currentMonth.value = currentDate.toLocaleString('default', {
    month: 'short',
  });
};
</script>

<style scoped></style>
