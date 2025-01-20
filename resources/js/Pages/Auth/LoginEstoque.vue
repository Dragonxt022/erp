<template>
  <Head title="Acessar" />
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

    <div class="login-box">
      <div class="login-box-inner">
        <div class="title-container">
          <div class="title">Entrar no Taiksu</div>
          <div class="subtitle">Acompanhe seu negócio em tempo real</div>
        </div>

        <!-- Mensagem de status de erro ou sucesso -->
        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
          {{ status }}
        </div>

        <form @submit.prevent="submit">
          <!-- Campo CPF -->
          <div class="input-container">
            <label for="cpf">CPF</label>
            <input
              id="cpf"
              v-model="form.cpf"
              @input="applyCpfMask"
              class="input"
              type="text"
              placeholder="123.456.789-90"
              autocomplete="off"
              aria-label="CPF"
            />
            <!-- Exibindo erro -->
            <InputError class="mt-2" :message="form.errors.cpf" />
          </div>

          <!-- Campo Senha -->
          <div class="input-container">
            <label for="password">Senha</label>
            <input
              id="password"
              v-model="form.password"
              class="input"
              type="password"
              placeholder="●●●●●●●●●●●●"
              autocomplete="off"
              aria-label="Senha"
            />
            <!-- Exibindo erro -->
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <!-- Botão Acessar -->
          <ButtonPrimary
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Acessar
          </ButtonPrimary>

          <!-- Link Esqueci minha senha -->
          <Link :href="route('password.request')" class="cursor-pointer">
            <p class="forgot-password">Esqueci minha senha</p>
          </Link>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import ButtonPrimary from '@/Components/Button/ButtonPrimary.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  cpf: '',
  password: '',
  remember: true,
});

const submit = async () => {
  // Transforma os dados antes de enviar
  form.transform((data) => ({
    ...data,
    remember: form.remember ? 'on' : '',
  }));

  // Envia a requisição de login
  form.post(route('entrar.painel'), {
    onFinish: () => {
      form.reset('password');

      // Obtenha e armazene o token, se necessário
      fetchToken();
    },
  });
};

// Função para buscar o token após o login
const fetchToken = async () => {
  try {
    const response = await axios.get('/get-token', { withCredentials: true });
    if (response.data.status === 'success') {
      const token = response.data.token;

      // Armazene o token no localStorage
      localStorage.setItem('auth_token', token);

      // Configure o Axios para usar o token em futuras requisições
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

      console.log('Token obtido com sucesso!');
    } else {
      console.error('Erro ao obter o token:', response.data);
    }
  } catch (error) {
    console.error('Erro na requisição para obter o token:', error);
  }
};

// Função para aplicar a máscara de CPF
const applyCpfMask = (event) => {
  let value = event.target.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
  if (value.length > 11) value = value.slice(0, 11); // Limita o valor a 11 dígitos

  // Aplica a formatação do CPF
  if (value.length > 9) {
    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
  } else if (value.length > 6) {
    value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
  } else if (value.length > 3) {
    value = value.replace(/(\d{3})(\d{3})/, '$1.$2');
  }

  event.target.value = value;
  form.cpf = value;
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
