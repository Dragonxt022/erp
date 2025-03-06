<template>
  <Head title="Recuperar Senha" />

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
        <h1 class="text-3xl font-bold text-gray-800">Recuperar Senha</h1>
        <p class="text-gray-500">Defina uma nova senha para sua conta</p>
      </div>

      <form @submit.prevent="submit">
        <!-- Campo E-mail -->
        <div class="mb-6">
          <label
            for="email"
            class="block text-sm font-semibold text-gray-800 mb-2"
          >
            E-mail
          </label>
          <input
            id="email"
            v-model="form.email"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            type="email"
            placeholder="seu@email.com"
            autocomplete="email"
            aria-label="E-mail"
          />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <!-- Campo Nova Senha -->
        <div class="mb-6">
          <label
            for="password"
            class="block text-sm font-semibold text-gray-800 mb-2"
          >
            Nova Senha
          </label>
          <input
            id="password"
            v-model="form.password"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            type="password"
            placeholder="●●●●●●●●●●●●"
            autocomplete="new-password"
            aria-label="Nova Senha"
          />
          <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <!-- Campo Confirmar Senha -->
        <div class="mb-6">
          <label
            for="password_confirmation"
            class="block text-sm font-semibold text-gray-800 mb-2"
          >
            Confirmar Senha
          </label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            type="password"
            placeholder="●●●●●●●●●●●●"
            autocomplete="new-password"
            aria-label="Confirmar Senha"
          />
          <InputError
            class="mt-2"
            :message="form.errors.password_confirmation"
          />
        </div>

        <!-- Botão Atualizar Senha -->
        <ButtonPrimary
          class="w-full py-3 transition-opacity"
          :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
          :disabled="form.processing"
        >
          Atualizar Senha
        </ButtonPrimary>
      </form>
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
  success: Boolean, // Adiciona a prop para receber o status de sucesso
  message: String, // Opcional: para exibir a mensagem, se desejar
});

const form = useForm({
  token: props.token,
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('updateRecupe.update', { token: props.token }), {
    onSuccess: (page) => {
      // Verifica se a propriedade 'success' é true e redireciona
      if (page.props.success) {
        Inertia.visit(route('entrar.painel'));
      }
    },
    onError: (errors) => {
      console.log('Erro ao atualizar senha:', errors);
    },
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>
