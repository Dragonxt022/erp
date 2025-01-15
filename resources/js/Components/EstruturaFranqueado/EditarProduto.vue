<template>
  <transition name="fade">
    <div v-if="isVisible" class="sidebar-container">
      <!-- Animação de Carregamento -->
      <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
      </div>
      <div v-else>
        <div class="w-full h-[80%] bg-white rounded-[20px] p-12">
          <div class="relative w-full h-full">
            <!-- Container das colunas -->
            <div class="flex items-center">
              <!-- Coluna da Imagem -->
              <div class="w-1/1 flex justify-center">
                <img
                  :src="getProfilePhotoUrl(produto.profile_photo)"
                  alt="Foto do Produto"
                  class="w-20 h-20 rounded-md shadow-lg"
                />
              </div>

              <div class="w-2/3 pl-5">
                <div
                  class="text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[30px] tracking-tight"
                >
                  {{ produto.nome || 'N/A' }}

                  <div
                    class="text-[#6db631] text-[25px] font-bold font-['Figtree'] mt-3 tracking-tight"
                  >
                    {{
                      produto.unidadeDeMedida === 'a_granel' &&
                      produto.valor_pago_por_quilo_lote
                        ? produto.valor_pago_por_quilo_lote
                        : produto.valor_total_lote
                    }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <form @submit.prevent="showConfirmDialog('Atualizar este produto?')">
          <!-- Tabela de Lotes -->
          <div class="mt-5">
            <table class="min-w-full table-auto">
              <thead>
                <tr>
                  <th
                    class="px-6 py-2 text-[15px] font-semibold text-[#1d5915] uppercase tracking-wider TrRedonEsquerda"
                  >
                    entrada
                  </th>
                  <th
                    class="px-6 py-2 text-[15px] font-semibold text-[#1d5915] uppercase tracking-wider"
                  >
                    Fornecedor
                  </th>
                  <th
                    class="px-6 py-2 text-[15px] font-semibold text-[#1d5915] uppercase tracking-wider"
                  >
                    qtd
                  </th>
                  <!-- Título da coluna, que muda dinamicamente -->

                  <th
                    class="px-6 py-2 text-[15px] font-semibold text-[#1d5915] uppercase tracking-wider TrRedonDireita"
                  >
                    {{
                      produto.unidadeDeMedida === 'unitario'
                        ? 'v. unit'
                        : 'v. kg'
                    }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="lote in produto.lotes" :key="lote.id">
                  <td
                    class="px-6 py-2 text-[16px] text-gray-800 font-semibold text-center"
                  >
                    {{ lote.data }}
                  </td>
                  <td
                    class="px-6 py-2 text-[16px] text-gray-800 font-semibold text-center"
                  >
                    {{ lote.fornecedor }}
                  </td>
                  <td
                    class="px-6 py-2 text-[16px] text-gray-800 font-semibold text-center"
                  >
                    <input
                      v-model="lote.quantidade"
                      type="number"
                      min="0"
                      class="border rounded-lg px-2 py-1 w-[75px] h-[32px] text-center"
                      @input="updateLote(lote)"
                    />
                  </td>
                  <td
                    class="px-6 py-2 text-[16px] text-gray-800 font-semibold text-center"
                  >
                    {{ lote.preco_unitario }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Botões -->
          <div class="flex justify-left mt-12">
            <ButtonCancelar text="Voltar" @click="cancelForm" />
          </div>
        </form>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useToast } from 'vue-toastification';
import { defineProps, defineEmits } from 'vue';
import axios from 'axios';
import debounce from 'lodash/debounce'; // Importando debounce do lodash

import ButtonCancelar from '../Button/ButtonCancelar.vue';

const toast = useToast();

const props = defineProps({
  isVisible: {
    type: Boolean,
    required: true,
  },
  produto: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['cancelar']);

const nome = ref('');
const categoria = ref('');
const isLoading = ref(false);
const isConfirmDialogVisible = ref(false);
const motivo = ref('');

// Atualiza os campos quando o produto é alterado
watch(
  () => props.produto,
  (novoProduto) => {
    if (novoProduto) {
      nome.value = novoProduto.nome || '';
      categoria.value = novoProduto.categoria || '';
    }
  },
  { immediate: true }
);

// Método para gerar a URL correta da imagem
const getProfilePhotoUrl = (profilePhoto) => {
  if (!profilePhoto) {
    return '/storage/images/no_imagem.svg'; // Caminho para imagem padrão
  }
  return new URL(profilePhoto, window.location.origin).href;
};

// Atualiza o lote com debounce para evitar múltiplas requisições seguidas
const updateLote = debounce((lote) => {
  axios
    .put(`/api/estoque/estoque/lote/${lote.id}`, {
      quantidade: lote.quantidade,
    })
    .then((response) => {
      toast.success('Quantidade do lote atualizada!');
    })
    .catch((error) => {
      toast.error('Erro ao atualizar o lote.');
    });
}, 1000); // 1000ms = 1 segundo de delay

const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmDialogVisible.value = true; // Exibe o diálogo de confirmação
};

const cancelForm = () => {
  emit('cancelar');
};
</script>

<style scoped>
/* Estilizando a tabela */
table {
  width: 100%;
  margin-top: 20px;

  border-collapse: collapse;
}

th,
td {
  padding: 12px;
}

th {
  background-color: #d2fac3;
  color: #262a27;
  margin-bottom: 10px;
}

.TrRedonEsquerda {
  border-radius: 20px 0px 0px 0px;
}

.TrRedonDireita {
  border-radius: 0px 20px 0px 0px;
}

tr:nth-child(even) {
  background-color: #f4f5f3;
}

tr:hover {
  background-color: #dededea9;
  cursor: pointer;
}
/* Customiza as opções de rádio */
.w-5 {
  width: 1.25rem;
}
.h-5 {
  height: 1.25rem;
}
.rounded-full {
  border-radius: 50%;
}
.border-2 {
  border-width: 2px;
}
.bg-green-500 {
  background-color: #22c55e; /* Verde */
}
/* Estilos mantidos */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #6db631;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
