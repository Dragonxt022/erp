<template>
  <LayoutFranqueado>
    <Head title="Painel" />
    <div class="painel-title">DRE Gerencial</div>
    <div class="painel-subtitle">
      <p>Acompanhe a saúde da sua operação</p>
    </div>

    <!-- Conteúdo da página -->
    <div class="mt-5 col-span-3">12-02-2025</div>
    <div class="mt-5">
      <div class="grid grid-cols-2 grid-rows-1 gap-4">
        <!-- Segunda coluna -->
        <div class="bg-white rounded-lg p-2">
          <table
            class="w-full text-left text-[14px] border-collapse font-['Figtree']"
          >
            <!-- Faturamento -->
            <thead>
              <tr class="bg-[#174111] text-white">
                <th colspan="2" class="p-1">Faturamento do Período</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-1">Faturamento do Período</td>
                <td class="px-2 py-1 text-right">R$ 0,00</td>
              </tr>
            </tbody>

            <!-- Custos Fixos -->
            <thead>
              <tr class="bg-[#174111] text-white">
                <th colspan="2" class="p-1">Custos Fixos</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-1">Faturamento do Período</td>
                <td class="px-2 py-1 text-right">R$ 0,00</td>
              </tr>
              <tr>
                <td class="p-1">Folha de Pagamento</td>
                <td class="px-2 py-1 text-right">R$ 0,00</td>
              </tr>
              <tr>
                <td class="p-1">Internet</td>
                <td class="px-2 py-1 text-right">R$ 0,00</td>
              </tr>
              <tr>
                <td class="p-1">Energia</td>
                <td class="px-2 py-1 text-right">R$ 0,00</td>
              </tr>
              <tr>
                <td class="p-1">Aluguel</td>
                <td class="px-2 py-1 text-right">R$ 0,00</td>
              </tr>
            </tbody>

            <!-- CMV -->
            <thead>
              <tr class="bg-[#174111] text-white">
                <th colspan="2" class="p-1">CMV</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-1">Mercadoria Vendida</td>
                <td class="px-2 py-1 text-right">R$ 0,00</td>
              </tr>
              <tr>
                <td class="p-1">Frete Fornecedores</td>
                <td class="px-2 py-1 text-right">R$ 0,00</td>
              </tr>
            </tbody>

            <!-- Impostos -->
            <thead>
              <tr class="bg-[#174111] text-white">
                <th colspan="2" class="p-1">Impostos</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-1">FGTS</td>
                <td class="px-2 py-1 text-right">R$ 0,00</td>
              </tr>
            </tbody>

            <!-- Variáveis -->
            <thead>
              <tr class="bg-[#174111] text-white">
                <th colspan="2" class="p-1">Variáveis</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-1">Fundo de Propaganda</td>
                <td class="px-2 py-1 text-right">R$ 0,00</td>
              </tr>
            </tbody>

            <!-- Outros Custos -->
            <thead>
              <tr class="bg-[#174111] text-white">
                <th colspan="2" class="p-1">Outros Custos</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-1">Não Especificado</td>
                <td class="px-2 py-1 text-right">R$ 0,00</td>
              </tr>
            </tbody>

            <!-- Resultado do Período -->
            <thead>
              <tr class="bg-[#174111] text-white">
                <th colspan="1" class="p-2">Resultado do Período</th>
                <th colspan="1" class="p-2 text-right font-bold">R$ 0,00</th>
              </tr>
            </thead>
          </table>
        </div>

        <div class="bg-white rounded-lg p-7">
          <div
            class="text-[#262a27] text-[12px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            <!--  -->
          </div>
        </div>

        <!-- Terceira coluna -->
        <div class="bg-white rounded-lg p-7">
          <div
            class="text-[#262a27] text-[12px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            <!--  -->
          </div>
        </div>
        <div class="bg-white rounded-lg p-4">
          <div
            class="text-[#262a27] text-[12px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            <!--  -->
          </div>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
//
</script>

<style lang="css" scoped>
.painel-title {
  font-size: 34px;
  line-height: 15px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  margin-bottom: 10px; /* Espaçamento inferior */
}

.painel-subtitle {
  font-size: 17px;
  line-height: 25px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}
</style>
