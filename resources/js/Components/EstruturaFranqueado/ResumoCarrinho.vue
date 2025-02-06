<template>
  <div class="flex gap-5">
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
    </div>
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
              class="px-6 py-4 text-[16px] text-left text-gray-900 font-semibold"
            >
              {{ item.nome }}
            </td>

            <!-- Quantidade -->
            <td
              class="px-6 py-4 text-[16px] text-gray-900 font-semibold text-center"
            >
              <input
                v-if="indexEditavel === index"
                type="number"
                v-model.number="item.quantidade"
                class="border border-gray-300 rounded px-2 py-1 w-16"
              />
              <span v-else>
                {{ item.quantidade }}
                {{ item.unidadeDeMedida === 'a_granel' ? 'KG' : 'UN' }}
              </span>
            </td>

            <!-- Valor Unitário ou Valor por Quilo -->
            <td
              class="px-6 py-4 text-[16px] text-gray-900 font-semibold text-left"
            >
              <span v-if="item.unidadeDeMedida === 'a_granel'">
                R$ {{ valorPorQuilo(item).toFixed(2) }}
              </span>
              <input
                v-else-if="indexEditavel === index"
                type="text"
                v-model="item.valor"
                class="border border-gray-300 rounded px-2 py-1 w-20"
              />
              <span v-else>
                {{ item.valor }}
              </span>
            </td>

            <!-- Valor Total -->
            <td class="px-6 py-4 text-[16px] text-gray-500 text-center">
              <input
                v-if="
                  indexEditavel === index && item.unidadeDeMedida === 'a_granel'
                "
                type="number"
                v-model.number="item.valorTotal"
                class="border border-gray-300 rounded px-2 py-1 w-20"
              />
              <span v-else>
                {{
                  calcularTotal(item).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL',
                  })
                }}
              </span>
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
        </tbody>
      </table>

      <div v-if="!isSending" class="botao-container">
        <ButtonCancelar text="Voltar" @click="cancelarResumo" />
        <ButtonPrimaryMedio text="Confirmar" @click="enviarEntrada" />
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

const removerItem = (index) => {
  props.carrinho.splice(index, 1); // Remove o item do carrinho
  toast.info('Item removido com sucesso!');
};

const ativarEdicao = (index) => {
  indexEditavel.value = index;
};

const valorPorQuilo = (item) => {
  // Remove "R$" e formata o valor corretamente para float
  const valorTotal = parseFloat(
    item.valor.replace('R$', '').replace(/\./g, '').replace(',', '.')
  );

  // Converte a quantidade para um número decimal correto
  const quantidade = parseFloat(
    item.quantidade.replace(/\./g, '').replace(',', '.')
  );

  // Evita divisão por zero
  return quantidade > 0 ? valorTotal / quantidade : 0;
};

const calcularTotal = (item) => {
  // Converte a quantidade para número decimal
  const quantidade = parseFloat(
    item.quantidade.replace(/\./g, '').replace(',', '.')
  );

  if (item.unidadeDeMedida === 'a_granel' || item.unidadeDeMedida === 'quilo') {
    const valorPorKG = valorPorQuilo(item);
    return valorPorKG * quantidade || 0;
  } else {
    // Para itens por unidade, calcula normalmente
    const valorUnitario = parseFloat(
      item.valor.replace('R$', '').replace(/\./g, '').replace(',', '.')
    );
    return quantidade * valorUnitario;
  }
};

const enviarEntrada = async () => {
  isLoading.value = true;
  isSending.value = true;

  try {
    // Organizando os dados para enviar
    const dadosEntrada = {
      fornecedor_id: props.carrinho[0]?.fornecedor_id || null,
      itens: props.carrinho.map((item) => {
        // Converter a quantidade para número decimal corretamente (remover formatação) SOMENTE NA HORA DE ENVIAR
        const quantidadeDecimal = parseFloat(
          item.quantidade.replace(/\./g, '').replace(',', '.')
        );

        // Ajustando o valor unitário para decimal corretamente (remover formatação) SOMENTE NA HORA DE ENVIAR
        let valorUnitario = parseFloat(
          item.valor.replace('R$', '').replace(/\./g, '').replace(',', '.')
        );

        // Garantir que o valor unitário tenha 2 casas decimais
        valorUnitario = valorUnitario.toFixed(2);

        // Calcular o total com a função ajustada
        const total = parseFloat(calcularTotal(item).toFixed(2));

        // Verifique se a quantidade, valorUnitario e total estão corretos
        console.log('Quantidade:', quantidadeDecimal);
        console.log('Valor Unitário:', valorUnitario);
        console.log('Total:', total);

        return {
          id: item.id,
          nome: item.nome,
          quantidade: quantidadeDecimal, // Envia a quantidade como número decimal
          unidadeDeMedida: item.unidadeDeMedida,
          valorUnitario: valorUnitario, // Envia o valor unitário como número decimal
          total: total, // Total também em formato float
        };
      }),
    };

    console.log('Dados enviados:', JSON.stringify(dadosEntrada, null, 2));

    // Enviar os dados para o backend
    const response = await axios.post(
      '/api/estoque/armazenar-entrada',
      dadosEntrada
    );

    // Verifique a resposta da API antes de continuar
    if (response.status === 201) {
      console.log('Resposta da API:', response.data);

      // Redirecionando para a rota de inventário
      Inertia.visit(route('franqueado.inventario'));

      // Notificando o sucesso
      toast.success('Lista de insumos salvas em seu estoque.');
    } else {
      toast.error('Erro ao salvar no estoque, tente novamente.');
    }
  } catch (error) {
    console.error('Erro ao enviar entrada:', error);
    toast.error(
      'Erro ao confirmar entrada. Tente novamente ou chame o suporte.'
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
