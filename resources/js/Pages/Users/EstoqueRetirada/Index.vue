<template>
  <LayoutFranqueadoEstoque>
    <!-- Cabeçalho da página -->
    <Head title="Controle de Estoque" />

    <div class="grid">
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
              Estoque
            </th>
            <th
              class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider TrRedonDireita"
            >
              Retirada
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <!-- Nome do Item -->
            <td class="text-[16px] text-left text-gray-900 font-semibold">
              Arroz
            </td>

            <!-- Estoque -->
            <td class="text-[16px] text-gray-900 font-semibold text-center">
              20
            </td>

            <!-- Retirada -->
            <td class="text-[16px] text-gray-900 font-semibold text-center">
              <div class="relative w-[124.86px] h-[56.76px] mx-auto">
                <div
                  class="absolute w-[111.24px] h-[56.76px] left-[6.81px] top-0 bg-[#e5e5ea] rounded-xl"
                ></div>
                <div
                  class="absolute w-[45.41px] h-[56.76px] left-[124.86px] top-[57px] origin-top-left rotate-180 bg-[#efeff4] rounded-tl-xl rounded-bl-xl"
                ></div>
                <div
                  class="absolute w-[45.41px] h-[56.76px] left-0 top-0 bg-[#efeff4] rounded-tl-xl rounded-bl-xl"
                ></div>
                <div
                  class="absolute w-[9.08px] h-[26.11px] left-[57.89px] top-[17.03px] text-center text-[#6d6d6d] text-xl font-extrabold font-['Figtree'] uppercase leading-relaxed"
                >
                  1
                </div>
                <img
                  class="absolute w-[16.42px] h-[16.42px] left-[94.22px] top-[20.43px]"
                  src="/storage/images/Plus_verde.svg"
                />
                <div
                  class="absolute w-[16.42px] h-[2.05px] left-[14.76px] top-[27.24px] bg-[#ff3b30] rounded"
                ></div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </LayoutFranqueadoEstoque>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import LayoutFranqueadoEstoque from '@/Layouts/LayoutFranqueadoEstoque.vue';
</script>

<style lang="css" scoped>
/* Estilizando a tabela */
table {
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
</style>
