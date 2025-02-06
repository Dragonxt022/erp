<template>
  <div>
    <!-- Título principal -->
    <div class="painel-title">Gestão de equipe</div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Gerencie a sua equipe e permissões aqui</p>
    </div>
    <ButtonPrimaryMedio
      text="Cadastrar colaborador"
      class="w-full"
      @click="toggleCadastro"
    />
    <!-- Lista de usuários -->
    <div
      v-for="usuario in usuarios"
      :key="usuario.id"
      @click="selecionarUsuario(usuario)"
      class="flex items-center bg-white p-4 rounded-lg cursor-pointer hover:bg-gray-100 transition mt-3"
    >
      <!-- Foto do perfil -->
      <img
        :src="usuario.profile_photo_url"
        alt="Foto de perfil"
        class="w-12 h-12 object-cover mr-4"
      />

      <!-- Informações do usuário -->
      <div>
        <p class="text-lg font-medium text-gray-900">{{ usuario.name }}</p>
        <p class="text-sm text-gray-600">{{ usuario.email }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';

const emit = defineEmits(['usuario-selecionado', 'abrir-cadastro']);
const usuarios = ref([]);

// Buscar os usuários da API
const fetchUsuarios = async () => {
  try {
    const response = await axios.get('/api/usuarios/colaboradores');
    usuarios.value = response.data.map((usuario) => ({
      ...usuario,
      permissions: usuario.permissions || {
        // Define permissões padrão se não existirem
        controle_estoque: false,
        controle_saida_estoque: false,
        gestao_equipe: false,
        fluxo_caixa: false,
        dre: false,
        contas_pagar: false,
      },
    }));
  } catch (error) {
    console.error('Erro ao carregar os usuários:', error);
  }
};

// Chamada ao montar o componente
onMounted(fetchUsuarios);

// Selecionar um usuário
const selecionarUsuario = (usuario) => {
  emit('usuario-selecionado', usuario);
};

// Alternar entre a tela de cadastro e a listagem
const toggleCadastro = () => {
  emit('abrir-cadastro');
};
</script>

<style scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}

.button-container {
  margin-top: 15px;
  text-align: right;
}

.card-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.card {
  width: 100%;
  height: 63px;
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0px 0px 1px rgba(142.11, 142.11, 142.11, 0.08);
}

.card-inner {
  display: flex;
  align-items: center;
  padding: 8px;
}

.icon-container {
  position: relative;
  width: 55px;
  height: 55px;
}

.icon-bg {
  width: 55px;
  height: 55px;
  position: absolute;
  left: 0;
  top: 1.33px;
}

.text-container {
  margin-left: 14px;
  flex-grow: 1;
}

.city {
  font-size: 17px;
  font-family: Figtree;
  font-weight: 600;
  line-height: 22px;
  color: #262a27;
}

.owner {
  font-size: 13px;
  font-family: Figtree;
  font-weight: 500;
  line-height: 18px;
  color: #6d6d6e;
}

.action-icon {
  width: 24px;
  height: 24px;
}
</style>
