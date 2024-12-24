<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import ButtonPrimary from '@/Components/Button/ButtonPrimary.vue';

let isEmailSent = false;
let status = null;

defineProps({
  status: String,
});

const form = useForm({
  email: '',
});

// Função de envio do formulário
const submit = async () => {
  form.post(route('password.email'), {
    onSuccess: () => {
      // Quando o e-mail for enviado com sucesso, altere o estado
      isEmailSent = true;
      status = 'Verifique sua caixa de entrada!';
    },
    onError: (errors) => {
      // Caso haja erros, exiba o erro no formulário
      status = errors.email ? 'Ocorreu um erro ao enviar o e-mail.' : null;
    },
  });
};
</script>

<style lang="css" scoped>
.login-container {
  width: 100%;
  height: 100%;
  position: relative;
  background: #f3f8f3;
}

.background-img {
  width: auto;
  height: 100vh;
  position: absolute;
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
  position: relative;
}

.title-container {
  width: 100%;
  margin-bottom: 30px;
}

.title {
  font-size: 34px;
  font-family: Figtree, sans-serif;
  font-weight: 700;
  color: #262a27;
  line-height: 41px;
}

.subtitle {
  color: #6d6d6e;
  font-size: 17px;
  font-family: Figtree, sans-serif;
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

/* Estilos da caixa de conteúdo */
.content-box {
  padding: 70px 60px;
  position: absolute;
  left: 44%;
  top: 160px;
  background: rgba(255, 255, 255, 0);
  border-radius: 20px;
  display: inline-flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  gap: 10px;
}

/* Estilos do círculo */
.circle-container {
  width: 50px;
  height: 50px;
  position: relative;
}

.circle-background {
  width: 50px;
  height: 50px;
  position: absolute;
  top: 0;
  left: 0;
}

.circle-inner {
  width: 41.67px;
  height: 33.33px;
  position: absolute;
  top: 8.33px;
  left: 4.17px;
}

/* Estilos do conteúdo textual */
.text-content2 {
  align-self: stretch;
  flex: 1 1 0;
  position: relative;
}

.title2 {
  position: absolute;
  left: 0;
  top: 0;
  color: #262a27;
  font-size: 34px;
  font-family: Figtree, sans-serif;
  font-weight: 700;
  line-height: 41px;
  letter-spacing: 0.41px;
  word-wrap: break-word;
}

.subtitle2 {
  position: absolute;
  left: 0;
  top: 49px;
  color: #6d6d6e;
  font-size: 17px;
  font-family: Figtree, sans-serif;
  font-weight: 400;
  line-height: 22px;
  word-wrap: break-word;
}
</style>

<template>
  <Head title="Recuperar Senha" />
  <div class="login-container">
    <!-- Imagens com alt para acessibilidade -->
    <img
      class="background-img"
      src="/storage/images/mulher_login.png"
      alt="Imagem de fundo representando uma mulher usando um computador"
    />
    <img
      class="logo-img"
      src="/storage/images/logo_tipo.png"
      alt="Logo do Taiksu"
    />

    <!-- Formulário de recuperação de senha -->
    <div class="login-box" v-if="!isEmailSent">
      <div class="login-box-inner">
        <div class="title-container">
          <div class="title">Recuperar Senha</div>
          <div class="subtitle">Digite seu e-mail para redefinir sua senha</div>
        </div>

        <!-- Mensagem de status de erro ou sucesso -->
        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
          {{ status }}
        </div>

        <form @submit.prevent="submit">
          <!-- Campo Email -->
          <div class="input-container">
            <label for="email">Email</label>
            <input
              id="email"
              v-model="form.email"
              class="input"
              type="email"
              placeholder="exemplo@dominio.com"
              required
              autofocus
              autocomplete="username"
            />
            <!-- Exibindo erro -->
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <!-- Botão Enviar -->
          <ButtonPrimary
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Enviar Link de Redefinição
          </ButtonPrimary>
        </form>
        <Link :href="route('entrar')" class="cursor-pointer">
          <p class="forgot-password">Lembrei minha senha</p>
        </Link>
      </div>
    </div>

    <!-- Mensagem de sucesso após envio de e-mail -->
    <div class="content-box" v-if="isEmailSent">
      <div class="circle-container">
        <div class="circle-background"></div>
        <div class="circle-inner">
          <img src="/storage/images/icon_mail.svg" alt="Logo E-mail" />
        </div>
      </div>
      <div class="title-container">
        <div class="title">Verifique seu E-mail</div>
        <div class="subtitle">
          Enviamos um link de redefinição da sua
          <br />
          senha para o seu e-mail cadastrado
        </div>
      </div>
    </div>
  </div>
</template>
