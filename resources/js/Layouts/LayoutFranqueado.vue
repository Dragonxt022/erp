<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Navbar from '@/Components/LaytoutFranqueado/Navbar.vue';
import Slidebar from '@/Components/LaytoutFranqueado/Slidebar.vue';

import { useSidebarStore } from '@/stores/sidebar';
const sidebarStore = useSidebarStore();

const toggleSidebar = () => {
    sidebarStore.toggle();
};

defineProps({
    title: String,
});

const logout = () => {
    router.post(route('logout'));
};



// Função para fechar sidebar automaticamente no mobile
function handleResize() {
  if (window.innerWidth < 768) {
    sidebarStore.close();
  } else {
    sidebarStore.open();
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize);
  handleResize(); // já fecha ou abre conforme o tamanho da tela
});

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});
</script>

 
<style lang="css" scoped>
.content {
    position: fixed;
    top: 70px;
    height: calc(100vh - 70px);
    padding: 20px;
    background-color: #f8f8f8;
    overflow-y: auto;
    transition: left 0.3s ease, width 0.3s ease;

}
</style>

<template>
    <div>

        <Head :title="title" />
        <Navbar />
        <Slidebar />
        <main class="content" :style="{
            left: sidebarStore.isOpen ? '252px' : '0',
            width: sidebarStore.isOpen ? 'calc(100vw - 250px)' : '100vw',
        }">
            <slot />
        </main>
    </div>
</template>
