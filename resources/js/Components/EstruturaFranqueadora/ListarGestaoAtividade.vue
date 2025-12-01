<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Gestão de atividades</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Visualize as atividades dos processos da franquia</p>
    </div>

    <!-- Campo de pesquisa -->
    <div class="search-container relative flex items-center w-full mb-4">
      <div class="absolute left-3">
        <img src="/storage/images/search.svg" alt="Ícone de pesquisa" class="w-5 h-5 text-gray-500" />
      </div>
      <input type="text" v-model="searchQuery" placeholder="Buscar uma atividade"
        class="search-input pl-10 w-full py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" />
    </div>

    <!-- Cards -->
    <div class="card-container">
      <div v-for="item in filteredOperacionais" :key="item.id"
        class="card cursor-pointer transform transition-transform duration-200 hover:shadow-lg"
        @click="selecionarOperacional(item)">
        <div class="card-inner">
          <div class="icon-container">
            <img :src="item.profile_photo_url" alt="Foto do setor" class="w-10 h-10" />
          </div>
          <div class="text-container">
            <div class="city">{{ item.name }}</div>
            <!-- Exibe a quantidade de etapas -->
            <div class="owner text-gray-500 text-sm">{{ item.etapas.length }} Etapas</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, computed, onMounted, defineEmits } from 'vue'
import axios from 'axios'

const operacionais = ref([])
const searchQuery = ref('')

const emit = defineEmits(['selecionado']);

const fetchOperacionais = async () => {
  try {
    const response = await axios.get('/api/atividades')
    operacionais.value = response.data.data
  } catch (error) {
    console.error('Erro ao carregar operacionais:', error)
  }
}

const selecionarOperacional = (item) => {
  // Emite para o componente pai se necessário
  emit('selecionado', item)
  console.log('Selecionado:', item)
}

const filteredOperacionais = computed(() =>
  operacionais.value.filter((item) =>
    item.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
)

onMounted(fetchOperacionais)
</script>


<style scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
  /* Cor escura para título */
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e;
  /* Cor secundária */
  max-width: 600px;
  /* Limita a largura do subtítulo */
}

.button-container {
  margin-top: 20px;
  text-align: right;
}

.card-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.card {
  width: 100%;
  height: 63px;
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0px 0px 1px rgba(142.11, 142.11, 142.11, 0.08);
}

.card-inner {
  display: flex;
  align-items: center;
  padding: 13px;
}

.icon-container {
  position: relative;
  width: 32px;
  height: 32px;
  margin-top: -8px;
}

.icon-bg {
  width: 32px;
  height: 32px;
  position: absolute;
  left: 0;

}

.text-container {
  margin-left: 14px;
  flex-grow: 1;
}

.city {
  font-size: 17px;
  font-family: Figtree;
  font-weight: 600;
  line-height: 22px;
  color: #262a27;
}

.owner {
  font-size: 13px;
  font-family: Figtree;
  font-weight: 500;
  line-height: 18px;
  color: #6d6d6e;
}

.action-icon {
  width: 24px;
  height: 24px;
}
</style>
