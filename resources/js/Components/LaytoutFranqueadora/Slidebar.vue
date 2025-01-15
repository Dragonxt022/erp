<template>
  <div class="sidebar" ref="sidebar" @scroll="saveScrollPosition">
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
        :link="route(item.link)"
        :isActive="isActive(route(item.link))"
        :isLogout="item.isLogout"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MenuItem from './MenuItem.vue';

// Referência da sidebar
const sidebar = ref(null);

// Recuperar a posição da rolagem do localStorage
onMounted(() => {
  const savedScrollPosition = localStorage.getItem('sidebarScrollPosition');
  if (savedScrollPosition && sidebar.value) {
    sidebar.value.scrollTop = savedScrollPosition;
  }
});

// Salvar a posição da rolagem no localStorage sempre que a sidebar for rolada
const saveScrollPosition = () => {
  if (sidebar.value) {
    localStorage.setItem('sidebarScrollPosition', sidebar.value.scrollTop);
  }
};

// Função para verificar se o link está ativo
const isActive = (link) => {
  const currentPath = window.location.pathname;
  const resolvedPath = new URL(link, window.location.origin).pathname;
  return currentPath === resolvedPath || currentPath.startsWith(resolvedPath);
};

// Definindo as categorias de menu
const menuCategories = [
  {
    name: '',
    items: [
      {
        label: 'Visão Geral',
        icon: '/storage/images/insert_chart.svg',
        link: 'franqueadora.painel',
        isLogout: false,
      },
    ],
  },
  {
    name: 'Painel',
    items: [
      {
        label: 'E-mail',
        icon: '/storage/images/email.svg',
        link: 'franqueadora.email',
        isLogout: false,
      },
      {
        label: 'Comunidade',
        icon: '/storage/images/diversity_4.svg',
        link: 'franqueadora.comunidade',
        isLogout: false,
      },
      {
        label: 'Mídias',
        icon: '/storage/images/perm_media.svg',
        link: 'franqueadora.midias',
        isLogout: false,
      },
    ],
  },
  {
    name: 'Gestão da rede',
    items: [
      {
        label: 'Megafone',
        icon: '/storage/images/campaign.svg',
        link: 'franqueadora.megafone',
        isLogout: false,
      },
      {
        label: 'Franqueados',
        icon: '/storage/images/person.svg',
        link: 'franqueadora.franqueados',
        isLogout: false,
      },
      {
        label: 'Unidades',
        icon: '/storage/images/storefront.svg',
        link: 'franqueadora.unidades',
        isLogout: false,
      },
    ],
  },
  {
    name: 'Parâmetros da franquia',
    items: [
      {
        label: 'Fornecedores',
        icon: '/storage/images/handshake_branca.svg',
        link: 'franqueadora.fornecedores',
        isLogout: false,
      },
      {
        label: 'Insumos',
        icon: '/storage/images/insumos_nova.svg',
        link: 'franqueadora.insumos',
        isLogout: false,
      },
      {
        label: 'Inspetor',
        icon: '/storage/images/inspecionador.svg',
        link: 'franqueadora.inspetor',
        isLogout: false,
      },
      {
        label: 'Taiksu IA',
        icon: '/storage/images/TAIKSU_IA_ICONE.svg',
        link: 'franqueado.midias',
        isLogout: false,
        isActive: false,
      },
      {
        label: 'Sair',
        icon: '/storage/images/log-out.png',
        link: 'logout',
        isLogout: true,
      },
    ],
  },
];
</script>

<style scoped>
.sidebar {
  width: 249px;
  padding: 10px;
  height: calc(100% - 70px);
  position: fixed;
  top: 70px;
  left: 0;
  background-color: #164110;
  display: flex;
  flex-direction: column;
  padding-top: 27px;
  padding-bottom: 27px;
  color: white;
  overflow-y: scroll;
  scrollbar-width: thin;
  scrollbar-color: transparent transparent;
}

.sidebar::-webkit-scrollbar {
  width: 8px;
}

.sidebar::-webkit-scrollbar-thumb {
  background-color: transparent;
}

.sidebar::-webkit-scrollbar-track {
  background: transparent;
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
