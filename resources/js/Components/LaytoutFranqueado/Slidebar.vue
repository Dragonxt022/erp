<template>
    <div>
        <div v-if="loading" class="loader-container">
            <div class="spinner"></div>
        </div>


        <!-- Sidebar -->
        <div v-else :class="[
            'sidebar fixed top-16 left-0 bg-[#164110] text-white flex flex-col p-3 overflow-y-auto h-full transition-transform duration-300 ease-in-out',
            sidebarStore.isOpen ? 'translate-x-0' : '-translate-x-full',
            'md:translate-x-0 md:relative  md:top-0 md:left-0'
        ]" ref="sidebar" @scroll="saveScrollPosition" >

            <div v-for="category in filteredMenuCategories" :key="category.name" class="menu-category mb-6">
                <!-- Categoria de menu (Título escondido em telas pequenas) -->
                <div class="category-title menu-categorias text-sm sm:text-base font-medium text-[#87ba73] mb-3 pl-4"
                    v-if="category.items.length > 0">
                    {{ category.name }}
                </div>

                <!-- Itens do menu -->
                <MenuItem v-for="item in category.items" :key="item.link || 'no-link'" :label="item.label"
                    :icon="item.icon" :link="item.link" :submenuItems="item.submenuItems"
                    :isActive="item.link ? isActive(item.link) : false" :isLogout="item.isLogout"
                    :requiredPermission="item.requiredPermission" />
            </div>
        </div>


    </div>
</template>

<script setup>
import { ref, onMounted, computed, provide } from 'vue';
import { useSidebarStore } from '@/stores/sidebar';

import MenuItem from './MenuItem.vue';
import axios from 'axios';

const sidebar = ref(null);
const userPermissions = ref({});
const menuCategories = ref([]);
const sidebarStore = useSidebarStore();
const loading = ref(true);
const openSubmenuLink = ref(null); // Controla qual submenu está aberto

const toggleSidebar = () => {
  sidebarStore.toggle();
};

// Busca permissões do usuário
const fetchPermissions = async () => {
  try {
    const response = await axios.get('/api/navbar-profile');
    userPermissions.value = response.data.data.permissions || {};
  } catch (error) {
    console.error('Erro ao carregar permissões:', error);
    userPermissions.value = {};
  }
};

// Busca o menu e ordena categorias, itens e filhos
const fetchMenu = async () => {
  try {
    // Adiciona timestamp para evitar cache
    const response = await axios.get(`/api/menu?t=${Date.now()}`);
    const categories = response.data.data || [];

    // DEBUG: Verifica se o item 31 está presente
    let found31 = false;
    categories.forEach(category => {
      category.items.forEach(item => {
        if (item.id === 31) {
          found31 = true;
          console.log('🔴 ITEM 31 ENCONTRADO NO FRONTEND:', item);
        }
      });
    });
    
    if (!found31) {
      console.log('✅ Item 31 (DRE) NÃO está na resposta da API - filtro funcionando!');
    } else {
      console.error('❌ Item 31 (DRE) AINDA ESTÁ NA RESPOSTA - filtro NÃO funcionando!');
    }

    // Ordena categorias pelo campo 'order'
    const orderedCategories = categories
      .map(category => {
        // Ordena os itens da categoria
        const orderedItems = (category.items || []).slice().sort((a, b) => a.order - b.order);

        // Ordena os filhos de cada item, se existirem
        orderedItems.forEach(item => {
          if (item.children && Array.isArray(item.children)) {
            item.children = item.children.slice().sort((a, b) => a.order - b.order);
          }
        });

        return { ...category, items: orderedItems };
      })
      .sort((a, b) => a.order - b.order);

    menuCategories.value = orderedCategories;
  } catch (error) {
    console.error('Erro ao carregar menu:', error);
    menuCategories.value = [];
  }
};

// Fornece userPermissions para componentes filhos via provide/inject
provide('userPermissions', userPermissions);
provide('openSubmenuLink', openSubmenuLink);

// Computed que filtra categorias e itens com base nas permissões do usuário
const filteredMenuCategories = computed(() => {
  return menuCategories.value
    .map(category => ({
      ...category,
      items: category.items.map(item => ({
          ...item,
          submenuItems: item.children || [],
      }))
    }));
});

onMounted(async () => {
  loading.value = true;
  await fetchPermissions();
  await fetchMenu();

  // Restaura posição de scroll da sidebar
  const savedScrollPosition = localStorage.getItem('sidebarScrollPosition');
  if (savedScrollPosition && sidebar.value) {
    sidebar.value.scrollTop = savedScrollPosition;
  }
  loading.value = false;
});

// Salva posição de scroll da sidebar
const saveScrollPosition = () => {
  if (sidebar.value) {
    localStorage.setItem('sidebarScrollPosition', sidebar.value.scrollTop);
  }
};

// Verifica se o link está ativo (para destacar menu)
const isActive = (link) => {
  const currentPath = window.location.pathname;
  const resolvedPath = new URL(link, window.location.origin).pathname;
  return currentPath === resolvedPath || currentPath.startsWith(resolvedPath);
};
</script>


<style scoped>
.sidebar {
    height: calc(100% - 60px);
    top: 70px;
    width: 249px;
    padding-top: 27px;
    padding-bottom: 27px;
    background-color: #164110;
    display: flex;
    flex-direction: column;
    color: white;
    overflow-y: hidden;

    scrollbar-width: thin;
    scrollbar-color: transparent transparent;
    /* Para a animação do toggle */
    transform: translateX(0);
}

.sidebar.-translate-x-full {
    transform: translateX(-100%);
}


/* Personalizando a barra de rolagem */
.sidebar::-webkit-scrollbar {
    width: 8px;
}

.sidebar::-webkit-scrollbar-thumb {
    background-color: transparent;
}

.sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.spinner {
  border: 4px solid rgba(255, 255, 255, 0.2);
  border-top-color: #6DB631;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  animation: spin 1s linear infinite;
  margin: auto;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}



/* Overlay para mobile */
</style>
