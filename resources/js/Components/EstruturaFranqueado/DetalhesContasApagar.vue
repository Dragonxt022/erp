<template>
  <div class="elemento-fixo">
    <!-- Tabela de Lotes -->
    <div v-if="!isEditMode">
      <div class="mt-8">
        <div class="w-full h-[530px] bg-white rounded-[20px] p-7 relative">
          <p
            class="text-[25px] font-bold font-['Figtree'] leading-8 tracking-tight"
          >
            {{ dados.nome }}
          </p>

          <P
            class="text-[#6db631] text-xl font-normal font-['Figtree'] leading-[25px] tracking-tight"
          >
            {{ dados.valor_formatado }}
          </P>
          <p
            class="text-[#6d6d6d] text-[15px] font-medium font-['Figtree'] leading-[20px]"
          ></p>
          <div class="col-span-1 row-span-2 mt-8">
            <div class="col-span-1 row-span-2 mt-8">
              <div
                class="text-[#262a27] text-[12px] font-bold leading-[48px] tracking-wide"
              >
                <!-- Input para Data Emitida -->
                <LabelModel text="Data emitida" />
                <input
                  type="text"
                  id="emitida_em"
                  :value="formatarData(dados.emitida_em)"
                  name="emitida_em"
                  class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-gray-700 focus:ring-2 focus:ring-green-500"
                  readonly
                />

                <div class="mt-12"></div>
                <!-- Input para Data de Vencimento -->
                <LabelModel text="Data de vencimento" />
                <input
                  type="text"
                  id="vencimento"
                  name="vencimento"
                  :value="formatarData(dados.vencimento)"
                  class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-gray-700 focus:ring-2 focus:ring-green-500"
                  readonly
                />
              </div>
            </div>
            <LabelModel text="Informações adicionais" />
            <p
              class="w-full h-[100px] bg-white border-gray-300 rounded-lg border-2 border-[#d7d7db] px-2 py-1 text-[14px] outline-none resize-none focus:ring-2 focus:ring-green-500"
            >
              {{ dados.descricao }}
            </p>
          </div>
          <div class="flex space-x-4 mt-5">
            <ButtonClaroMedio
              :text="props.dados.status === 'pendente' ? 'Pagar' : 'Pago'"
              :class="getStatusClass(props.dados.status)"
              @click="showConfirmDialog('Marca como paga essa conta?')"
              :iconPath="getStatusIcon(props.dados.status)"
              :disabled="props.dados.status === 'pago'"
            />

            <ButtonClaroMedio
              text="Baixar boleto"
              class="text-[#6db631] w-full bg-[#f4faf4] hover:bg-[#c1fab6] transition duration-200 ease-in-out"
              @click="baixarArquivo"
            />
          </div>
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
import { defineProps, ref } from 'vue';
import { defineEmits } from 'vue';
import LabelModel from '../Label/LabelModel.vue';
import ButtonClaroMedio from '../Button/ButtonClaroMedio.vue';
import { useToast } from 'vue-toastification';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';

const toast = useToast();

const props = defineProps({
  dados: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['atualizar']);

const isEditMode = ref(false);
const indexEditavel = ref(null);
const isConfirmDialogVisible = ref(false);
const motivo = ref('');

// Configuração do diálogo de confirmação

const showConfirmDialog = (motivoParam) => {
  motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
  isConfirmDialogVisible.value = true; // Exibe o diálogo de confirmação
};

const getStatusIcon = (status) => {
  return status === 'pendente'
    ? '/storage/images/check_circle_laranja.svg'
    : '/storage/images/check_circle_verde.svg';
};

const getStatusClass = (status) => {
  return status === 'pendente'
    ? 'text-[#FF9500] w-full bg-[#f4faf4] hover:bg-[#ffdeb1] transition duration-200 ease-in-out'
    : 'text-[#6db631] w-full bg-[#f4faf4] hover:bg-[#c1fab6] transition duration-200 ease-in-out ';
};

const handleConfirm = () => {
  isConfirmDialogVisible.value = false;
  pagarConta(); // Agora acessa props.dados corretamente
};

const handleCancel = () => {
  isConfirmDialogVisible.value = false;
};

// Função para ativar o modo de edição
const ativarEdicao = (index) => {
  indexEditavel.value = index;
};

const pagarConta = async () => {
  if (!props.dados || !props.dados.id) {
    toast.error('Erro: Dados da conta não encontrados.');
    return;
  }

  try {
    const response = await axios.post(
      `/api/cursto/contas-a-pagar/${props.dados.id}/pagar`
    );
    toast.success(response.data.message);

    // Atualiza o status da conta
    props.dados.status = 'pago';
  } catch (error) {
    console.error('Erro ao pagar a conta:', error);
    toast.error('Erro ao pagar a conta');
  }
};

// Função para formatar a data corretamente
const formatarData = (data) => {
  if (!data) return '';
  const partes = data.split('-');
  return `${partes[2]}/${partes[1]}/${partes[0]}`;
};

const baixarArquivo = () => {
  if (props.dados.arquivo) {
    // Cria um link temporário e força o download
    const link = document.createElement('a');
    link.href = `/${props.dados.arquivo}`; // Caminho do arquivo
    link.download = props.dados.arquivo.split('/').pop(); // Nome do arquivo
    link.target = '_blank';
    link.click();
    toast.info('Arquivo baixado com sucesso');
  } else {
    toast.warning('Arquivo não encontrado');
    console.error('Arquivo não encontrado');
  }
};
</script>

<style scoped>
/* Estilizando a tabela */
table {
  width: 100%;
  margin-top: 20px;

  border-collapse: collapse;
}

th,
td {
  padding: 12px;
}

th {
  background-color: #164110;
  color: #ffff;
  margin-bottom: 10px;
}

.TrRedonEsquerda {
  border-radius: 20px 0px 0px 0px;
}

.TrRedonDireita {
  border-radius: 0px 20px 0px 0px;
}

tr:nth-child(even) {
  background-color: #f4f5f3;
}

tr:hover {
  background-color: #dededea9;
  cursor: pointer;
}

.elemento-fixo {
  position: -webkit-sticky; /* Para navegadores que exigem o prefixo */
  position: sticky;
  top: 0; /* Defina o valor para o topo de onde o elemento ficará fixo */
  z-index: 10; /* Para garantir que o elemento fique sobre outros */
}
/* Tornando a lista rolável com barra de rolagem invisível */
.scrollbar-hidden::-webkit-scrollbar {
  display: none;
}

.scrollbar-hidden {
  -ms-overflow-style: none; /* Para o IE e Edge */
  scrollbar-width: none; /* Para o Firefox */
}
.owner {
  font-size: 13px;
  font-family: Figtree;
  font-weight: 500;
  line-height: 18px;
  color: #6d6d6e;
}
</style>
