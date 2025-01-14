<template>
  <div class="elemento-fixo">
    <div
      v-if="!isEditMode"
      class="w-full h-[200px] bg-white rounded-[20px] p-12"
    >
      <div class="relative w-full h-full">
        <!-- Container das colunas -->
        <div class="flex items-center">
          <!-- Coluna da Imagem -->
          <div class="w-1/1 flex justify-center">
            <img
              :src="getProfilePhotoUrl(produto.profile_photo)"
              alt="Foto do Usuário"
              class="w-20 h-20 rounded-md shadow-lg"
            />
          </div>

          <!-- Coluna do Nome -->
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

              <!-- <div class="owner">
                {{ produto.valor_total }}
              </div> -->
            </div>
          </div>

          <div class="w-1/1">
            <ConfirmDialog
              :isVisible="isConfirmDialogVisible"
              :motivo="motivo"
              @confirm="handleConfirm"
              @cancel="handleCancel"
            />
            <!-- <div
              class="absolute top-4 right-4 cursor-pointer"
              @click="showConfirmDialog('Excluir esse produto?')"
            >
              <img
                src="/storage/images/delete.svg"
                alt="Deletar Usuário"
                class="w-6 h-6"
              />
            </div> -->
          </div>
        </div>
        <!-- Exibe o botão de edição apenas se uma unidade for selecionada -->
      </div>
    </div>
    <!-- <div v-if="produto.id && !isEditMode" class="mt-4">
      <ButtonEditeMedio
        text="Editar insumos"
        icon-path="/storage/images/border_color.svg"
        @click="toggleEditMode"
        class="px-4 py-2 bg-[#F8F8F8] text-white rounded-lg"
      />
    </div> -->
    <!-- Tabela de Lotes -->
    <div class="mt-8">
      <table class="min-w-full table-auto">
        <thead>
          <tr>
            <th
              class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider TrRedonEsquerda"
            >
              entrada
            </th>
            <th
              class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider"
            >
              Fornecedor
            </th>
            <th
              class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider"
            >
              qtd
            </th>
            <!-- Título da coluna, que muda dinamicamente -->

            <th
              class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider"
            >
              {{ produto.unidadeDeMedida === 'unitario' ? 'v. unit' : 'v. kg' }}
            </th>

            <th
              class="px-6 py-4 text-[15px] font-semibold text-gray-500 uppercase tracking-wider TrRedonDireita"
            >
              total
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(lote, index) in produto.lotes"
            :key="index"
            class="text-center"
          >
            <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
              {{ lote.data }}
            </td>
            <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
              {{ lote.fornecedor }}
            </td>
            <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
              {{ lote.quantidade }}
            </td>

            <!-- Preço por Quilo ou Preço Unitário -->
            <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
              {{
                produto.unidadeDeMedida === 'unitario'
                  ? lote.preco_unitario
                  : lote.valor_pago_por_quilo
              }}
            </td>

            <!-- Valor Total ou Preço Unitário -->
            <td class="px-6 py-4 text-[16px] text-gray-500 font-semibold">
              {{
                produto.unidadeDeMedida === 'unitario'
                  ? lote.valor_total
                  : lote.preco_unitario
              }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <EditarProduto
      v-if="isEditMode"
      ref="dadosProduto"
      :isVisible="isEditMode"
      :produto="produto"
      @dadosProduto="fetchProdutos"
      @cancelar="cancelEdit"
    />
  </div>
</template>

<script setup>
import { defineProps, ref } from 'vue';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';
import { useToast } from 'vue-toastification';
import EditarProduto from './EditarProduto.vue';
import ButtonEditeMedio from '../Button/ButtonEditeMedio.vue';
import ConfirmDialog from '../LaytoutFranqueado/ConfirmDialog.vue';

const toast = useToast();

const props = defineProps({
  produto: {
    type: Object,
    required: true,
  },
});

const isConfirmDialogVisible = ref(false);
const motivo = ref('');
const showCadastroProduto = ref(false);

const isEditMode = ref(false);
const isLoading = ref(false);

const fetchProdutos = () => {
  const dadosProduto = ref.dadosProduto;
  dadosProduto.fetchProduto();
};

const getProfilePhotoUrl = (profilePhoto) => {
  if (!profilePhoto) {
    return '/storage/images/no_imagem.svg'; // Caminho para imagem padrão
  }
  return new URL(profilePhoto, window.location.origin).href;
};

const deleteProduto = async () => {
  try {
    isLoading.value = true;
    const response = await axios.delete(
      `/api/produtos/excluir/${props.produto.id}`
    ); // URL para deletar o produto
    console.log(response.data.message); // Mensagem de sucesso do backend
    toast.success('Produto deletado com sucesso!'); // Exibe um toast de sucesso
    Inertia.visit(route('franqueadora.insumos'));
  } catch (error) {
    toast.error(
      'Erro ao deletar o produto:',
      error.response?.data?.error || error.message
    );
    console.error(
      'Erro ao deletar o produto:',
      error.response?.data?.error || error.message
    );
  } finally {
    isLoading.value = false;
  }
};

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value;
  showCadastroProduto.value = false;
};

const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmDialogVisible.value = true; // Exibe o diálogo de confirmação
};

const handleConfirm = () => {
  deleteProduto();
  isConfirmDialogVisible.value = false;
};

const handleCancel = () => {
  isConfirmDialogVisible.value = false;
};

const cancelEdit = () => {
  isEditMode.value = false;
  showCadastroProduto.value = true;
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
  background-color: #f3f8f3;
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

.elemento-fixo {
  position: -webkit-sticky; /* Para navegadores que exigem o prefixo */
  position: sticky;
  top: 0; /* Defina o valor para o topo de onde o elemento ficará fixo */
  z-index: 10; /* Para garantir que o elemento fique sobre outros */
}
/* Tornando a lista rolável com barra de rolagem invisível */
.scrollbar-hidden::-webkit-scrollbar {
  display: none;
}

.scrollbar-hidden {
  -ms-overflow-style: none; /* Para o IE e Edge */
  scrollbar-width: none; /* Para o Firefox */
}
.owner {
  font-size: 13px;
  font-family: Figtree;
  font-weight: 500;
  line-height: 18px;
  color: #6d6d6e;
}
</style>
