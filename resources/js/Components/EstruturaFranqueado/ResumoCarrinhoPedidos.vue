<template>
  <div class="flex gap-5">
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
    </div>
    <!-- Coluna da esquerda -->
    <div class="flex-1">
      <!-- Título principal -->
      <div class="painel-title">Confirmar pedido</div>

      <!-- Subtítulo da página -->
      <div class="painel-subtitle">
        <p>Verifique seu pedido antes de enviar</p>
      </div>

      <div
        class="mt-[20%] text-[#6d6d6d] text-[18px] font-normal font-['Figtree'] leading-[23px] tracking-tight"
      >
        <p>
          Verifique se o seu pedido
          <br />
          está correto antes de enviar
          <br />
          para o fornecedor
        </p>

        <span
          class="text-[#6db631] text-xl font-bold font-['Figtree'] leading-[25px] tracking-tight mt-12"
        >
          {{ nomePrimeiroFornecedor || 'Fornecedor desconhecido' }}.
        </span>
      </div>
      <div
        class="mt-[20%] text-[#ff2d55] text-[15px] font-normal font-['Figtree'] leading-[18px] tracking-tight"
      >
        <p>
          * valor atualizado semanalmente,
          <br />
          podendo sofrer alterações, confirme
          <br />
          com o fornecedor no ato do envio do pedido.
        </p>
      </div>
    </div>

    <!-- Coluna da direita -->
    <div class="flex-1">
      <table class="min-w-full table-auto">
        <thead>
          <tr>
            <th
              class="px-6 py-3 text-xs text-left font-medium text-gray-500 uppercase tracking-wider TrRedonEsquerda"
            >
              Item
            </th>
            <th
              class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              QTD.
            </th>
            <th
              class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              V. UN/KG
            </th>
            <th
              class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              TOTAL
            </th>
            <th
              class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider TrRedonDireita"
            >
              AÇÃO
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(item, index) in carrinho"
            :key="item.id"
            @dblclick="ativarEdicao(index)"
          >
            <!-- Nome do Item -->
            <td
              class="px-6 py-4 text-[16px] text-left text-gray-500 font-semibold"
            >
              {{ item.nome }}
            </td>

            <!-- Quantidade -->
            <td
              class="px-6 py-4 text-[16px] text-gray-500 font-semibold text-center"
            >
              <input
                v-if="indexEditavel === index"
                type="number"
                v-model.number="item.quantidade"
                class="border border-gray-300 rounded px-2 py-1 w-16"
              />
              <span v-else>
                {{ formatarQuantidade(item) }}
              </span>
            </td>

            <!-- Valor Unitário ou Valor por Quilo -->
            <td
              class="px-6 py-4 text-[16px] text-gray-500 font-semibold text-left"
            >
              R$
              {{
                valorPorQuilo(item).toLocaleString('pt-BR', {
                  minimumFractionDigits: 2,
                })
              }}
            </td>

            <!-- Valor Total -->
            <td
              class="px-6 py-4 text-[16px] text-gray-500 text-center font-semibold"
            >
              R$
              {{
                calcularTotal(item).toLocaleString('pt-BR', {
                  minimumFractionDigits: 2,
                })
              }}
            </td>
            <td class="px-6 py-4 text-center">
              <button @click="removerItem(index)" class="w-[16] h-[16]">
                <img
                  src="/storage/images/delete.svg"
                  alt="icone de excluir"
                  class="w-[16px] h-[16px]"
                />
              </button>
            </td>
          </tr>
          <!-- Linha Total -->
          <tr class="bg-[#e4f6de]">
            <td
              class="px-6 py-8 text-[16px] text-left text-gray-500 font-semibold"
              colspan="3"
            >
              Total do Pedido
            </td>
            <td
              class="px-6 py-4 text-[16px] text-center text-gray-500 font-semibold"
            >
              R$
              {{
                calcularSomaTotal(item).toLocaleString('pt-BR', {
                  minimumFractionDigits: 2,
                })
              }}
            </td>
            <td></td>
          </tr>
        </tbody>
      </table>

      <div v-if="!isSending" class="botao-container">
        <ButtonCancelar text="Voltar" @click="cancelarResumo" />
        <ButtonPrimaryMedio text="Confirmar" @click="enviarPedido" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import { Inertia } from '@inertiajs/inertia';
import ButtonCancelar from '../Button/ButtonCancelar.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';

const toast = useToast();

const props = defineProps({
  carrinho: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['confirmar', 'cancelar']);

const isLoading = ref(false);
const isSending = ref(false);

const indexEditavel = ref(null);

const nomePrimeiroFornecedor =
  props.carrinho[0]?.nomeFornecedor || 'Sem fornecedor';

const removerItem = (index) => {
  props.carrinho.splice(index, 1); // Remove o item do carrinho
  toast.info('Item removido com sucesso!');
};

const ativarEdicao = (index) => {
  indexEditavel.value = index;
};

const formatarQuantidade = (item) => {
  const quantidade = Number(item.quantidade) || 0;

  return item.unidadeDeMedida === 'a_granel'
    ? quantidade.toLocaleString('pt-BR', {
        minimumFractionDigits: 3,
        maximumFractionDigits: 3,
      }) + ' KG'
    : quantidade.toLocaleString('pt-BR', { maximumFractionDigits: 0 }) + ' UN';
};

const valorPorQuilo = (item) => {
  const preco = Number(item.preco) || 0;

  return preco.toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const calcularTotal = (item) => {
  return (item.quantidade || 0) * (item.preco || 0);
};

// Função para calcular a soma total do Carrinho
const calcularSomaTotal = () => {
  return props.carrinho.reduce((total, item) => {
    return total + (item.quantidade || 0) * (item.preco || 0);
  }, 0);
};

const enviarPedido = async () => {
  isLoading.value = true;
  isSending.value = false;
  try {
    // Preparando os dados que serão enviados para a API
    const dadosEntrada = {
      itens: props.carrinho.map((item) => ({
        fornecedor_id: item.fornecedorId,
        id: item.id,
        nome: item.nome,
        nomeFornecedor: item.nomeFornecedor,
        quantidade: parseFloat(item.quantidade),
        valor_unitario: parseFloat(item.preco),
        valor_total_item: calcularTotal(item),
        unidadeDeMedida: item.unidadeDeMedida,
      })),
      valor_total_carrinho: parseFloat(calcularSomaTotal()),
      nomePrimeiroFornecedor: nomePrimeiroFornecedor,
    };

    console.log('Dados a ser enviado:', JSON.stringify(dadosEntrada, null, 2));

    // Enviar os dados
    const response = await axios.post(
      '/api/estoque/criar-pedido',
      dadosEntrada
    );

    // Verificar a resposta da API
    if (response.status === 201) {
      console.log('Resposta da API:', response.data);

      // Exibindo notificação de sucesso
      toast.success('Lista de itens está sendo enviada.');
      // Redirecionando para a página de inventário após sucesso
      Inertia.visit(route('franqueado.inventario'));
    } else {
      // Caso a resposta da API não seja bem-sucedida
      toast.error('Erro ao enviar os itens.');
    }
  } catch (error) {
    console.error('Erro ao enviar:', error);
    toast.error(
      'Erro ao confirmar o envio. Tente novamente ou chame o suporte.'
    );
  } finally {
    isLoading.value = false;
  }
};

const cancelarResumo = () => {
  emit('cancelar');
};
</script>

<style scoped>
.botao-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  display: flex;
  gap: 10px;
}

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
  background-color: #164110;
  color: #ffffff;
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
/* fim */
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #414141; /* Cor escura para título */
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}
.resumo-container {
  padding: 20px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Estilos para o spinner */
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
  position: relative; /* Necessário para centralizar a imagem dentro */
  border: 4px solid #f3f3f3; /* Cor de fundo */
  border-top: 4px solid #6db631; /* Cor do topo */
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 2s linear infinite;
}

/* Animação do spinner */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Estilo para a imagem centralizada */
.spinner img {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%); /* Centraliza a imagem */
  width: 32px;
  height: 32px;
}
</style>
