<template>
  <AppLayout>
    <!-- Cabeçalho da página -->
    <Head title="Franqueados" />

    <!-- Container principal da grade com 2 colunas -->
    <div class="grid grid-cols-1 gap-4 mt-3 sm:grid-cols-2">
      <!-- Coluna 1: Listar Unidades -->
      <div>
        <ListarUsuarios
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
              text="Novo Franqueado"
              iconPath="/storage/images/arrow_left_alt.svg"
              @click="toggleCadastro"
            />
          </div>
        </template>
        <template v-else>
          <div class="mt-4">
            <CadastroFranqueado
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
import AppLayout from '@/Layouts/LayoutFranqueadora.vue';
import { Head } from '@inertiajs/vue3';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import ListarUsuarios from '@/Components/EstruturaFranqueadora/ListarUsuarios.vue';
import DetalhesUsuario from '@/Components/EstruturaFranqueadora/DetalhesUsuario.vue';
import CadastroFranqueado from '@/Components/EstruturaFranqueadora/CadastroFranqueado.vue';

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
