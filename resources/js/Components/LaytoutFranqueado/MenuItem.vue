<template>
    <div>
        <!-- Link externo ou interno -->
        <component :is="isExternalLink ? 'a' : Link" v-if="!isLogout && link && isVisible" class="menu-item"
            :class="[menuItemClass, { active: isActive || isAnySubmenuActive }]" :href="linkHref"
            :target="isExternalLink ? '_blank' : null" @click="handleClick">
            <div class="icon">
                <img :src="icon" alt="icon" />
            </div>
            <div class="label">{{ label }}</div>
            <div v-if="filteredSubmenuItems.length > 0" class="right-icon" :class="{ 'rotate-icon': isIconRotated }">
                <img src="/storage/images/arrow_drop_down.svg" alt="arrow icon" />
            </div>
        </component>

        <!-- Caso seja logout, trata com POST -->
        <form v-else-if="isLogout && isVisible" @submit.prevent="handleLogout" class="menu-item">
            <button type="submit" class="w-full h-full flex items-center">
                <div class="icon">
                    <img :src="icon" alt="icon" />
                </div>
                <div class="label">{{ label }}</div>
            </button>
        </form>

        <!-- Submenu -->
        <div v-if="
            filteredSubmenuItems.length > 0 && (showSubmenu || isAnySubmenuActive)
        " class="submenu">
            <div v-for="(submenu, submenuIndex) in filteredSubmenuItems" :key="submenuIndex">
                <Link class="submenu-item" :href="route(submenu.link)"
                    :class="{ active: isSubmenuActive(submenu.link) }">
                <div class="label">{{ submenu.label }}</div>
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, ref, computed, watch, nextTick, inject } from 'vue';
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
    requiredPermission: String,
});

// userPermissions removido
const openSubmenuLink = inject('openSubmenuLink', ref(null)); // Injeta o estado compartilhado

const showSubmenu = computed(() => openSubmenuLink.value === props.link);
const isIconRotated = computed(() => showSubmenu.value);

// Verifica se o link é externo (começa com http:// ou https://)
const isExternalLink = computed(() => {
    return props.link && /^https?:\/\//.test(props.link);
});

// Calcula o href dependendo se é interno ou externo
const linkHref = computed(() => {
    return isExternalLink.value
        ? props.link
        : props.link
            ? route(props.link)
            : '#';
});

// Verifica se o item principal é visível
const isVisible = computed(() => {
    return true; // Visibilidade total (permissões removidas)
});

// Filtra submenus com base nas permissões
const filteredSubmenuItems = computed(() => {
    return props.submenuItems; // Retorna todos os submenus (sem filtro)
});

const handleClick = (e) => {
    if (filteredSubmenuItems.value.length > 0) {
        e.preventDefault();
        toggleSubmenu();
    }
    // Para links externos, o clique não precisa ser tratado aqui, pois o <a> já redireciona
};

const toggleSubmenu = () => {
    // Se o submenu atual está aberto, fecha. Caso contrário, abre e fecha os outros
    if (openSubmenuLink.value === props.link) {
        openSubmenuLink.value = null;
    } else {
        openSubmenuLink.value = props.link;
    }
    saveSubmenuState();
};

const loadSubmenuState = () => {
    const storedState = localStorage.getItem('openSubmenuLink');
    if (storedState !== null) {
        openSubmenuLink.value = storedState;
    }
}; 

const saveSubmenuState = () => {
    if (openSubmenuLink.value) {
        localStorage.setItem('openSubmenuLink', openSubmenuLink.value);
    } else {
        localStorage.removeItem('openSubmenuLink');
    }
};

const menuItemClass = computed(() => {
    return isAnySubmenuActive.value || props.isActive ? 'submenu-active' : '';
});

const isSubmenuActive = (subLink) => {
    const currentPath = window.location.pathname;
    const resolvedPath = new URL(route(subLink), window.location.origin).pathname;
    return currentPath === resolvedPath;
};

const isAnySubmenuActive = computed(() => {
    return filteredSubmenuItems.value.some((item) => isSubmenuActive(item.link));
});

const handleLogout = () => {
    if (props.isLogout) {
        router.post(route('logout.sair'));
    }
};



loadSubmenuState();

const checkSubmenuStatus = () => {
    if (!isAnySubmenuActive.value && openSubmenuLink.value === props.link) {
        nextTick(() => {
            openSubmenuLink.value = null;
            saveSubmenuState();
        });
    }
};

watch(isAnySubmenuActive, checkSubmenuStatus);
watch(openSubmenuLink, () => {
    nextTick(() => saveSubmenuState());
});
</script>

<style scoped>
/* Mantém o mesmo estilo fornecido */
.rotate-icon {
    transform: rotate(90deg);
    transition: transform 0.3s ease-in-out;
}

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
    font-size: 90%;
    font-family: Figtree;
    font-weight: 600;
    line-height: 22px;
    word-wrap: break-word;
}

.menu-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.submenu {
    margin-left: 0px;
    padding: 5px 0;
}

.submenu-item {
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

.submenu-item.active {
    background-color: #568f4063;
}

.submenu-item .label {
    color: white;
    font-size: 15px;
    font-family: Figtree;
    font-weight: 600;
    line-height: 22px;
    word-wrap: break-word;
}

.submenu-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
}
</style>
