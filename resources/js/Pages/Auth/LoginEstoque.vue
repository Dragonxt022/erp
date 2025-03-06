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

  <div class="min-h-screen flex flex-col items-center justify-center px-4 pt-4">
    <div class="w-full max-w-md rounded-2xl p-8 mt-16">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 text-center">
          Controle de estoque
        </h1>
        <p class="text-gray-500 text-center">
          Registro de retirada em estoque da loja.
        </p>
      </div>

      <form @submit.prevent="submit">
        <!-- Campo Senha -->
        <div class="mb-6">
          <label
            for="pin"
            class="block text-2-l font-semibold text-gray-800 mb-1"
          >
            PIN
          </label>
          <input
            id="pin"
            v-model="form.pin"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            type="password"
            placeholder="●●●●"
            autocomplete="off"
            aria-label="Senha"
          />
          <!-- Exibindo erro -->
          <InputError class="mt-2 text-center" :message="form.errors.pin" />
        </div>

        <div v-if="errorMessage" class="mb-4 text-sm text-red-600 text-center">
          {{ errorMessage }}
        </div>

        <!-- Botão Acessar -->
        <ButtonPrimary
          class="w-full py-3 transition-opacity"
          :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
          :disabled="form.processing"
        >
          Acessar
        </ButtonPrimary>
      </form>
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
      console.log('Ocorreu um erro inesperado');
    }
  }
};
</script>
