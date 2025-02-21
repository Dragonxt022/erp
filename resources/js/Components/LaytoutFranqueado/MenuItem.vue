<template>
  <div>
    <!-- Link do item principal -->
    <Link
      v-if="!isLogout && link"
      class="menu-item"
      :class="[menuItemClass, { active: isActive || isAnySubmenuActive }]"
      :href="link || '#'"
      @click="handleClick"
    >
      <div class="icon">
        <img :src="icon" alt="icon" />
      </div>
      <div class="label">{{ label }}</div>
      <div
        v-if="submenuItems && submenuItems.length > 0"
        class="right-icon"
        :class="{ 'rotate-icon': isIconRotated }"
      >
        <img src="/storage/images/arrow_drop_down.svg" alt="arrow icon" />
      </div>
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
const isIconRotated = computed(() => showSubmenu.value);

const handleClick = (e) => {
  if (props.submenuItems && props.submenuItems.length > 0) {
    e.preventDefault();
    toggleSubmenu();
  }
};

const toggleSubmenu = () => {
  showSubmenu.value = !showSubmenu.value;
  saveSubmenuState();
};

const loadSubmenuState = () => {
  const storedState = localStorage.getItem(`submenu-${props.link}`);
  if (storedState !== null) {
    showSubmenu.value = JSON.parse(storedState);
  }
};

const saveSubmenuState = () => {
  localStorage.setItem(
    `submenu-${props.link}`,
    JSON.stringify(showSubmenu.value)
  );
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
  return props.submenuItems.some((item) => isSubmenuActive(item.link));
});

const handleLogout = () => {
  if (props.isLogout) {
    router.post(route('logout'));
  }
};

loadSubmenuState();

const checkSubmenuStatus = () => {
  if (!isAnySubmenuActive.value) {
    nextTick(() => {
      showSubmenu.value = false;
      saveSubmenuState();
    });
  }
};

watch(isAnySubmenuActive, checkSubmenuStatus);

watch(showSubmenu, () => {
  nextTick(() => {
    saveSubmenuState();
  });
});
</script>

<style scoped>
.rotate-icon {
  transform: rotate(90deg);
  transition: transform 0.3s ease-in-out;
}

.menu-item {
  display: flex;
  align-items: center;
  height: 44px;
  padding-left: 8px;
  padding-right: 8px;
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
  width: 20px;
  height: 20px;
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
  background-color: rgba(255, 255, 255, 0.1);
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
