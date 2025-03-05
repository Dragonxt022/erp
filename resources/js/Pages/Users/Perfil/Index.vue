<template>
  <LayoutFranqueado>
    <Head title="Minha conta" />

    <!-- Título principal com Link para voltar -->
    <div class="painel-header">
      <div class="painel-title">Minha conta</div>
    </div>

    <!-- Subtítulo da página -->
    <div class="painel-subtitle">
      <p>Seus dados pessoais e senha.</p>
    </div>

    <!-- Container de duas colunas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Coluna 1: Detalhes do usuário -->
      <div class="bg-white rounded-tl-[20px] rounded-tr-[20px] p-7 shadow-sm">
        <div v-if="dados" class="flex justify-between items-center">
          <!-- Foto do usuário -->
          <div class="flex justify-center w-1/4">
            <img
              :src="profilePhoto"
              alt="Foto do Usuário"
              class="w-20 h-20 rounded-md shadow-sm"
            />
          </div>

          <!-- Nome, Cargo e Cidade -->
          <div class="w-2/3">
            <div
              class="text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[30px] tracking-tight"
            >
              {{ dados.name || 'N/A' }}
            </div>
            <div class="flex gap-4 mt-2">
              <div
                class="flex-1 h-[44px] bg-[#F3F8F3] rounded-lg border-2 border-[#d7d7db] p-2 text-base text-[#262A27] font-bold flex items-center"
              >
                <span>{{ dados.cargo?.nome || 'Sem informações' }}</span>
              </div>
              <div
                class="flex-1 h-[44px] bg-[#F3F8F3] rounded-lg border-2 border-[#d7d7db] p-2 text-base text-[#262A27] font-bold flex items-center"
              >
                <span>{{ dados.unidade?.cidade || 'Sem informações' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Informações adicionais -->
        <div v-if="dados" class="grid grid-cols-2 gap-4 mt-8">
          <div>
            <LabelModel text="E-mail" />
            <p
              class="w-full h-[44px] bg-white border-2 border-[#d7d7db] rounded-lg p-2 text-base font-bold text-gray-800"
            >
              {{ dados.email || 'N/A' }}
            </p>
          </div>
          <div>
            <LabelModel text="Salário" />
            <p
              class="w-full h-[44px] bg-white border-2 border-[#d7d7db] rounded-lg p-2 text-base font-bold text-gray-800"
            >
              {{ salarioFormatado }}
            </p>
          </div>
        </div>

        <!-- Loading state -->
        <div v-else class="animate-pulse">
          <div class="flex justify-between items-center">
            <div class="w-20 h-20 bg-gray-200 rounded-md"></div>
            <div class="w-2/3 space-y-2">
              <div class="h-8 bg-gray-200 rounded"></div>
              <div class="w-72 h-[44px] bg-gray-200 rounded-lg"></div>
              <div class="w-72 h-[44px] bg-gray-200 rounded-lg"></div>
            </div>
          </div>
          <div class="grid grid-cols-1 gap-4 mt-8">
            <div class="h-[44px] bg-gray-200 rounded-lg"></div>
            <div class="h-[44px] bg-gray-200 rounded-lg"></div>
          </div>
        </div>
      </div>

      <!-- Coluna 2: Formulário de atualização de senha -->
      <div class="bg-white rounded-tl-[20px] rounded-tr-[20px] p-7 shadow-sm">
        <h3 class="text-xl font-bold text-[#262a27] mb-4">Atualizar Senha</h3>
        <form @submit.prevent="submitPasswordForm">
          <div class="grid grid-cols-1 gap-4">
            <!-- Senha Atual -->
            <div>
              <LabelModel text="Senha Atual" />
              <input
                v-model="form.current_password"
                type="password"
                class="w-full h-[44px] bg-white border-2 border-[#d7d7db] rounded-lg p-2 text-base text-gray-800 focus:ring-2 focus:ring-green-500"
                :class="{ 'border-red-500': errors.current_password }"
              />
              <span v-if="errors.current_password" class="text-red-500 text-sm">
                {{ errors.current_password }}
              </span>
            </div>

            <!-- Nova Senha -->
            <div>
              <LabelModel text="Nova Senha" />
              <input
                v-model="form.password"
                type="password"
                class="w-full h-[44px] bg-white border-2 border-[#d7d7db] rounded-lg p-2 text-base text-gray-800 focus:ring-2 focus:ring-green-500"
                :class="{ 'border-red-500': errors.password }"
              />
              <span v-if="errors.password" class="text-red-500 text-sm">
                {{ errors.password }}
              </span>
            </div>

            <!-- Confirmação da Nova Senha -->
            <div>
              <LabelModel text="Confirmar Nova Senha" />
              <input
                v-model="form.password_confirmation"
                type="password"
                class="w-full h-[44px] bg-white border-2 border-[#d7d7db] rounded-lg p-2 text-base text-gray-800 focus:ring-2 focus:ring-green-500"
                :class="{ 'border-red-500': errors.password_confirmation }"
              />
              <span
                v-if="errors.password_confirmation"
                class="text-red-500 text-sm"
              >
                {{ errors.password_confirmation }}
              </span>
            </div>

            <!-- Botão de envio -->
            <div class="mt-4">
              <button
                type="submit"
                class="w-full bg-[#6DB631] text-white font-bold py-2 px-4 rounded-lg hover:bg-[#528429] transition-colors"
                :disabled="form.processing"
              >
                {{ form.processing ? 'Salvando...' : 'Atualizar Senha' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { router, useForm } from '@inertiajs/vue3';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import LabelModel from '@/Components/Label/LabelModel.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

// Dados do usuário
const dados = ref(null);

// Foto do perfil
const profilePhoto = computed(() => {
  return (
    dados.value?.profile_photo_url ||
    `https://ui-avatars.com/api/?name=${getInitials(
      dados.value?.name
    )}&color=7F9CF5&background=EBF4FF`
  );
});

// Formatação do salário
const salarioFormatado = computed(() => {
  const salario = dados.value?.salario || 0;
  return `R$ ${Number(salario).toFixed(2).replace('.', ',')}`;
});

// Função para pegar as iniciais do nome
const getInitials = (name) => {
  if (!name) return '*';
  const nameParts = name.split(' ');
  return nameParts.map((part) => part.charAt(0).toUpperCase()).join('');
};

// Função para buscar os dados do perfil via API
const fetchUserProfile = async () => {
  try {
    const response = await fetch('/api/navbar-profile', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        // Adicione autenticação se necessário, ex.: 'Authorization': `Bearer ${token}`
      },
    });

    if (!response.ok) {
      throw new Error('Erro ao carregar perfil do usuário');
    }

    const data = await response.json();
    dados.value = data.data; // Ajustado para a estrutura da API fornecida
  } catch (error) {
    console.error('Erro ao buscar perfil:', error);
    dados.value = null;
  }
};

// Carrega os dados ao montar o componente
onMounted(() => {
  fetchUserProfile();
});

// Formulário de atualização de senha
const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});
const errors = ref({});

const submitPasswordForm = () => {
  form.post(route('usuario.password.update'), {
    onSuccess: () => {
      toast.success('Atualizado com sucesso');

      form.reset();
      errors.value = {};
    },
    onError: (err) => {
      toast.error('Erro atualizadas');

      errors.value = err;
    },
  });
};
</script>

<style scoped>
.painel-header {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
  line-height: 30px;
}

.painel-subtitle {
  font-size: 17px;
  margin-bottom: 25px;
  color: #6d6d6e;
  max-width: 600px;
}

.back-link {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: #6d6d6e;
}

.back-link:hover {
  color: #528429;
}

.grid-cols-1 {
  grid-template-columns: 1fr;
}

@media (min-width: 768px) {
  .md\:grid-cols-2 {
    grid-template-columns: repeat(2, 1fr);
  }
}

.animate-pulse div {
  background-color: #e0e0e0;
}
</style>
