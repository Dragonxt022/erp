<template>
  <AppLayout>
    <!-- Cabeçalho da página -->
    <Head title="Fornecedores" />

    <!-- Container principal da grade com 2 colunas -->
    <div class="grid grid-cols-1 gap-4 mt-3 sm:grid-cols-2">
      <div>
        <!-- Componente de listagem de fornecedores -->
        <ListarFornecedores
          ref="listarFornecedores"
          @fornecedor-cadastrado="fetchFornecedores"
          @fornecedor-selecionado="fornecedorSelecionadoHandler"
        />
      </div>

      <!-- Coluna 2: Alternar entre Detalhes e Cadastro -->
      <div class="flex flex-col gap-4">
        <template v-if="!showCadastroFornecedor">
          <!-- Passa os dados do fornecedor selecionado apenas se existirem -->
          <template v-if="fornecedorSelecionado">
            <DetalhesFornecedor :fornecedor="fornecedorSelecionado" />
          </template>

          <div class="absolute bottom-4 right-4">
            <ButtonPrimaryMedio
              text="Novo Fornecedor"
              iconPath="/storage/images/arrow_left_alt.svg"
              @click="toggleCadastro"
            />
          </div>
        </template>
        <template v-else>
          <div class="mt-4">
            <CadastroFornecedor
              :isVisible="showCadastroFornecedor"
              @fornecedor-cadastrado="handleCadastro"
              @cancelar="toggleCadastro"
            />
          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';

import AppLayout from '@/Layouts/LayoutFranqueadora.vue';
import { Head } from '@inertiajs/vue3';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import ListarFornecedores from '@/Components/EstruturaFranqueadora/ListarFornecedores.vue';
import DetalhesFornecedor from '@/Components/EstruturaFranqueadora/DetalhesFornecedor.vue';
import CadastroFornecedor from '@/Components/EstruturaFranqueadora/CadastroFornecedor.vue';

// Dados do fornecedor selecionado
const fornecedorSelecionado = ref(null);

const showCadastroFornecedor = ref(false);

// Alterna a visibilidade entre Cadastro e Detalhes
const toggleCadastro = () => {
  showCadastroFornecedor.value = !showCadastroFornecedor.value;
};

// Atualiza a lista de fornecedores após o cadastro
const handleCadastro = () => {
  fetchFornecedores();
  showCadastroFornecedor.value = false;
};

// Atualiza a lista de fornecedores
const fetchFornecedores = () => {
  const listarFornecedores = ref('listarFornecedores');
  listarFornecedores.value?.fetchFornecedores();
};

// Define os dados do fornecedor selecionado
const fornecedorSelecionadoHandler = (fornecedor) => {
  if (fornecedor && typeof fornecedor === 'object') {
    fornecedorSelecionado.value = fornecedor;
  } else {
    console.warn(
      'Dados inválidos recebidos no evento fornecedor-selecionado:',
      fornecedor
    );
  }
};
</script>
