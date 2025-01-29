<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Contas a pagar</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Compromissos financeiros do mês vigente</p>
    </div>

    <!-- Listagem das contas -->
    <div
      v-for="conta in filteredDados"
      :key="conta.id"
      @click="selecionarDados(conta)"
      class="flex justify-between items-center bg-white p-4 rounded-lg cursor-pointer hover:bg-gray-100 transition mt-3"
    >
      <!-- Informações da conta -->
      <div>
        <p class="text-lg font-medium text-gray-900">{{ conta.nome }}</p>
        <p class="text-sm text-gray-600">
          {{ conta.valor_formatado }} - Vence em
          {{ formatarData(conta.vencimento) }}
        </p>
      </div>

      <!-- Ícone de status -->
      <div>
        <img
          :src="getStatusIcon(conta.status)"
          :alt="conta.status"
          class="w-8 h-8"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['dado-selecionado']);

const dados = ref([]);
const searchQuery = ref('');

// Buscar as contas da API
const fetchDados = async () => {
  try {
    const response = await axios.get('/api/cursto/listar-contas-a-pagar');
    dados.value = response.data.data;
  } catch (error) {
    console.error('Erro ao carregar os dados:', error);
  }
};

// Chamada ao montar o componente
onMounted(fetchDados);

// Selecionar uma conta
const selecionarDados = (conta) => {
  emit('dado-selecionado', conta);
};

// Filtragem das contas por pesquisa
const filteredDados = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return dados.value.filter(
    (conta) =>
      conta.nome.toLowerCase().includes(query) ||
      String(conta.valor).includes(query)
  );
});

// Formatar a data
const formatarData = (data) => {
  const partes = data.split('-'); // Divide "YYYY-MM-DD"
  return `${partes[2]}/${partes[1]}/${partes[0]}`; // Retorna "DD/MM/YYYY"
};

// Retornar o ícone do status
const getStatusIcon = (status) => {
  return status === 'pendente'
    ? '/storage/images/check_circle_laranja.svg'
    : '/storage/images/check_circle_verde.svg';
};
</script>

<style scoped>
.estrela {
  color: gold;
  font-size: 20px;
  margin-left: 15px;
}
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}

.button-container {
  margin-top: 15px;
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
  padding: 8px;
}

.icon-container {
  position: relative;
  width: 55px;
  height: 55px;
}

.icon-bg {
  width: 55px;
  height: 55px;
  position: absolute;
  left: 0;
  top: 1.33px;
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
