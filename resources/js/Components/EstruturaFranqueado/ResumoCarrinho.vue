<template>
  <div class="flex gap-5">
    <!-- Coluna da esquerda -->
    <div class="flex-1">
      <!-- Título principal -->
      <div class="painel-title">Confirmar entrada</div>

      <!-- Subtítulo da página -->
      <div class="painel-subtitle">
        <p>Vamos conferir se está tudo certo</p>
      </div>

      <div
        class="mt-[25%] text-[#6d6d6d] text-[18px] font-normal font-['Figtree'] leading-[23px] tracking-tight"
      >
        Você confirma que está
        <br />
        realizando uma nova entrada
        <br />
        com os seguintes itens?
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
              class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider TrRedonDireita"
            >
              TOTAL
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in carrinho" :key="item.id">
            <td
              class="px-6 py-4 text-[16px] text-left text-gray-900 font-semibold"
            >
              {{ item.nome }}
            </td>
            <td
              class="px-6 py-4 text-[16px] text-gray-900 font-semibold text-center"
            >
              {{ item.quantidade }}
            </td>
            <td
              class="px-6 py-4 text-[16px] text-gray-900 font-semibold text-left"
            >
              {{
                item.unidadeDeMedida === 'a_granel'
                  ? `${valorPorQuilo(item).toFixed(2)} /kg`
                  : item.valor
              }}
            </td>
            <td class="px-6 py-4 text-[16px] text-gray-500 text-center">
              {{
                calcularTotal(item).toLocaleString('pt-BR', {
                  style: 'currency',
                  currency: 'BRL',
                })
              }}
            </td>
          </tr>
        </tbody>
      </table>

      <div class="botao-container">
        <ButtonCancelar text="Cancelar" @click="cancelarResumo" />
        <ButtonPrimaryMedio text="Confirmar" @click="enviarEntrada" />
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import ButtonCancelar from '../Button/ButtonCancelar.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import { Inertia } from '@inertiajs/inertia';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
  carrinho: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['confirmar', 'cancelar']);

// Função para calcular o valor por quilo
const valorPorQuilo = (item) => {
  if (item.unidadeDeMedida === 'a_granel') {
    return (
      parseFloat(item.valor.replace('R$', '').replace(',', '.')) /
      item.quantidade
    );
  }
  return parseFloat(item.valor.replace('R$', '').replace(',', '.'));
};

// Função para calcular o total
const calcularTotal = (item) => {
  return item.unidadeDeMedida === 'a_granel'
    ? valorPorQuilo(item) * item.quantidade
    : item.quantidade *
        parseFloat(item.valor.replace('R$', '').replace(',', '.'));
};

// Função para enviar os dados ao controlador
const enviarEntrada = async () => {
  try {
    // Acessar a prop `carrinho` diretamente
    const dadosEntrada = {
      fornecedor_id: props.carrinho[0]?.fornecedor_id || null, // Usar `props.carrinho` diretamente
      itens: props.carrinho.map((item) => ({
        id: item.id,
        nome: item.nome,
        quantidade: item.quantidade,
        unidadeDeMedida: item.unidadeDeMedida,
        valorUnitario: Math.round(
          parseFloat(item.valor.replace('R$', '').replace(',', '.')) * 100
        ), // Convertendo para centavos
        total: Math.round(calcularTotal(item) * 100), // Convertendo para centavos
      })),
    };

    console.log('Dados enviados:', JSON.stringify(dadosEntrada, null, 2));

    // Envio para o backend
    const response = await axios.post(
      '/api/estoque/armazenar-entrada',
      dadosEntrada
    );
    console.log('Resposta da API:', response.data);
    Inertia.visit(route('franqueado.inventario'));
    toast.success('Lista de insumos salvas em seu estoque.');
    // emit('confirmar'); // Emite evento de confirmação
  } catch (error) {
    toast.error('Ouve um erro, tente novamente ou chame o suporte.');
    console.error(
      'Erro ao enviar entrada:',
      error.response?.data || error.message
    );
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
  color: #6db631; /* Cor escura para título */
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
</style>
