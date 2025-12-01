<template>
  <div class="painel-title">Editar colaborador</div>
  <div class="painel-subtitle">
    <p>Atualize as informações do colaborador</p>
  </div>

  <div v-if="isLoading" class="loading-overlay">
    <div class="spinner"></div>
  </div>
  <div class="mt-5">
    <!-- Formulário de cadastro -->
    <div class="bg-white rounded-lg p-12">
      <div class="flex gap-8 mb-8">
        <!-- Coluna 1: Upload de Imagem -->
        <div class="flex flex-col items-center">
          <div class="relative group">
            <div
              class="w-[110px] h-[110px] bg-[#f3f8f3] rounded-xl flex items-center justify-center cursor-pointer overflow-hidden"
              @click="openFileSelector"
            >
              <template v-if="profilePhotoUrl">
                <img
                  :src="profilePhotoUrl"
                  alt="Imagem selecionada"
                  class="w-full h-full object-cover"
                />
                <div
                  class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                  @click.stop
                >
                  <button
                    @click.stop="removeImage"
                    class="w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-sm"
                  >
                    X
                  </button>
                </div>
              </template>
              <template v-else>
                <img
                  src="/storage/images/arrow_upload_ready.svg"
                  alt="Ícone de upload"
                />
              </template>
            </div>
            <input
              type="file"
              ref="fileInput"
              accept="image/*"
              class="hidden"
              @change="handleImageUpload"
            />
          </div>
        </div>

        <!-- Coluna 2: Seletor de Cargo -->
        <div class="flex flex-col flex-1">
          <LabelModel text="Cargo" class="mb-2" />
          <select
            v-model="selectedCargo"
            class="w-full h-[44px] bg-[#F3F8F3] border-gray-100 rounded-lg border-2 border-[#d7d7db] p-2 text-base text-[#6DB631] font-bold focus:ring-2 focus:ring-green-500"
          >
            <option value="" disabled selected>Selecione um cargo</option>
            <option
              v-for="cargo in cargos"
              :key="cargo.id"
              :value="cargo.id"
              class="text-base font-semibold"
            >
              {{ cargo.nome }}
            </option>
          </select>
        </div>
        <!-- Coluna 2: Seletor de Setor Operacional -->
        <div class="flex flex-col flex-1">
          <LabelModel text="Setor operacional" class="mb-2" />
          <select
            v-model="selectedSetor"
            class="w-full h-[44px] bg-[#F3F8F3] border-gray-100 rounded-lg border-2 border-[#d7d7db] p-2 text-base text-[#6DB631] font-bold focus:ring-2 focus:ring-green-500"
          >
            <option value="" disabled selected>Selecione um setor</option>
            <option
              v-for="setor in setores"
              :key="setor.id"
              :value="setor.id"
              class="text-base font-semibold"
            >
              {{ setor.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Campos do formulário -->
      <LabelModel text="Nome Completo" />
      <input
        id="nome"
        v-model="nome"
        class="w-full py-3 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        placeholder="João Silva Souza"
      />
      <span v-if="errors.nome" class="text-red-500 text-sm">
        {{ errors.nome }}
      </span>

      <LabelModel text="E-mail" />
      <input
        id="email"
        v-model="email"
        class="w-full py-3 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        placeholder="usuario@email.com
"
      />
      <span v-if="errors.email" class="text-red-500 text-sm">
        {{ errors.email }}
      </span>

      <LabelModel text="CPF" />
      <input
        id="cpf"
        v-model="cpf"
        @input="cpf = formatarCPF(cpf)"
        class="w-full py-3 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        placeholder="000.000.000-00"
        maxlength="14"
      />
      <span v-if="errors.cpf" class="text-red-500 text-sm">
        {{ errors.cpf }}
      </span>

      <LabelModel text="Salário" />
      <input
        id="salario"
        v-model="salario"
        @input="salario = formatarValorBR(salario)"
        class="w-full py-3 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
        placeholder="R$ 1.600,00"
      />
      <span v-if="errors.salario" class="text-red-500 text-sm">
        {{ errors.salario }}
      </span>

      <!-- Botões do formulário -->
      <div class="form-buttons">
        <ButtonCancelar class="w-full" text="Cancelar" @click="cancelForm" />
        <ButtonPrimaryMedio
          text="Atualizar"
          class="w-full"
          iconPath="/storage/images/arrow_left_alt.svg"
          @click="showConfirmDialog('Atualizar colaborador?')"
        />
      </div>
    </div>
  </div>

  <!-- Diálogo de confirmação -->
  <ConfirmDialog
    :isVisible="isConfirmDialogVisible"
    :motivo="motivo"
    @confirm="handleConfirm"
    @cancel="handleCancel"
  />
</template>

<script setup>
import { ref, onMounted, defineEmits } from 'vue';
import LabelModel from '@/Components/Label/LabelModel.vue';
import axios from 'axios';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import ButtonCancelar from '../Button/ButtonCancelar.vue';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';
import { useToast } from 'vue-toastification';

// Adicione o prop user
const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
});

const toast = useToast();
const emit = defineEmits(['voltar']);

// Dados do formulário
const user_id = ref(props.user ? props.user.id : null);
const nome = ref('');
const email = ref('');
const cpf = ref('');
const salario = ref('');
const selectedCargo = ref(null);
const selectedSetor = ref(null);
const profilePhotoUrl = ref('');
const selectedFile = ref(null);
const fileInput = ref(null);
const cargos = ref([]);
const setores = ref([]);
const errors = ref({});

// Diálogo de confirmação
const isConfirmDialogVisible = ref(false);
const motivo = ref('');

const isLoading = ref(false);

// Modifique o onMounted para carregar dados do usuário
onMounted(async () => {
  try {
    // Carregar cargos
    const response = await axios.get('/api/usuarios/cargos');
    cargos.value = response.data;

    // Se for edição, carregar dados do usuário
    if (props.user) {
      nome.value = props.user.name;
      email.value = props.user.email;
      cpf.value = formatarCPF(props.user.cpf);
      salario.value = formatarValorBR(props.user.salario.toString());
      selectedCargo.value = props.user.cargo_id;
      selectedSetor.value = props.user.setor_id;
      profilePhotoUrl.value = props.user.profile_photo_url || '';
    }
  } catch (error) {
    console.error('Erro ao carregar dados:', error);
  }
});
// Carregar setores
const fetchSetores = async () => {
  try {
    const response = await axios.get('/api/usuarios/operacionais');
    setores.value = response.data.data;
  } catch (error) {
    console.error('Erro ao carregar setores:', error);
  }
};
fetchSetores();

const formatarCPF = (valor) => {
  // Remove tudo que não for número
  let cpfFormatado = valor.replace(/\D/g, '');

  // Aplica a máscara: 000.000.000-00
  cpfFormatado = cpfFormatado.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona o primeiro ponto
  cpfFormatado = cpfFormatado.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona o segundo ponto
  cpfFormatado = cpfFormatado.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Adiciona o traço

  return cpfFormatado;
};

// Formatar valor para o formato monetário
const formatarValorBR = (valor) => {
  let numero = valor.replace(/\D/g, '');
  if (!numero) numero = '0';

  const inteiro = numero.slice(0, -2) || '0';
  const centavos = numero.slice(-2);

  return `R$ ${Number(inteiro).toLocaleString('pt-BR')},${centavos}`;
};

// Converter valor monetário para decimal
const converterParaDecimal = (valorFormatado) => {
  // Remove o "R$" e espaços em branco
  let valor = valorFormatado.replace('R$', '').trim();

  // Substitui a vírgula por ponto e converte para número
  valor = valor.replace('.', '').replace(',', '.');

  // Converte para número decimal
  return parseFloat(valor);
};

// Função para submeter o formulário
const submitForm = async () => {
  errors.value = {}; // Limpa os erros

  // Validações
  if (!nome.value) errors.value.nome = 'O nome é obrigatório';
  if (!email.value) errors.value.email = 'O email é obrigatório';
  if (!cpf.value) errors.value.cpf = 'O CPF é obrigatório';
  if (!salario.value) errors.value.salario = 'O salário é obrigatório';
  if (!selectedCargo.value) errors.value.cargo = 'O cargo é obrigatório';
  if (!selectedSetor.value) errors.value.setor = 'O setor é obrigatório';

  if (Object.keys(errors.value).length === 0) {
    try {
      isLoading.value = true;

      const formData = new FormData();
      formData.append('user_id', user_id.value);
      formData.append('name', nome.value);
      formData.append('email', email.value);
      formData.append('cpf', cpf.value);
      formData.append('salario', converterParaDecimal(salario.value)); // Converte o salário
      formData.append('cargo_id', selectedCargo.value);
      formData.append('setor_id', selectedSetor.value);

      // Adicionar a foto de perfil se estiver presente
      if (selectedFile.value) {
        formData.append('profile_photo', selectedFile.value);
      }

      // Enviar os dados para o backend
      const response = await axios.post(
        '/api/usuarios/atualiza-colaborador',
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        }
      );

      toast.success('Colaborador atualizado com sucesso!');
      console.log('Colaborador atualizado:', response.data);
      emit('voltar');
      // Limpar o formulário
      cancelForm();
      window.location.reload();
    } catch (error) {
      toast.error('Erro ao atualizar colaborador');
      console.error('Erro ao atualizar colaborador:', error);
    } finally {
      isLoading.value = false;
    }
  }
};

// Diálogo de confirmação
const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam;
  isConfirmDialogVisible.value = true;
};

const handleConfirm = () => {
  submitForm();
  isConfirmDialogVisible.value = false;
};

const handleCancel = () => {
  isConfirmDialogVisible.value = false;
};

// Funções para upload de imagem
const openFileSelector = () => {
  fileInput.value?.click();
};

const removeImage = () => {
  profilePhotoUrl.value = '';
  selectedFile.value = null;
  toast.info('Imagem removida.');
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
    const reader = new FileReader();
    reader.onload = () => {
      profilePhotoUrl.value = reader.result;
      toast.success('Imagem carregada com sucesso!');
    };
    reader.readAsDataURL(file);
  }
};

// Limpar formulário
const cancelForm = () => {
  nome.value = '';
  email.value = '';
  cpf.value = '';
  salario.value = '';
  selectedCargo.value = null;
  profilePhotoUrl.value = '';
  selectedFile.value = null;
  emit('voltar');
};
</script>

<style lang="css" scoped>
.painel-title {
  font-size: 34px;
  line-height: 15px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  margin-bottom: 10px; /* Espaçamento inferior */
}

.painel-subtitle {
  font-size: 17px;
  line-height: 25px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}
.form-buttons {
  display: flex;
  justify-content: flex-end;
  margin-top: 55px;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

/* Estilos para o spinner */
.spinner {
  border: 4px solid #f3f3f3; /* Cor de fundo */
  border-top: 4px solid #6db631; /* Cor do topo */
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 2s linear infinite;
}

/* Animação do spinner */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
