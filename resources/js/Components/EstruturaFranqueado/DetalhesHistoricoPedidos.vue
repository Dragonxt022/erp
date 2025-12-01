<template>
  <div class="elemento-fixo">
    <!-- Tabela de Lotes -->
    <div v-if="!isEditMode">
      <div class="mt-8">
        <div
          class="flex justify-between w-full h-[24.32px] text-[#262a27] text-[42px] font-semibold font-['Figtree'] leading-[12px]"
        >
          <p># {{ dados.id }}</p>
          <p
            class="text-[#6d6d6d] text-[15px] font-medium font-['Figtree'] leading-[20px]"
          ></p>
        </div>

        <div
          class="flex justify-between w-full h-[17.68px] text-[#6d6d6d] text-[15px] font-medium font-['Figtree'] leading-[20px]"
        >
          <!-- Coluna do fornecedor -->
          <div class="w-[250px]">
            {{ dados.fornecedor_nome }}
          </div>
          <!-- Coluna da data -->
          <div class="w-[250px] text-right font-semibold">
            {{ dados.created_at }}
          </div>
        </div>

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
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, index) in props.dados.itens_id"
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
                <span>
                  {{ formatQuantidade(item.quantidade, item.unidadeDeMedida) }}
                  {{ item.unidadeDeMedida === 'a_granel' ? 'KG' : 'UN' }}
                </span>
              </td>

              <!-- Valor Unitário ou Valor por Quilo -->
              <td
                class="px-6 py-4 text-[16px] text-gray-900 font-semibold text-center"
              >
                <span v-if="item.unidadeDeMedida === 'a_granel'">
                  R$ {{ valorPorQuilo(item).toFixed(2) }}
                </span>
                <span v-else>
                  <!-- Verificar se o valor unitário é um array e somá-los se necessário -->
                  R$
                  {{
                    Array.isArray(item.valor_unitario)
                      ? item.valor_unitario
                          .reduce((acc, val) => acc + val, 0)
                          .toFixed(2)
                      : item.valor_unitario.toFixed(2)
                  }}
                </span>
              </td>

              <!-- Valor Total -->
              <td class="px-6 py-4 text-[16px] text-gray-500 text-center">
                <span>
                  {{
                    calcularTotal(item).toLocaleString('pt-BR', {
                      style: 'currency',
                      currency: 'BRL',
                    })
                  }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref } from 'vue';
import { defineEmits } from 'vue';

const props = defineProps({
  dados: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['atualizar']);

const isEditMode = ref(false);
const indexEditavel = ref(null);

// Função para ativar o modo de edição
const ativarEdicao = (index) => {
  indexEditavel.value = index;
};

// Função para calcular o valor total do item
const calcularTotal = (item) => {
  // Se 'valor_unitario' for um array, calcula a soma de todos os valores
  const valorUnitario = Array.isArray(item.valor_unitario)
    ? item.valor_unitario.reduce((acc, val) => acc + val, 0)
    : parseFloat(item.valor_unitario);

  // Se 'quantidade' for um array, calcula a soma de todas as quantidades
  const quantidade = Array.isArray(item.quantidade)
    ? item.quantidade.reduce((acc, val) => acc + val, 0)
    : parseFloat(item.quantidade);

  // Verifica se os valores são válidos antes de calcular
  if (isNaN(valorUnitario) || isNaN(quantidade)) {
    return 0; // Caso os valores não sejam numéricos, retorna 0
  }

  return valorUnitario * quantidade;
};

// Função para calcular o valor por quilo (para a_granel) ou por unidade (para unitario)
// Neste caso, o valor_unitario já representa o preço correto
const valorPorQuilo = (item) => {
  return parseFloat(item.valor_unitario);
};

// Função para formatar a quantidade conforme a unidade de medida
const formatQuantidade = (quantidade, unidade) => {
  // Se quantidade for um array, soma seus valores; caso contrário, converte para número
  const total = Array.isArray(quantidade)
    ? quantidade.reduce((acc, val) => acc + val, 0)
    : parseFloat(quantidade) || 0;

  if (unidade === 'a_granel') {
    // Formata com 3 casas decimais, padrão brasileiro
    return Number(total).toLocaleString('pt-BR', {
      minimumFractionDigits: 3,
      maximumFractionDigits: 3,
    });
  } else {
    // Para unitário, formata como número inteiro
    return Number(total).toLocaleString('pt-BR', {
      maximumFractionDigits: 0,
    });
  }
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
  background-color: #164110;
  color: #ffff;
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
