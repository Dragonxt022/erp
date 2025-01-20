<template>
  <LayoutFranqueado>
    <!-- Cabeçalho da página -->
    <Head title="Fornecedores" />

    <!-- Container principal da grade com 2 colunas -->
    <div class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2">
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
        </template>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { ref } from 'vue';

import { Head } from '@inertiajs/vue3';
import DetalhesFornecedor from '@/Components/EstruturaFranqueado/DetalhesFornecedor.vue';
import ListarFornecedores from '@/Components/EstruturaFranqueado/ListarFornecedores.vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';

// Dados do fornecedor selecionado
const fornecedorSelecionado = ref(null);
const showCadastroFornecedor = ref(false);

// Atualiza a lista de fornecedores
const fetchFornecedores = () => {
  const listarFornecedores = ref('listarFornecedores');
  listarFornecedores.value?.fetchFornecedores();
};

// Define os dados do fornecedor selecionado
const fornecedorSelecionadoHandler = (fornecedor) => {
  if (fornecedor && typeof fornecedor === 'object') {
    fornecedorSelecionado.value = { ...fornecedor }; // Cria uma nova referência
  } else {
    console.warn(
      'Dados inválidos recebidos no evento fornecedor-selecionado:',
      fornecedor
    );
  }
};
</script>
