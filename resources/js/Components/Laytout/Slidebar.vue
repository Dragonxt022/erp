<template>
  <div class="sidebar">
    <div
      v-for="category in menuCategories"
      :key="category.name"
      class="menu-category"
    >
      <div class="category-title">{{ category.name }}</div>
      <MenuItem
        v-for="item in category.items"
        :key="item.link"
        :label="item.label"
        :icon="item.icon"
        :link="item.link"
        :isActive="isActive(item.link)"
        @onActivate="setActive"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import MenuItem from './MenuItem.vue';

const activeItem = ref('');

const menuCategories = [
  {
    name: '',
    items: [
      {
        label: 'Visão Geral',
        icon: '/storage/images/insert_chart.svg',
        link: 'painel',
      },
    ],
  },
  {
    name: 'Painel',
    items: [
      {
        label: 'E-mail',
        icon: '/storage/images/email.svg',
        link: 'painel/email',
      },
      {
        label: 'Comunidade',
        icon: '/storage/images/diversity_4.svg',
        link: 'painle/comunidade',
      },
      {
        label: 'Mídias',
        icon: '/storage/images/perm_media.svg',
        link: 'painel/midias',
      },
    ],
  },
  {
    name: 'Gestão da rede',
    items: [
      {
        label: 'Megafone',
        icon: '/storage/images/campaign.svg',
        link: 'painel/megafone',
      },
      {
        label: 'Franqueados',
        icon: '/storage/images/person.svg',
        link: 'painel/franqueados',
      },
      {
        label: 'Unidades',
        icon: '/storage/images/storefront.svg',
        link: 'painel/unidades',
      },
    ],
  },
  {
    name: 'Parâmetros da franquia',
    items: [
      {
        label: 'Fornecedores',
        icon: '/storage/images/fornecedores.svg',
        link: 'painel/fornecedores',
      },
      {
        label: 'Insumos',
        icon: '/storage/images/insumos.svg',
        link: 'painel/insumos',
      },
      {
        label: 'Inspetor',
        icon: '/storage/images/inspecionador.svg',
        link: 'painel/inspetor',
      },
    ],
  },
];

const setActive = (link) => {
  activeItem.value = link;
};

// Função para verificar se o item está ativo com base na URL atual
const isActive = (link) => {
  const currentPath = window.location.pathname.split('/').pop(); // Obtém o nome da página atual
  return currentPath === link;
};
</script>

<style scoped>
.sidebar {
  width: 249px;
  padding: 10px;
  height: calc(100% - 70px); /* Ajuste para compensar a altura da navbar */
  position: fixed;
  top: 70px;
  left: 0;
  background-color: #164110;
  display: flex;
  flex-direction: column;
  padding-top: 27px;
  padding-bottom: 27px;
  color: white;
  overflow-y: scroll; /* Faz a rolagem funcionar */
  scrollbar-width: thin; /* Controla a largura da barra de rolagem no Firefox */
  scrollbar-color: transparent transparent; /* Faz a barra de rolagem transparente */
}

/* Para navegadores baseados em Webkit (como Chrome, Safari, etc.) */
.sidebar::-webkit-scrollbar {
  width: 8px; /* Ajuste da largura da barra de rolagem */
}

.sidebar::-webkit-scrollbar-thumb {
  background-color: transparent; /* Torna a parte que desliza invisível */
}

.sidebar::-webkit-scrollbar-track {
  background: transparent; /* Torna a trilha da barra de rolagem invisível */
}

.menu-category {
  margin-bottom: 20px;
}

.category-title {
  color: #87ba73;
  font-size: 14px;
  font-family: Figtree, sans-serif;
  font-weight: 500;
  word-wrap: break-word;
  margin-bottom: 10px;
  padding-left: 14px;
}
</style>
