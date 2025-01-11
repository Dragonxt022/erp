<template>
  <Link
    v-if="!isLogout"
    class="menu-item"
    :class="{ active: isActive }"
    :href="link"
  >
    <div class="icon">
      <img :src="icon" alt="icon" />
    </div>
    <div class="label">{{ label }}</div>
  </Link>

  <!-- Caso seja logout, trata com POST -->
  <form v-else @submit.prevent="handleLogout" class="menu-item">
    <button type="submit" class="w-full h-full flex items-center">
      <div class="icon">
        <img :src="icon" alt="icon" />
      </div>
      <div class="label">{{ label }}</div>
    </button>
  </form>
</template>

<script setup>
import { defineProps } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  label: String,
  icon: String,
  link: String, // URL ou rota gerada com Inertia
  isActive: Boolean, // Define o estado ativo
  isLogout: Boolean, // Define se o link é para logout
});

const handleLogout = () => {
  if (props.isLogout) {
    router.post(route('logout')); // Envia a requisição POST para a rota de logout
  }
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
  font-size: 15px;
  font-family: Figtree;
  font-weight: 600;
  line-height: 22px;
  word-wrap: break-word;
}

.menu-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
}
</style>
