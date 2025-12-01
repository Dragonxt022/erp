<template>
  <div class="input-container">
    <label v-if="label" :for="id" class="input-label">
      {{ label }}
    </label>
    <input
      :type="type"
      :id="id"
      v-bind="attrs"
      v-model="modelValue"
      class="input"
    />
    <p v-if="errorMessage" class="input-error">{{ errorMessage }}</p>
  </div>
</template>

<script setup>
import { computed, defineProps, toRefs } from 'vue';

const props = defineProps({
  modelValue: [String, Number], // Valor do input
  type: {
    type: String,
    default: 'text',
  },
  id: {
    type: String,
    default: null,
  },
  label: {
    type: String,
    default: null,
  },
  errorMessage: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue']);

const attrs = computed(() =>
  Object.fromEntries(
    Object.entries(toRefs(props)).filter(
      ([key]) => !['modelValue', 'label', 'errorMessage'].includes(key)
    )
  )
);
</script>

<style scoped>
.input-container {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.input-label {
  font-size: 14px;
  color: #333;
  font-weight: 600;
}
.input {
  padding: 8px 12px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.input-error {
  font-size: 12px;
  color: #ff4d4f;
}
</style>
