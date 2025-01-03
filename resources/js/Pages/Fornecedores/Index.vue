<template>
  <AppLayout>
    <!-- Cabeçalho da página -->
    <Head title="Fornecedores" />

    <!-- Container principal da grade com 2 colunas -->
    <div class="grid grid-cols-1 gap-4 mt-3 sm:grid-cols-2">
      <div>
        <ListarFornecedores
          ref="listarUnidades"
          @unidade-cadastrada="fetchUnidades"
          @usuario-selecionado="usuarioSelecionado"
        />
      </div>

      <!-- Coluna 2: Alternar entre Detalhes e Cadastro -->
      <div class="flex flex-col gap-4">
        <template v-if="!showCadastroUsuario">
          <!-- Passa os dados do usuário selecionado apenas se existirem -->
          <template v-if="usuarioSelecionada">
            <DetalhesUsuario :usuario="usuarioSelecionada" />
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
              :isVisible="showCadastroUsuario"
              @unidade-cadastrada="handleCadastro"
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
import DetalhesUsuario from '@/Components/Estrutura/DetalhesUsuario.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import CadastroFornecedor from '@/Components/Estrutura/CadastroFornecedor.vue';
import ListarFornecedores from '@/Components/Estrutura/ListarFornecedores.vue';

// Dados do usuário selecionado
const usuarioSelecionada = ref(null);

const showCadastroUsuario = ref(false);

// Alterna a visibilidade entre Cadastro e Detalhes
const toggleCadastro = () => {
  showCadastroUsuario.value = !showCadastroUsuario.value;
};

// Atualiza a lista de unidades após o cadastro
const handleCadastro = () => {
  fetchUnidades();
  showCadastroUsuario.value = false;
};

// Atualiza a lista de unidades
const fetchUnidades = () => {
  const listarUnidades = ref('listarUnidades');
  listarUnidades.value?.fetchUnidades();
};

// Define os dados da unidade selecionada
const usuarioSelecionado = (usuario) => {
  if (usuario && typeof usuario === 'object') {
    usuarioSelecionada.value = usuario;
  } else {
    console.warn(
      'Dados inválidos recebidos no evento usuario-selecionado:',
      usuario
    );
  }
};
</script>
