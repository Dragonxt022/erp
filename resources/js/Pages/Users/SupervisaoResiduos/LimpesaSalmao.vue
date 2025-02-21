<template>
  <LayoutFranqueado>
    <Head title="Limpeza de Salmão" />
    <div class="flex justify-between items-center mb-4">
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
          Limpeza de Salmão
        </div>
        <div class="painel-subtitle">
          <p class="text-sm sm:text-base md:text-lg">
            Registre o aproveitamento de salmão
          </p>
        </div>
      </div>
    </div>

    <div class="mt-5">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-lg px-12 py-5">
          <div class="flex flex-col flex-1">
            <LabelModel text="Responsável" class="mb-2" />
            <select v-model="selectedResponsavel" class="input-select">
              <option value="" disabled selected>
                Selecione um Responsável
              </option>
              <option
                v-for="responsavel in usuariosResponsaves"
                :key="responsavel"
                :value="responsavel"
              >
                {{ responsavel }}
              </option>
            </select>

            <LabelModel text="Calibre do Salmão" class="mb-2" />
            <select v-model="selectedCalibre" class="input-select">
              <option value="" disabled selected>Selecione um calibre</option>
              <option
                v-for="calibre in calibres"
                :key="calibre"
                :value="calibre"
              >
                {{ calibre }}
              </option>
            </select>
          </div>
        </div>

        <div class="bg-white rounded-lg px-12 py-5">
          <LabelModel text="Aproveitamento" />
          <div class="flex items-center -mt-9">
            <span
              class="font-bold text-[120.01px] text-[#1d5915] tracking-wider"
            >
              72%
            </span>
            <svg
              class="w-[40px] h-[40px] ml-2"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <polygon points="12,2 22,20 2,20" fill="#6DB631" />
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-lg px-12 py-5">
          <div class="mt-4 flex flex-col gap-3">
            <!-- Valor Pago -->
            <div class="flex justify-between items-center px-4 py-2 rounded-lg">
              <LabelModel text="Valor Pago" class="text-gray-800" />
              <div class="relative flex items-center w-full">
                <input
                  v-model="valor_pago"
                  @input="formatarValor"
                  class="input-text"
                  placeholder="R$ 0,00"
                />
              </div>
            </div>

            <!-- Peso Bruto -->
            <div class="flex justify-between items-center px-4 py-2 rounded-lg">
              <LabelModel text="Peso Bruto" class="text-gray-800" />
              <input
                v-model="peso_bruto"
                @input="formatarPesoBruto"
                class="input-text"
                placeholder="0,000"
              />
            </div>

            <!-- Peso Limpo -->
            <div class="flex justify-between items-center px-4 py-2 rounded-lg">
              <LabelModel text="Peso Limpo" class="text-gray-800" />
              <input
                v-model="peso_limpo"
                @input="formatarPesoLimpo"
                class="input-text"
                placeholder="0,000"
              />
            </div>
          </div>
        </div>

        <div class="rounded-lg px-12 py-5">
          <LabelModel text="Desperdício" />
          <div class="flex items-center -mt-5">
            <span
              class="font-bold text-[63.36px] text-[#424242] tracking-wider"
            >
              7.828 kg
            </span>
          </div>
          <div
            class="flex items-center gap-2 text-[#6d6d6d] text-[15px] font-semibold -mt-4"
          >
            <p>Equivalente a R$ 350,22</p>
          </div>
        </div>
      </div>
      <div class="absolute bottom-4 right-4 botao-container">
        <ButtonPrimaryMedio
          text="Concluir"
          iconPath="/storage/images/arrow_left_alt.svg"
          @click="toggleCadastro"
        />
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { ref } from 'vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
import CalendarSimples from '@/Components/Filtros/CalendarSimples.vue';
import LabelModel from '@/Components/Label/LabelModel.vue';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';

const selectedResponsavel = ref('');
const usuariosResponsaves = ['Rogerio Silva', 'Rosangela Silva'];

const selectedCalibre = ref('');
const calibres = ['Salmão 10/12', 'Salmão 12/14'];

const valor_pago = ref('');
const peso_bruto = ref('');
const peso_limpo = ref('');

// Função para formatar valores monetários
const formatarValor = () => {
  let numeros = valor_pago.value.replace(/\D/g, ''); // Remove tudo que não for número
  let inteiro = numeros.slice(0, -2) || '0'; // Parte inteira
  let centavos = numeros.slice(-2).padStart(2, '0'); // Parte decimal
  valor_pago.value = `R$ ${Number(inteiro).toLocaleString(
    'pt-BR'
  )},${centavos}`;
};

// Função para formatar pesos (três casas decimais)
const formatarPeso = (campo) => {
  let numeros = campo.value.replace(/[^\d]/g, ''); // Remove caracteres inválidos
  if (numeros.length < 4) {
    campo.value = `0,${numeros.padStart(3, '0')}`; // Adiciona zero antes do decimal
  } else {
    let inteiro = numeros.slice(0, -3);
    let decimal = numeros.slice(-3);
    campo.value = `${Number(inteiro).toLocaleString('pt-BR')},${decimal}`;
  }
};

const formatarPesoBruto = () => formatarPeso(peso_bruto);
const formatarPesoLimpo = () => formatarPeso(peso_limpo);
</script>

<style scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  line-height: 80%;
}

.painel-subtitle {
  font-size: 17px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}

.input-text {
  width: 100%;
  padding: 8px;
  background: transparent;
  border: 1px solid #262a27;
  border-radius: 8px;
  text-align: center;
  font-size: 16px;
  color: #262a27;
  font-family: 'Figtree', sans-serif;
}

.input-select {
  width: 100%;
  height: 44px;
  background: #f3f8f3;
  border: 2px solid #d7d7db;
  padding: 8px;
  font-size: 16px;
  font-weight: bold;
  color: #6db631;
  border-radius: 8px;
  outline: none;
}

.botao-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  display: flex;
  gap: 10px;
}
</style>
