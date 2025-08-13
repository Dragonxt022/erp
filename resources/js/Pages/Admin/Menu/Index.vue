<template>
  <LayoutFranqueadora>
    <Head title="Menu do Franqueado" />
    <div class="painel-title">Menu do Franqueado</div>
    <div class="painel-subtitle">
      <p>Configurações de menu da Interface do Franqueado.</p>
    </div>
    <div v-if="loading">Carregando menu...</div>

    <div v-else class="mt-5">
      <div
        v-for="category in menuCategories"
        :key="category.id"
        class="mb-6"
      >
        <h3 class="font-bold text-lg text-green-700 mb-2">{{ category.name || 'Sem Categoria' }}</h3>

        <draggable
          v-model="category.items"
          group="menu"
          item-key="id"
          @end="onDragEnd(category)"
          class="grid grid-cols-3 gap-4"
        >
          <template #item="{ element }">
            <div
              class="menu-block p-4 border rounded shadow cursor-pointer bg-white"
              @click="openEditModal(element)"
            >
              <div class="font-semibold flex items-center gap-2">
                <img v-if="element.icon" :src="element.icon" alt="ícone" class="w-5 h-5" />
                {{ element.label }}
              </div>
              <div class="text-sm text-gray-500">{{ element.link }}</div>
            </div>
          </template>
        </draggable>
      </div>
    </div>

    <!-- Modal de Edição -->
    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal-content max-w-lg">
        <h2 class="text-xl font-semibold mb-4">Editar Botão</h2>

        <form @submit.prevent="saveEdit">
          <label class="block mb-2">Label</label>
          <input v-model="form.label" required class="input" />

          <label class="block mt-4 mb-2">Link (opcional)</label>
          <input v-model="form.link" class="input" />

          <label class="block mt-4 mb-2">Ícone (URL) (opcional)</label>
          <input v-model="form.icon" class="input" />

          <div class="flex justify-between mt-6">
            <button type="submit" class="btn-primary">Salvar</button>

            <button type="button" @click="duplicateItem" class="btn-secondary">
              Duplicar
            </button>

            <button type="button" @click="deleteItem" class="btn-danger">
              Excluir
            </button>

            <button type="button" @click="closeModal" class="btn-secondary ml-4">
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </LayoutFranqueadora>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import LayoutFranqueadora from '@/Layouts/LayoutFranqueadora.vue';
import draggable from 'vuedraggable';
import { Head } from '@inertiajs/vue3';

const menuCategories = ref([]);
const loading = ref(true);

const showModal = ref(false);
const form = ref({
  id: null,
  category_id: null,
  parent_id: null,
  label: '',
  icon: '',
  link: '',
  is_logout: false,
  required_permission: '',
  order: 0,
});

// Buscar menu já ordenado
const fetchMenu = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/menu');
    const categories = response.data.data.map(cat => {
      const itemsOrdered = [...cat.items].sort((a, b) => a.order - b.order);
      itemsOrdered.forEach(item => {
        if (item.children) {
          item.children = [...item.children].sort((a, b) => a.order - b.order);
        }
      });
      return {
        ...cat,
        items: itemsOrdered,
      };
    }).sort((a, b) => a.order - b.order);

    menuCategories.value = categories;
  } catch (error) {
    console.error('Erro ao carregar menu:', error);
  } finally {
    loading.value = false;
  }
};

// Abre modal e carrega item para edição
const openEditModal = (item) => {
  form.value = { ...item };
  showModal.value = true;
};

// Fecha modal
const closeModal = () => {
  showModal.value = false;
};

// Salva edição via PUT
const saveEdit = async () => {
  try {
    await axios.put(`/api/menu/items/${form.value.id}`, form.value);
    await fetchMenu();
    closeModal();
  } catch (error) {
    console.error('Erro ao salvar edição:', error);
    alert('Erro ao salvar edição');
  }
};

// Duplica o item, cria novo via POST com mesmo dados (menos id)
const duplicateItem = async () => {
  try {
    // Cria cópia sem id e order (vai pro fim)
    const duplicateData = { ...form.value };
    delete duplicateData.id;
    duplicateData.order = 9999; // para ficar no fim da lista
    const response = await axios.post('/api/menu/items', duplicateData);
    await fetchMenu();
    closeModal();
  } catch (error) {
    console.error('Erro ao duplicar item:', error);
    alert('Erro ao duplicar item');
  }
};

// Exclui o item via DELETE
const deleteItem = async () => {
  if (!confirm('Confirma exclusão deste item?')) return;

  try {
    await axios.delete(`/api/menu/items/${form.value.id}`);
    await fetchMenu();
    closeModal();
  } catch (error) {
    console.error('Erro ao excluir item:', error);
    alert('Erro ao excluir item');
  }
};

// Reordena após drag
const onDragEnd = async (category) => {
  category.items.forEach((item, index) => {
    item.order = index + 1;
    item.category_id = category.id;
  });

  const payload = category.items.map(item => ({
    id: item.id,
    order: item.order,
    category_id: item.category_id,
  }));

  try {
    await axios.post('/api/menu/items/reorder', { items: payload });
    await fetchMenu();
  } catch (error) {
    console.error('Erro ao salvar nova ordem:', error);
  }
};

onMounted(fetchMenu);
</script>

<style scoped>
.painel-title {
  font-size: 34px;
  line-height: 15px;
  font-weight: 700;
  color: #262a27;
  margin-bottom: 10px;
}
.painel-subtitle {
  font-size: 17px;
  line-height: 25px;
  color: #6d6d6e;
  max-width: 600px;
}

.menu-block {
  background: #f9fafb;
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 1rem;
  user-select: none;
}
.menu-block:hover {
  background: #eef6ee;
  cursor: pointer;
}

/* Botões */
.btn-primary {
  background-color: #6DB631;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
}
.btn-primary:hover {
  background-color: #5aa225;
}
.btn-secondary {
  background-color: #e0e0e0;
  color: #333;
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
}
.btn-secondary:hover {
  background-color: #c9c9c9;
}
.btn-danger {
  background-color: #e53e3e;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
}
.btn-danger:hover {
  background-color: #b72b2b;
}

/* Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}
.modal-content {
  background: white;
  padding: 24px;
  border-radius: 8px;
  width: 100%;
  max-width: 480px;
}
.input {
  width: 100%;
  padding: 8px 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
}
</style>
