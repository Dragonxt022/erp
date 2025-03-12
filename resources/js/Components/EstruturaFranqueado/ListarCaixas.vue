<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Caixa Retroativo</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Edite, exclua e adicione caixas retroativos</p>
    </div>
    <ButtonPrimaryMedio
      text="Adicionar um novo caixa"
      class="w-full"
      @click="abrirRotaCaixa"
    />
    <!-- Lista de caixas -->
    <div
      v-for="caixa in caixas"
      :key="caixa.id"
      @click="selecionarcaixa(caixa)"
      class="relative flex items-center bg-white p-4 rounded-lg cursor-pointer hover:bg-gray-100 transition mt-3"
    >
      <!-- Informações do caixa -->
      <div>
        <p class="text-lg font-medium text-gray-900">
          Caixa N° {{ caixa.id }} | Valor
          {{ caixa.valor_total_fechamento || 0 }}
        </p>
        <div class="flex items-center space-x-1">
          <img
            src="/storage/images/usc.svg"
            alt="Ícone"
            class="w-[14px] h-[14px]"
          />
          <p class="text-sm text-gray-600">
            {{ caixa.usuario_fechou }}
          </p>
        </div>
        <div class="flex items-center space-x-4">
          <p class="text-sm text-gray-600">
            Abriu: {{ formatDate(caixa.hora_abertura) }}
          </p>
          <p class="text-sm text-gray-600">
            Fechou: {{ formatDate(caixa.hora_fechamento) }}
          </p>
          <p class="text-sm text-gray-600">Ativo: {{ caixa.horas_aberto }}</p>
        </div>
      </div>
      <!-- Indicador de status no canto direito -->
      <div
        class="absolute right-4 top-1/2 transform -translate-y-1/2 px-2 py-1 rounded-full text-xs font-semibold text-white"
        :class="{
          'bg-yellow-500': caixa.status === 'Aberto',
          'bg-green-500': caixa.status === 'Fechado',
        }"
      >
        {{ caixa.status }}
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import { Inertia } from '@inertiajs/inertia'; // Importa o Inertia

const emit = defineEmits(['caixa-selecionado', 'abrir-cadastro']);
const caixas = ref([]);

// Buscar os caixas da API
const fetchCaixas = async () => {
  try {
    const response = await axios.get('/api/caixas/lista'); // Ajuste o endpoint conforme sua rota
    caixas.value = response.data.data; // Atribui os dados retornados pela API
  } catch (error) {
    console.error('Erro ao carregar os caixas:', error);
  }
};

// Formatar data para exibição
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short',
  });
};

// Chamada ao montar o componente
onMounted(fetchCaixas);

// Selecionar um caixa
const selecionarcaixa = (caixa) => {
  emit('caixa-selecionado', caixa);
};

// Alternar entre a tela de cadastro e a listagem
const abrirRotaCaixa = () => {
  Inertia.visit(route('franqueado.abrirCaixa'));
};
</script>

<style scoped>
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
