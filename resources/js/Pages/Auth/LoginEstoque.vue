<template>
  <Head title="Acessar" />
  <div class="login-container">
    <!-- Imagens com alt para acessibilidade -->
    <img
      class="background-img transform scale-x-[-1]"
      src="/storage/images/mulher_login_estoque.png"
      alt="Imagem de fundo representando uma mulher usando um computador"
    />
    <img
      class="logo-img"
      src="/storage/images/logo_tipo.png"
      alt="Logo do Taiksu"
    />

    <div class="login-box">
      <div class="login-box-inner">
        <div class="title-container">
          <div class="title">Controle de estoque</div>
          <div class="subtitle">Registro de retirada em estoque da loja.</div>
        </div>

        <form @submit.prevent="submit">
          <!-- Campo Senha -->
          <div class="input-container">
            <label for="pin">Seu PIN</label>
            <input
              id="pin"
              v-model="form.pin"
              class="input"
              type="pin"
              placeholder="●●●●"
              autocomplete="off"
              aria-label="Senha"
            />
            <!-- Exibindo erro -->
            <InputError class="mt-2 text-center" :message="form.errors.pin" />
          </div>
          <div
            v-if="errorMessage"
            class="mb-4 font-medium text-sm text-red-600 text-center"
          >
            {{ errorMessage }}
          </div>

          <!-- Botão Acessar -->
          <ButtonPrimary
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Acessar
          </ButtonPrimary>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import ButtonPrimary from '@/Components/Button/ButtonPrimary.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  pin: '',
});

const errorMessage = ref(null);

const submit = async () => {
  // Reseta a mensagem de erro
  errorMessage.value = null;

  try {
    // Envia a requisição de login
    const response = await form.post(route('login.estoque'), {
      onFinish: () => form.reset('pin'),
    });

    // Verifica se a resposta contém um redirecionamento
    if (response.data?.redirect) {
      // Redireciona para a página de controle de estoque
      window.location.href = response.data.redirect;
    }
  } catch (error) {
    // Lida com erros de validação ou acesso negado
    if (error.response?.status === 403) {
      errorMessage.value = 'Acesso negado ao controle de estoque.';
    } else if (error.response?.status === 401) {
      errorMessage.value = 'PIN inválido.';
    } else {
      console.log('Ouve um erro inesperado');
    }
  }
};
</script>

<style scoped>
.login-container {
  width: 100%;
  height: 100%;
  position: relative;
  background: #f3f8f3;
}

.background-img {
  width: 40%; /* Define a largura desejada */
  height: 100vh; /* A altura ocupa toda a altura da tela */
  object-fit: cover; /* Garante que a imagem preencha o contêiner sem distorção */
  object-position: 100% center; /* Desloca o centro da imagem para a direita */
}

.logo-img {
  width: 151px;
  height: 30px;
  position: absolute;
  left: 40px;
  top: 19px;
}

.login-box {
  padding: 70px 60px;
  position: absolute;
  left: 665px;
  top: 145px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 20px;
}

.login-box-inner {
  width: 367px;
  height: 402px;
  position: relative;
}

.title-container {
  width: 100%;
  margin-bottom: 30px;
}

.title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
  line-height: 41px;
}

.subtitle {
  font-size: 17px;
  color: #6d6d6e;
}

.input-container {
  width: 100%;
  margin-bottom: 20px;
}

label {
  font-size: 17px;
  font-weight: 600;
  color: #262a27;
  margin-bottom: 8px;
  display: block;
}

.input {
  width: 100%;
  height: 48px;
  padding: 8px 16px;
  background: white;
  border: 2px solid #d7d7db;
  border-radius: 8px;
  font-size: 16px;
  color: #222222;
  opacity: 0.8;
}

.forgot-password {
  text-align: center;
  font-size: 16px;
  font-weight: 600;
  color: #262a27;
  margin-top: 10px;
}
</style>
