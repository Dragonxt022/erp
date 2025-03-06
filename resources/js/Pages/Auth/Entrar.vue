<template>
  <Head title="Acessar" />

  <!-- Faixa verde no topo com logo centralizada -->
  <div class="w-full h-[71px] bg-[#164110] py-4 fixed top-0 left-0 z-10">
    <div class="container mx-auto flex justify-center">
      <img
        class="h-8"
        src="/storage/images/logo_tipo.png"
        alt="Logo do Taiksu"
      />
    </div>
  </div>

  <div
    class="min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4 pt-4"
  >
    <div class="w-full max-w-md rounded-2xl p-8 mt-16">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Entrar no Taiksu</h1>
        <p class="text-gray-500">Acompanhe seu negócio em tempo real</p>
      </div>

      <!-- Mensagem de status de erro ou sucesso -->
      <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
        {{ status }}
      </div>

      <form @submit.prevent="submit">
        <!-- Campo CPF -->
        <div class="mb-6">
          <label
            for="cpf"
            class="block text-sm font-semibold text-gray-800 mb-2"
          >
            Entre com CPF ou E-mail
          </label>
          <input
            id="cpf"
            v-model="form.cpf"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            type="text"
            placeholder="Entre com CPF ou E-mail"
            autocomplete="off"
            aria-label="CPF"
          />
          <!-- Exibindo erro -->
          <InputError class="mt-2" :message="form.errors.cpf" />
        </div>

        <!-- Campo Senha -->
        <div class="mb-6">
          <label
            for="password"
            class="block text-sm font-semibold text-gray-800 mb-2"
          >
            Senha
          </label>
          <input
            id="password"
            v-model="form.password"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
          class="w-full py-3 transition-opacity"
          :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
          :disabled="form.processing"
        >
          Acessar
        </ButtonPrimary>

        <!-- Link Esqueci minha senha -->
        <Link :href="route('password.request')" class="block text-center mt-4">
          <p
            class="text-gray-800 font-semibold hover:text-green-600 transition-colors"
          >
            Esqueci minha senha
          </p>
        </Link>
      </form>
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
</script>
