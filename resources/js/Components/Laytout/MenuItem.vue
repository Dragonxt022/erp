<template>
  <div
    class="menu-item"
    :class="['menu-item', { active: isActive }]"
    @click="handleClick"
  >
    <div class="icon">
      <img :src="icon" alt="icon" />
    </div>
    <div class="label">{{ label }}</div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  label: String,
  icon: String,
  link: String,
  isActive: Boolean,
});

const emit = defineEmits();

const handleClick = () => {
  // Emite o evento para o componente pai se necessário
  emit('onActivate', props.link);
};
</script>

<style scoped>
.menu-item {
  display: flex;
  align-items: center;
  height: 44px;
  padding-left: 14px;
  padding-right: 14px;
  cursor: pointer;
  border-radius: 10px;
  margin-bottom: 10px;
  user-select: none;
  transition: background-color 0.3s;
}

.menu-item.active {
  background-color: #568f40;
}

.menu-item .icon {
  width: 24px;
  height: 24px;
  margin-right: 10px;
}

.menu-item .label {
  color: white;
  font-size: 17px;
  font-family: Figtree;
  font-weight: 600;
  line-height: 22px;
  word-wrap: break-word;
}

.menu-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
}
</style>
