<template>
  <div class="painel-title">Cadastrar Contas</div>
  <div class="painel-subtitle">
    <p>Cadastrar contas a pagar da sua operação</p>
  </div>

  <div class="mt-5">
    <div class="grid grid-cols-2 gap-8">
      <!-- Primeira coluna com duas linhas -->
      <div class="bg-white rounded-lg p-12">
        <div class="flex gap-4">
          <!-- Seletor de Nome da Conta (categorias) -->
          <div class="flex-1 mb-8">
            <label
              for="categoria"
              class="block text-sm font-medium text-gray-700"
            ></label>
            <LabelModel text="Nome da Conta" />
            <select
              id="categoria"
              v-model="categoriaSelecionada"
              @change="verificarCategoriaFornecedor"
              name="categoria"
              class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
            >
              <option value="" selected>Selecione uma categoria</option>
              <option
                v-for="categoria in categorias"
                :key="categoria.id"
                :value="categoria.id"
              >
                {{ categoria.nome }}
              </option>
            </select>
            <span v-if="errors.categoria" class="text-red-500 text-sm">
              {{ errors.categoria }}
            </span>
          </div>

          <!-- Seletor de Dias de Lembrete -->
          <div class="flex-1">
            <LabelModel text="Lembrar - me" />
            <select
              id="dias_lembrete"
              v-model="diasLembrete"
              name="dias_lembrete"
              class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
            >
              <option value="1">1 dia antes</option>
              <option value="3">3 dias antes</option>
              <option value="5">5 dias antes</option>
              <option value="15">15 dias antes</option>
              <option value="30">30 dias antes</option>
            </select>
            <span v-if="errors.diasLembrete" class="text-red-500 text-sm">
              {{ errors.diasLembrete }}
            </span>
          </div>
        </div>

        <!-- Seletor de Fornecedor (aparece apenas quando a categoria for específica) -->
        <div v-if="mostrarSeletorFornecedor" class="mb-8">
          <LabelModel text="Fornecedor" />
          <select
            id="fornecedor"
            v-model="fornecedorSelecionado"
            @change="atualizarNomeConta"
            name="fornecedor"
            class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
          >
            <option value="" selected>Selecione um fornecedor</option>
            <option
              v-for="fornecedor in fornecedores"
              :key="fornecedor.id"
              :value="fornecedor"
            >
              {{ fornecedor.nome }}
            </option>
          </select>
          <span v-if="errors.fornecedor" class="text-red-500 text-sm">
            {{ errors.fornecedor }}
          </span>
        </div>

        <LabelModel text="Valor" />
        <input
          id="valor"
          v-model="valor"
          @input="valor = formatarValorBR(valor)"
          class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
          placeholder="R$ 0,00"
        />
        <span v-if="errors.valor" class="text-red-500 text-sm">
          {{ errors.valor }}
        </span>

        <div class="col-span-1 row-span-2 mt-12">
          <div
            class="text-[#262a27] text-[12px] font-bold font-['Figtree'] leading-[48px] tracking-wide"
          >
            <!-- Input para Data Emitida -->
            <LabelModel text="Data emitida" />
            <input
              type="date"
              id="emitida_em"
              v-model="emitidaEm"
              name="emitida_em"
              class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
            />
            <span v-if="errors.emitidaEm" class="text-red-500 text-sm">
              {{ errors.emitidaEm }}
            </span>

            <div class="mt-12"></div>
            <!-- Input para Data de Vencimento -->
            <LabelModel text="Data de vencimento" />
            <input
              type="date"
              id="vencimento"
              v-model="vencimento"
              name="vencimento"
              class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']"
            />
            <span v-if="errors.vencimento" class="text-red-500 text-sm">
              {{ errors.vencimento }}
            </span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg p-12">
        <!-- Seletor de Dias de Lembrete -->
        <LabelModel text="Informações adicionais" />
        <textarea
          id="descricao"
          v-model="descricao"
          class="w-full h-[170px] bg-white border-gray-300 rounded-lg border-2 border-[#d7d7db] px-2 py-1 text-[14px] outline-none resize-none focus:ring-2 focus:ring-green-500"
          placeholder="Detalhes adicionais aqui..."
        ></textarea>

        <p
          class="text-right text-xs mt-1"
          :class="{
            'text-gray-500': descricao.length <= 200,
            'text-red-500': descricao.length > 200,
          }"
        >
          {{ descricao.length }}/200 caracteres
        </p>

        <div class="mt-2">
          <LabelModel text="Enviar boleto/fatura" />
          <div class="mt-2">
            <label
              for="arquivo"
              class="w-full flex items-center justify-center gap-2 p-4 text-gray-800 bg-[#d9d9d9] rounded-lg cursor-pointer hover:bg-gray-300 focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
            >
              {{ arquivoNome || 'Selecionar Arquivo' }}
              <img
                src="/storage/images/upload.svg"
                alt="Upload"
                class="w-5 h-5"
              />
            </label>
            <input
              type="file"
              id="arquivo"
              name="arquivo"
              class="hidden"
              @change="handleFileUpload"
            />
            <span v-if="errors.arquivo" class="text-red-500 text-sm">
              {{ errors.arquivo }}
            </span>
          </div>
        </div>
        <div class="form-buttons">
          <ButtonCancelar class="w-full" text="Cancelar" @click="cancelForm" />
          <ButtonPrimaryMedio
            text="Cadastrar"
            class="w-full"
            iconPath="/storage/images/arrow_left_alt.svg"
            @click="showConfirmDialog('Cadastrar um nova conta?')"
          />
        </div>
      </div>
    </div>
  </div>

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

const toast = useToast();

const valor = ref(null);
const descricao = ref('');
const emitidaEm = ref('');
const vencimento = ref('');
const diasLembrete = ref(1);
const categoriaSelecionada = ref(null);
const arquivoNome = ref('');
const fornecedorSelecionado = ref(null);
const fornecedores = ref([]);
const mostrarSeletorFornecedor = ref(false);

const isConfirmDialogVisible = ref(false);
const motivo = ref('');

const categorias = ref([]);
const errors = ref({}); // Armazenar erros de validação

const emit = defineEmits(['voltar']);

// IDs das categorias que devem mostrar o seletor de fornecedor
const CATEGORIAS_FORNECEDOR = [19];


onMounted(async () => {
  try {
    const response = await axios.get('/api/categorias/seleto-contas'); 
    categorias.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar categorias:', error);
  }
});

// Carrega fornecedores da API externa
const carregarFornecedores = async () => {
  if (fornecedores.value.length > 0) return; // Evita recarregar se já tiver

  try {
    const response = await axios.get('/api/cursto/fornecedores-externos');
    fornecedores.value = response.data;
    console.log(fornecedores.value);
  } catch (error) {
    console.error('Erro ao carregar fornecedores:', error);
    toast.error('Erro ao carregar lista de fornecedores');
  }
};

// Verifica se a categoria selecionada deve mostrar o seletor de fornecedores
const verificarCategoriaFornecedor = () => {
  // Verifica se a categoria selecionada está na lista de categorias de fornecedor
  mostrarSeletorFornecedor.value = CATEGORIAS_FORNECEDOR.includes(parseInt(categoriaSelecionada.value));
  
  // Se for categoria de fornecedor, carrega a lista
  if (mostrarSeletorFornecedor.value) {
    carregarFornecedores();
  } else {
    // Se não for categoria de fornecedor, limpa o fornecedor selecionado
    fornecedorSelecionado.value = null;
  }
};

// Atualiza o nome da conta quando um fornecedor é selecionado
const atualizarNomeConta = () => {
    // A lógica de usar o nome é feita no submitForm enviando 'nome_personalizado'.
    // Aqui podemos fazer alguma validação visual se necessário.
    console.log('Fornecedor selecionado:', fornecedorSelecionado.value);
};

// Função para submeter o formulário
const submitForm = async () => {
  errors.value = {}; // Limpa os erros

  // Validações
  if (!categoriaSelecionada.value)
    errors.value.categoria = 'A categoria é obrigatória';
  if (!valor.value) errors.value.valor = 'O valor é obrigatório';
  if (!emitidaEm.value) errors.value.emitidaEm = 'A data emitida é obrigatória';
  if (!vencimento.value)
    errors.value.vencimento = 'A data de vencimento é obrigatória';
  if (descricao.value.length > 200)
    errors.value.descricao = 'A descrição deve ter no máximo 200 caracteres';

  // Ajusta o valor digitado
  const valorFormatado = parseFloat(
    valor.value.replace('R$', '').replace('.', '').replace(',', '.').trim()
  );

  if (isNaN(valorFormatado)) {
    errors.value.valor = 'O valor informado não é válido';
  } else {
    valor.value = valorFormatado;
  }

  if (Object.keys(errors.value).length === 0) {
    try {
      const formData = new FormData();
      formData.append('categoria_id', categoriaSelecionada.value);
      formData.append('valor', valor.value); // Valor ajustado
      formData.append('emitida_em', emitidaEm.value);
      formData.append('vencimento', vencimento.value);
      formData.append('dias_lembrete', diasLembrete.value);
      formData.append('descricao', descricao.value);

      // Se houver fornecedor selecionado, envia o nome dele como nome personalizado
      if (fornecedorSelecionado.value && fornecedorSelecionado.value.nome) {
          formData.append('nome_personalizado', fornecedorSelecionado.value.nome);
      }

      // Adicionar o arquivo se estiver presente
      if (arquivoNome.value) {
        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput.files.length > 0) {
          formData.append('arquivo', fileInput.files[0]);
        }
      }

      const response = await axios.post('/api/cursto/contas-a-pagar', formData);
      toast.success('Conta cadastrada com sucesso', response.data);
      console.log('Conta cadastrada com sucesso', response.data);

      // Limpa os campos
      cancelForm();
    } catch (error) {
      toast.error('Erro ao cadastrar a conta');
      console.error('Erro ao cadastrar a conta', error);
    }
  }
};

// Configuração do diálogo de confirmação

const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmDialogVisible.value = true; // Exibe o diálogo de confirmação
};

const handleConfirm = () => {
  submitForm();
  isConfirmDialogVisible.value = false;
};

const handleCancel = () => {
  isConfirmDialogVisible.value = false;
};

// Função para lidar com o upload do arquivo
const handleFileUpload = (event) => {
  toast.info('Arquivo selecionado com sucesso');
  const file = event.target.files[0];
  if (file) {
    arquivoNome.value = file.name;
  }
};

// Formatar valor para o formato monetário
const formatarValorBR = (valor) => {
  let numero = valor.replace(/\D/g, '');
  if (!numero) numero = '0';

  const inteiro = numero.slice(0, -2) || '0';
  const centavos = numero.slice(-2);

  return `R$ ${Number(inteiro).toLocaleString('pt-BR')},${centavos}`;
};

const cancelForm = () => {
  // Limpa os campos
  categoriaSelecionada.value = null;
  valor.value = null;
  emitidaEm.value = '';
  vencimento.value = '';
  diasLembrete.value = 1;
  descricao.value = '';
  arquivoNome.value = '';
  fornecedorSelecionado.value = null;
  mostrarSeletorFornecedor.value = false;

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
</style>
