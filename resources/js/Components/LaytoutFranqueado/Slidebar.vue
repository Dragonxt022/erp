<template>
    <div>
        <div v-if="loading" class="loader-container">
            <div class="spinner"></div>
        </div>


        <!-- Sidebar -->
        <div v-else :class="[
                'sidebar z-50 bg-[#164110] text-white flex flex-col p-3 transition-transform duration-300 ease-in-out',

                /* Mobile: fixo na esquerda */
                'fixed inset-y-0 left-0 transform',
                sidebarStore.isOpen ? 'translate-x-0' : '-translate-x-full',

                /* Desktop: fixo e sempre visível */
                'md:relative md:translate-x-0 md:h-screen md:sticky md:top-0'
            ]" ref="sidebar" @scroll="saveScrollPosition">

            <div v-for="category in filteredMenuCategories" :key="category.name" class="menu-category mb-6">
                <!-- Categoria de menu (Título escondido em telas pequenas) -->
                <div class="category-title menu-categorias text-sm sm:text-base font-medium text-[#87ba73] mb-3 pl-4"
                    v-if="category.items.length > 0">
                    {{ category.name }}
                </div>

                <!-- Itens do menu -->
                <MenuItem v-for="item in category.items" :key="item.link || 'no-link'" :label="item.label"
                    :icon="item.icon" :link="item.link" :submenuItems="item.children"
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
const menuCategories = ref([]);
const sidebarStore = useSidebarStore();
const loading = ref(true);
const openSubmenuLink = ref(null);

const userProfile = ref({});
const userPermissions = ref({});


const toggleSidebar = () => {
  sidebarStore.toggle();
};

// =====================
// PERMISSÕES
// =====================
const fetchPermissions = async () => {
  try {
    const response = await axios.get('/api/navbar-profile');

    const user = response.data.data;

    userProfile.value = {
      franqueado: user.franqueado,
      colaborador: user.colaborador,
    };

    userPermissions.value = user.permissions || {};

  } catch (error) {
    console.error('Erro ao carregar permissões:', error);
    userProfile.value = {};
    userPermissions.value = {};
  }
};


// =====================
// MENU
// =====================
const fetchMenu = async () => {
  try {
    const response = await axios.get(`/api/menu?t=${Date.now()}`);

    const categories = response.data?.data || [];

    menuCategories.value = categories
      .map(category => {

        const items = (category.items || [])
          .slice()
          .sort((a, b) => a.order - b.order)
          .map(item => {
            const children = item.children
              ? item.children.slice().sort((a, b) => a.order - b.order)
              : [];

            return {
              ...item,
              children,
            };
          });

        return { ...category, items };
      })
      .sort((a, b) => a.order - b.order);

  } catch (error) {
    console.error('[Menu] Erro ao carregar menu:', error);
    menuCategories.value = [];
  }
};

// =====================
// PROVIDE
// =====================
provide('userPermissions', userPermissions);
provide('openSubmenuLink', openSubmenuLink);

// =====================
// MENU FILTRADO (DEBUG TOTAL)
// =====================
const filteredMenuCategories = computed(() => {
  const { franqueado, colaborador } = userProfile.value;

  return menuCategories.value
    .map(category => {
      const items = category.items
        .map(item => {
          // REGRA DO DRE
          if (item.required_permission === 'dre') {
            const show = franqueado === true && colaborador === false;

            if (!show) return null;
          }

          // 🔐 PERMISSÕES NORMAIS
          if (
            item.required_permission &&
            userPermissions.value[item.required_permission] !== true
          ) {
            return null;
          }

          const children = (item.children || []).filter(child => {
            if (!child.required_permission) return true;
            return userPermissions.value[child.required_permission] === true;
          });

          return {
            ...item,
            children,
          };
        })
        .filter(Boolean);

      return { ...category, items };
    })
    .filter(category => category.items.length > 0);
});


// =====================
// MOUNT
// =====================
onMounted(async () => {
  loading.value = true;

  await fetchPermissions();
  await fetchMenu();

  const savedScrollPosition = localStorage.getItem('sidebarScrollPosition');

  if (savedScrollPosition && sidebar.value) {
    sidebar.value.scrollTop = savedScrollPosition;
  }

  loading.value = false;
});

// =====================
// SCROLL
// =====================
const saveScrollPosition = () => {
  if (sidebar.value) {
    localStorage.setItem('sidebarScrollPosition', sidebar.value.scrollTop);
  }
};

// =====================
// LINK ATIVO
// =====================
const isActive = (link) => {
  const currentPath = window.location.pathname;
  const resolvedPath = new URL(link, window.location.origin).pathname;

  const active =
    currentPath === resolvedPath || currentPath.startsWith(resolvedPath);
  return active;
};
</script>

<style scoped>
.sidebar {
    /* Remova o top e height fixos daqui se quiser usar a lógica do Tailwind */
    width: 249px;
    padding-top: 27px;
    padding-bottom: 27px;
    background-color: #164110;
    display: flex;
    flex-direction: column;
    color: white;

    /* Permite rolagem se o menu for grande */
    overflow-y: auto;

    /* Mantém a transição suave */
    transition: transform 0.3s ease-in-out;
}

.sidebar.-translate-x-full {
    transform: translateX(-100%);
}


/* Personalizando a barra de rolagem */
.sidebar::-webkit-scrollbar {
    width: 8px;
}

.sidebar::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.2); /* Mude de transparent para uma cor visível */
    border-radius: 4px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(255, 255, 255, 0.4);
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

/* No MenuItem.vue */
.submenu {
    margin-left: 20px; /* Adicione um recuo para os submenus aparecerem à direita do ícone pai */
    padding: 5px 0;
    border-left: 1px solid rgba(255, 255, 255, 0.1); /* Opcional: uma linha guia */
}

.submenu-item .label {
    font-size: 14px; /* Submenus levemente menores */
    opacity: 0.9;
}
@media (min-width: 768px) {
    .sidebar {
        /* Ajuste o '70px' para a altura exata da sua Navbar */
        height: 100vh;
        position: sticky;
        top: 0;
        padding-top: 95px;
        padding-bottom: 100px;
    }
}
@keyframes spin {
  to { transform: rotate(360deg); }
}



/* Overlay para mobile */
</style>
