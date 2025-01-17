<template>
  <div>
    <!-- Link do item principal -->
    <Link
      v-if="!isLogout"
      class="menu-item"
      :class="[menuItemClass, { active: isActive || isAnySubmenuActive }]"
      :href="link"
      @click.prevent="toggleSubmenu"
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

    <!-- Submenu -->
    <div
      v-if="
        submenuItems &&
        submenuItems.length > 0 &&
        (showSubmenu || isAnySubmenuActive)
      "
      class="submenu"
    >
      <div v-for="(submenu, submenuIndex) in submenuItems" :key="submenuIndex">
        <Link
          class="submenu-item"
          :href="route(submenu.link)"
          :class="{ active: isSubmenuActive(submenu.link) }"
        >
          <!-- <div class="icon">
            <img :src="submenu.icon" alt="submenu icon" />
          </div> -->
          <div class="label">{{ submenu.label }}</div>
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref, computed, watch, nextTick } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
  label: String,
  icon: String,
  link: String,
  isActive: Boolean,
  isLogout: Boolean,
  submenuItems: {
    type: Array,
    default: () => [],
  },
});

const showSubmenu = ref(false);

// Função para carregar o estado do submenu do localStorage
const loadSubmenuState = () => {
  const storedState = localStorage.getItem(`submenu-${props.link}`);
  if (storedState !== null) {
    showSubmenu.value = JSON.parse(storedState);
  }
};

// Função para salvar o estado do submenu no localStorage
const saveSubmenuState = () => {
  localStorage.setItem(
    `submenu-${props.link}`,
    JSON.stringify(showSubmenu.value)
  );
};

// Função para alternar o submenu
const toggleSubmenu = () => {
  showSubmenu.value = !showSubmenu.value;
  saveSubmenuState(); // Salva o estado no localStorage
};

// Aplica a classe 'submenu-active' quando o submenu estiver ativo
const menuItemClass = computed(() => {
  return isAnySubmenuActive.value || props.isActive ? 'submenu-active' : '';
});

// Verifica se algum submenu está ativo
const isSubmenuActive = (subLink) => {
  const currentPath = window.location.pathname;
  const resolvedPath = new URL(route(subLink), window.location.origin).pathname;
  return currentPath === resolvedPath;
};

// Verifica se qualquer submenu está ativo
const isAnySubmenuActive = computed(() => {
  return props.submenuItems.some((item) => isSubmenuActive(item.link));
});

// Função de logout
const handleLogout = () => {
  if (props.isLogout) {
    router.post(route('logout')); // Envia a requisição POST para a rota de logout
  }
};

// Carregar o estado do submenu quando o componente é montado
loadSubmenuState();

// Função para verificar e fechar automaticamente o submenu se não houver navegação nos submenus
const checkSubmenuStatus = () => {
  // Caso não esteja navegando no submenu, fechamos o submenu
  if (!isAnySubmenuActive.value) {
    nextTick(() => {
      showSubmenu.value = false;
      saveSubmenuState(); // Atualiza o estado no localStorage
    });
  }
};

// Verificar automaticamente o estado do submenu após o carregamento ou navegação
watch(isAnySubmenuActive, checkSubmenuStatus);

// Para garantir que o estado de visibilidade do submenu seja atualizado após qualquer mudança
watch(showSubmenu, (newValue) => {
  nextTick(() => {
    // Certifique-se de que a atualização do submenu esteja no próximo ciclo de renderização
    saveSubmenuState();
  });
});
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
  margin-right: 5px;
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

/* Estilo para o submenu */
.submenu {
  margin-left: 0px; /* Distância à esquerda dos subitens */
  padding: 5px 0;
}

.submenu-item {
  display: flex;
  align-items: center;
  height: 44px; /* Mesmo tamanho dos itens do menu */
  padding-left: 14px;
  padding-right: 14px;
  cursor: pointer;
  border-radius: 10px;
  margin-bottom: 10px;
  user-select: none;
  transition: background-color 0.3s;
}

.submenu-item.active {
  background-color: #568f4063; /* Mesma cor de fundo quando ativo */
}

.submenu-item .icon {
  width: 24px;
  height: 24px;
  margin-right: 0px;
}

.submenu-item .label {
  color: white;
  font-size: 15px;
  font-family: Figtree;
  font-weight: 600;
  line-height: 22px;
  word-wrap: break-word;
}

.submenu-link.active {
  background-color: #568f40;
}

.submenu-item:hover {
  background-color: rgba(
    255,
    255,
    255,
    0.1
  ); /* Cor de hover igual aos itens principais */
}

.submenu-item a {
  text-decoration: none;
  color: white;
  font-size: 13px;
}

.submenu-item a:hover {
  text-decoration: underline;
}

@media (max-width: 840px) {
  .menu-item {
    height: 50%;
    padding-left: 5%;
    padding-right: 5%;
    border-radius: 10px;
    margin-bottom: 10px;
    /* justify-content: center;  */
  }

  .menu-item.active {
    background-color: #568f40;
  }

  .menu-item .icon {
    width: 25%;
    height: 25%;
    margin-right: 80%;
    margin-bottom: 8%;
  }

  .menu-item .label {
    display: none;
  }

  .menu-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
  }
}
</style>
