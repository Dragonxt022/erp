<template>
  <LayoutFranqueado>
    <Head title="Painel" />
    <div class="painel-title">DRE Gerencial</div>
    <div class="painel-subtitle">
      <p>Acompanhe a saúde da sua operação</p>
    </div>

    <div class="mt-5">
      <div class="grid grid-cols-2 grid-rows-1 gap-4">
        <div class="rounded-lg">
          <table
            class="w-full text-left text-[14px] border-collapse font-['Figtree']"
          >
            <thead>
              <tr class="bg-[#174111] text-white">
                <th colspan="2" class="p-1 px-5">Faturamento do Período</th>
              </tr>
            </thead>
            <tbody v-if="loading">
              <!-- Skeleton Loader com efeito de brilho (shimmer) -->
              <tr v-for="n in 25" :key="n">
                <td class="p-1 px-5 shimmer h-6 w-1/2 rounded"></td>
                <td class="p-1 px-5 shimmer h-6 w-1/4 rounded text-right"></td>
              </tr>
            </tbody>

            <tbody v-else>
              <tr>
                <td class="p-1 px-5 categorias">Faturamento do Período</td>
                <td class="px-5 py-2 text-right valores">
                  R$ {{ totalCaixas }}
                </td>
              </tr>
            </tbody>

            <template v-for="grupo in grupos" :key="grupo.nome_grupo">
              <thead>
                <tr class="bg-[#174111] text-white">
                  <th colspan="2" class="p-1 px-5">{{ grupo.nome_grupo }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="categoria in grupo.categorias"
                  :key="categoria.categoria"
                  class="odd:bg-gray-100 even:bg-white p-1 px-5"
                >
                  <td class="p-1 px-5 categorias align-middle">
                    {{ categoria.categoria }}
                  </td>
                  <td class="px-5 py-1 text-right valores">
                    R$ {{ categoria.total }}
                  </td>
                </tr>
              </tbody>
            </template>

            <thead>
              <tr class="bg-[#174111] text-white">
                <th colspan="1" class="p-2">Resultado do Período</th>
                <th colspan="1" class="p-2 text-right font-bold">
                  R$ {{ resultadoPeriodo }}
                </th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import axios from 'axios';

const totalCaixas = ref('0,00');
const resultadoPeriodo = ref('0,00');
const grupos = ref([]);
const loading = ref(true);

const fetchData = async () => {
  try {
    const response = await axios.get('/api/painel-dre/analitycs-dre');
    totalCaixas.value = response.data.total_caixas;
    resultadoPeriodo.value = response.data.resultado_do_periodo;
    grupos.value = response.data.grupos;
  } catch (error) {
    console.error('Erro ao buscar os dados do DRE:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);
</script>

<style lang="css" scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
  margin-bottom: -10px;
}
.painel-subtitle {
  font-size: 17px;
  color: #6d6d6e;
  max-width: 600px;
}

.categorias {
  color: #6d6d6e;
  font-size: 14px;
  font-family: Figtree;
  font-weight: 600;
  text-transform: capitalize;
  line-height: 14px;
  word-wrap: break-word;
}

.valores {
  color: #6d6d6e;
  font-size: 14px;
  font-family: Figtree;
  font-weight: 700;
  text-transform: capitalize;
  line-height: 14px;
  word-wrap: break-word;
}

/* Animação de shimmer */
.shimmer {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite linear;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style>
