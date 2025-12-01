<template>
  <Head title="Recuperar Senha" />

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
    <!-- Formulário de recuperação de senha -->
    <div v-if="!isEmailSent" class="w-full max-w-md rounded-2xl p-8 mt-16">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Recuperar Senha</h1>
        <p class="text-gray-500">Digite seu e-mail para redefinir sua senha</p>
      </div>

      <!-- Mensagem de status de erro ou sucesso -->
      <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
        {{ status }}
      </div>

      <form @submit.prevent="submit">
        <!-- Campo Email -->
        <div class="mb-6">
          <label
            for="email"
            class="block text-sm font-semibold text-gray-800 mb-2"
          >
            Email
          </label>
          <input
            id="email"
            v-model="form.email"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
          class="w-full py-3 transition-opacity"
          :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
          :disabled="form.processing"
        >
          Enviar Link de Redefinição
        </ButtonPrimary>
      </form>

      <Link :href="route('login')" class="block text-center mt-4">
        <p
          class="text-gray-800 font-semibold hover:text-green-600 transition-colors"
        >
          Lembrei minha senha
        </p>
      </Link>
    </div>

    <!-- Mensagem de sucesso após envio de e-mail -->
    <div
      v-if="isEmailSent"
      class="w-full max-w-md rounded-2xl p-8 mt-16 text-center"
    >
      <div class="flex justify-center mb-6">
        <div
          class="relative w-16 h-16 bg-green-100 rounded-full flex items-center justify-center"
        >
          <img
            src="/storage/images/icon_mail.svg"
            alt="Logo E-mail"
            class="w-8 h-8"
          />
        </div>
      </div>

      <h1 class="text-3xl font-bold text-gray-800 mb-2">
        Verifique seu E-mail
      </h1>
      <p class="text-gray-500">
        Enviamos um link de redefinição da sua
        <br />
        senha para o seu e-mail cadastrado
      </p>

      <Link :href="route('login')" class="block text-center mt-8">
        <ButtonPrimary class="px-6">Voltar para login</ButtonPrimary>
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import ButtonPrimary from '@/Components/Button/ButtonPrimary.vue';
import { ref } from 'vue';

let isEmailSent = ref(false);
let status = ref(null);

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
      isEmailSent.value = true;
      status.value = 'Verifique sua caixa de entrada!';
    },
    onError: (errors) => {
      // Caso haja erros, exiba o erro no formulário
      console.log(errors);
      form.errors.email = errors.email;
    },
  });
};
</script>
