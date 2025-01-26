<template>
  <div class="relative flex items-center w-full">
    <!-- Input de texto -->
    <input
      :value="formatadoValor"
      @input="onInputChange"
      :placeholder="placeholder"
      class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
    />
  </div>
</template>

<script setup>
import { defineProps, ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    disabled: Boolean,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
});

const formatadoValor = ref('');

// Computed para garantir que a formatação do valor seja mantida
watch(
  () => props.modelValue,
  (newVal) => {
    // Aplica a formatação assim que o valor for alterado
    let valor = newVal.toString().replace(/\D/g, '');
    let inteiro = valor.slice(0, -2);
    let centavos = valor.slice(-2).padStart(2, '0');
    inteiro = inteiro.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    formatadoValor.value = `R$ ${inteiro},${centavos}`;
  },
  { immediate: true }
);

const onInputChange = (event) => {
  let valor = event.target.value.replace(/\D/g, '');
  let inteiro = valor.slice(0, -2);
  let centavos = valor.slice(-2).padStart(2, '0');
  props.$emit('update:modelValue', parseFloat(`${inteiro}.${centavos}`));
};
</script>
