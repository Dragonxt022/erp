<template>
  <AppLayout>
    <Head title="Comunidade" />
    <div class="painel-container">
      <div class="painel-content">
        <div class="painel-title">Comunidade</div>
        <div class="painel-subtitle">
          <p>Acompanhe seu negócio em tempo real</p>
          <p>
            sair da aplicação
            <span class="cursor-pointer" @click="logout">
              <Link :href="route('logout')" class="cursor-pointer" />
            </span>
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const logout = () => {
  router.post(route('logout'));
};
</script>

<style lang="css" scoped>
.painel-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 55%;
  height: 120px;
}
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
  line-height: 41px;
}
.painel-subtitle {
  font-size: 17px;
  color: #6d6d6e;
}
</style>
