<template>
  <Head title="Recuperar Senha" />
  <div class="relative w-[1280px] h-[70px] bg-white overflow-hidden">
    <!-- Fundo branco -->
    <div class="absolute inset-0 bg-white"></div>

    <!-- Logotipo (área para adicionar logo ou texto) -->
    <div class="absolute left-[65px] top-[21px] w-[149px] h-[27.27px]">
      <img
        src="/storage/images/logo_tipo_verde.svg"
        alt="Logo tipo verde"
        class="w-full h-full"
      />
    </div>

    <!-- Menu hambúrguer -->
    <div
      class="absolute left-[19px] top-[26px] flex flex-wrap gap-[3px] w-[26px] h-[18px]"
    >
      <img
        src="/storage/images/quadrados_verdes.svg"
        alt="Quadrados verdes"
        class="w-full h-full"
      />
    </div>
  </div>

  <div class="login-container">
    <!-- Imagens com alt para acessibilidade -->
    <div class="login-box">
      <div class="login-box-inner">
        <div class="title-container">
          <div class="title">Recuperar Senha</div>
          <div class="subtitle">Defina uma nova senha para sua conta</div>
        </div>

        <form @submit.prevent="submit">
          <!-- Campo Nova Senha -->
          <div class="input-container">
            <label for="password">Nova Senha</label>
            <input
              id="password"
              v-model="form.password"
              class="input"
              type="password"
              placeholder="●●●●●●●●●●●●"
              autocomplete="new-password"
              aria-label="Nova Senha"
            />
            <!-- Exibindo erro -->
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <!-- Campo Confirmar Senha -->
          <div class="input-container">
            <label for="password_confirmation">Confirmar Senha</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              class="input"
              type="password"
              placeholder="●●●●●●●●●●●●"
              autocomplete="new-password"
              aria-label="Confirmar Senha"
            />
            <!-- Exibindo erro -->
            <InputError
              class="mt-2"
              :message="form.errors.password_confirmation"
            />
          </div>

          <!-- Botão Atualizar Senha -->
          <ButtonPrimary
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Atualizar Senha
          </ButtonPrimary>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import ButtonPrimary from '@/Components/Button/ButtonPrimary.vue';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
  token: String,
});

const form = useForm({
  token: props.token,
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('password.update'), {
    onSuccess: () => {
      // Redirecionando para a página de login após sucesso
      Inertia.visit(route('entrar.painel'));
    },
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<style scoped>
.login-container {
  width: 100%;
  height: 100%;
  position: relative;
  background: #f3f8f3;
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
  left: 35%;
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
