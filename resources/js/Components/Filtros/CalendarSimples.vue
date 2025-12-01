<template>
  <div class="relative">
    <div class="flex items-center justify-center rounded-md">
      <span class="font-semibold text-lg">
        {{ currentDate.format('MMMM YYYY') }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import dayjs from 'dayjs';
import 'dayjs/locale/pt-br';

dayjs.locale('pt-br');
const currentDate = ref(dayjs());
</script>
