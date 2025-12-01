<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Pontuação por critério</div>

    <!-- Subtítulo -->
    <div class="painel-subtitle">
      <p>Define o valor de cada parâmetro de produtividade</p>
    </div>

    <!-- Pontos Positivos -->
    <div class="section">
      <h2 class="section-title">Positivos</h2>
      <div class="criterios-list">
        <div
          v-for="item in pontosPositivos"
          :key="item.id"
          class="criterio-item"
          @click="$emit('selecionado', item)"
          style="cursor: pointer;"
        >
          <span class="criterio-nome">{{ item.name }}</span>
          <span class="criterio-pontos positivo">{{ formatPonto(item.pontos) }}</span>
        </div>
      </div>
    </div>

    <!-- Pontos Negativos -->
    <div class="section" style="margin-top: 2rem;">
      <h2 class="section-title">Negativos</h2>
      <div class="criterios-list">
        <div
          v-for="item in pontosNegativos"
          :key="item.id"
          class="criterio-item"
          @click="$emit('selecionado', item)"
          style="cursor: pointer;"
        >
          <span class="criterio-nome">{{ item.name }}</span>
          <span class="criterio-pontos negativo">{{ formatPonto(item.pontos) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const pontos = ref([])

const fetchPontos = async () => {
  try {
    const response = await axios.get('/api/pontos') // Ajuste para sua rota real
    pontos.value = response.data
  } catch (error) {
    console.error('Erro ao carregar pontos:', error)
  }
}

const pontosPositivos = computed(() =>
  pontos.value.filter(item => item.pontos >= 0)
)

const pontosNegativos = computed(() =>
  pontos.value.filter(item => item.pontos < 0)
)

const formatPonto = (valor) => {
  const abs = Math.abs(valor)
  const texto = abs === 1 ? 'ponto' : 'pontos'
  return `${valor} ${texto}`
}

onMounted(fetchPontos)
</script>

<style scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e;
  max-width: 600px;
}

.section-title {
  font-weight: 500;
  font-size: 15px;
  color: #262a27;
  margin-bottom: 0;
}

.criterios-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.criterio-item {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  background: #fff;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
}

.criterio-nome {
  font-weight: 600;
  font-size: 1rem;
  color: #262a27;
  margin-bottom: 0.2rem;
}

.criterio-pontos {
  font-weight: 600;
  font-size: 0.9rem;
}

.positivo {
  color: #22863a; /* verde */
}

.negativo {
  color: #d73a49; /* vermelho */
}
</style>
