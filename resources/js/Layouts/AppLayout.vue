<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Navbar from '@/Components/Laytout/Navbar.vue';
import Slidebar from '@/Components/Laytout/Slidebar.vue';

defineProps({
  title: String,
});

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <div>
    <Head :title="title" />
    <Navbar />
    <Slidebar />
    <slot></slot>
  </div>
</template>
