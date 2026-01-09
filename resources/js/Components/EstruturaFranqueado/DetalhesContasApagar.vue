<template>
    <div class="elemento-fixo">
        <!-- Tabela de Lotes -->
        <div v-if="!isEditMode">
            <div class="mt-8">
                <div class="w-full h-[555px] bg-white rounded-[20px] p-7 relative">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <p class="text-[25px] font-bold font-['Figtree'] leading-8 tracking-tight">
                                {{ dados.nome }}
                            </p>
                            <!-- Botão de Histórico -->
                            <button @click="isHistoryModalVisible = true" title="Ver Histórico" class="p-1 hover:bg-gray-100 rounded-full transition-colors">
                                <img src="/storage/images/history.svg" alt="Histórico" class="w-5 h-5 opacity-60 hover:opacity-100" />
                            </button>
                        </div>
                        <button class="text-gray-500 hover:text-red-600"
                            @click="isConfirmExluirDialogVisible('Excluir essa conta?')">
                            <img src="/storage/images/delete.svg" alt="Excluir" class="w-6 h-6" />
                        </button>
                    </div>

                    <p class="text-[#6db631] text-xl font-normal font-['Figtree'] leading-[25px] tracking-tight">
                        {{ dados.valor_formatado }}
                    </p>
                    <p class="text-[#6d6d6d] text-[15px] font-medium font-['Figtree'] leading-[20px]"></p>

                    <div class="col-span-1 row-span-2 mt-8">
                        <div class="col-span-1 row-span-2 mt-8">
                            <div class="text-[#262a27] text-[12px] font-bold leading-[48px] tracking-wide">
                                <!-- Input para Data Emitida -->
                                <LabelModel text="Data emitida" />
                                <input type="text" id="emitida_em" :value="formatarData(dados.emitida_em)"
                                    name="emitida_em"
                                    class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-gray-700 focus:ring-2 focus:ring-green-500"
                                    readonly />
                                <div class="mt-12"></div>

                                <!-- Input para Data de Vencimento -->
                                <LabelModel text="Data de vencimento" />
                                <input type="text" id="vencimento" name="vencimento"
                                    :value="formatarData(dados.vencimento)"
                                    class="w-full py-2 bg-transparent border border-gray-300 rounded-lg outline-none text-base text-center text-gray-700 focus:ring-2 focus:ring-green-500"
                                    readonly />
                            </div>
                        </div>

                        <LabelModel text="Informações adicionais" />
                        <p
                            class="w-full h-[100px] bg-white border-gray-300 rounded-lg border-2 border-[#d7d7db] px-2 py-1 text-[14px] outline-none resize-none focus:ring-2 focus:ring-green-500">
                            {{ dados.descricao }}
                        </p>
                    </div>

                    <div class="flex space-x-4 mt-5">
                        <div>
                            <select id="status" v-model="statusSelecionado" @change="atualizarStatus"
                                class="h-[46px] px-[38px] py-3 rounded-[10px] shadow-[2px_2px_10px_0px_rgba(0,0,0,0.04)] flex justify-center items-center gap-2.5 focus:outline-none"
                                style="border: 2px solid #c1fab6; outline-color: #c1fab6;">
                                <option v-for="status in statusOptions" :key="status" :value="status">
                                    {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                                </option>
                            </select>


                        </div>

                        <ButtonClaroMedio text="Baixar boleto"
                            class="text-[#6db631] bg-[#f4faf4] hover:bg-[#c1fab6] transition duration-200 ease-in-out"
                            @click="baixarArquivo" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <ConfirmDialog :isVisible="isConfirmDialogVisible" :motivo="motivo" @confirm="handleConfirm"
        @cancel="handleCancel" />

    <ConfirmDialog :isVisible="isConfirmExlusaoDialogVisible" :motivo="motivo" @confirm="handleConfirmExlucao"
        @cancel="handleCancelExlusao" />

    <!-- Modal de Histórico -->
    <div v-if="isHistoryModalVisible" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[80vh]">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <img src="/storage/images/history.svg" class="w-6 h-6 opacity-70" />
                    Histórico da Conta
                </h3>
                <button @click="isHistoryModalVisible = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1 bg-white">
                <div v-if="!dados.historico || dados.historico.length === 0" class="text-center py-10 text-gray-500">
                    Nenhum histórico registrado para esta conta.
                </div>
                <div v-else class="relative border-l-2 border-green-100 ml-3 space-y-8">
                    <div v-for="(log, index) in reversedHistorico" :key="index" class="relative pl-8">
                        <!-- Dot on Timeline -->
                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full border-2 border-white" 
                             :class="log.acao === 'criacao' ? 'bg-blue-500' : 'bg-green-500'"></div>
                        
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">
                                {{ formatarDataHora(log.data) }}
                            </span>
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 shadow-sm">
                                <p class="text-sm font-semibold text-gray-800 mb-2">
                                    {{ getAcaoDescricao(log) }}
                                </p>
                                <div class="flex items-center gap-2 text-xs text-gray-600">
                                    <span class="bg-white px-2 py-1 rounded border border-gray-100 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ log.usuario }}
                                    </span>
                                </div>
                                <div v-if="log.status_novo" class="mt-3 flex items-center gap-2">
                                    <span v-if="log.status_anterior" class="text-xs text-gray-400 line-through">{{ log.status_anterior }}</span>
                                    <svg v-if="log.status_anterior" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <span :class="['px-2 py-1 text-[10px] font-bold rounded-lg uppercase tracking-tight', getStatusClassBadge(log.status_novo)]">
                                        {{ log.status_novo }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-6 bg-gray-50 border-t border-gray-100 text-right">
                <button @click="isHistoryModalVisible = false" 
                        class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl transition-all active:scale-95">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, ref, watch, onMounted, computed } from 'vue';
import { defineEmits } from 'vue';
import LabelModel from '../Label/LabelModel.vue';
import ButtonClaroMedio from '../Button/ButtonClaroMedio.vue';
import { useToast } from 'vue-toastification';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';
import axios from 'axios';


const toast = useToast();
const props = defineProps({
    dados: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['voltar', 'atualiza']);
const statusOptions = ref([]);
const statusSelecionado = ref(props.dados.status);
const isEditMode = ref(false);
const indexEditavel = ref(null);
const isConfirmDialogVisible = ref(false);
const isConfirmExlusaoDialogVisible = ref(false);
const isHistoryModalVisible = ref(false);
const motivo = ref('');

// Configuração do diálogo de confirmação

const showConfirmDialog = (motivoParam) => {
    motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
    isConfirmDialogVisible.value = true; // Exibe o diálogo de confirmação
};
const isConfirmExluirDialogVisible = (motivoParam) => {
    motivo.value = motivoParam; // Agora 'motivo' é reativo e você pode alterar seu valor
    isConfirmExlusaoDialogVisible.value = true; // Exibe o diálogo de confirmação
};

watch(() => props.dados.status, (novoStatus) => {
    statusSelecionado.value = novoStatus;
});

const reversedHistorico = computed(() => {
    if (!props.dados.historico) return [];
    return [...props.dados.historico].reverse();
});

const formatarDataHora = (dataString) => {
    if (!dataString) return '';
    const data = new Date(dataString);
    return data.toLocaleString('pt-BR');
};

const getAcaoDescricao = (log) => {
    if (log.acao === 'criacao') return 'Conta criada no sistema';
    if (log.acao === 'alteracao_status') return 'Status da conta alterado';
    return log.acao;
};

const getStatusClassBadge = (status) => {
    const colors = {
        pendente: 'bg-orange-100 text-orange-700',
        pago: 'bg-green-100 text-green-700',
        agendada: 'bg-blue-100 text-blue-700',
        atrasado: 'bg-red-100 text-red-700',
    };
    return colors[status.toLowerCase()] || 'bg-gray-100 text-gray-700';
};


const statusIcons = {
  pendente: '/storage/images/check_circle_laranja.svg',
  concluida: '/storage/images/check_circle_verde.svg',
  agendada: '/storage/images/agendada.svg',
  atrasada: '/storage/images/atrasada.svg',
};

const getStatusIcon = (status) => {
  return statusIcons[status] || '/storage/images/check_circle_laranja.svg';
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

const handleConfirmExlucao = () => {
    isConfirmExlusaoDialogVisible.value = false;
    excluirConta(); // Agora acessa props.dados corretamente
};

const handleCancelExlusao = () => {
    isConfirmExlusaoDialogVisible.value = false;
};

// Função para ativar o modo de edição
const ativarEdicao = (index) => {
    indexEditavel.value = index;
};

// Seletor de status
const atualizarStatus = async () => {
    try {
        const response = await axios.put(`/api/cursto/cursto/contas-a-pagar/${props.dados.id}/status`, {
            status: statusSelecionado.value,
        });
        toast.success(response.data.message);
        
        // Atualiza o histórico localmente após a resposta bem sucedida
        if (response.data.data && response.data.data.historico) {
            props.dados.historico = response.data.data.historico;
        }
        
        emit('atualiza');
    } catch (error) {
        console.error(error);
        toast.error('Erro ao atualizar o status da conta');
    }
};

// botão
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
        
        // Atualiza o histórico localmente se retornado
        if (response.data.data && response.data.data.historico) {
            props.dados.historico = response.data.data.historico;
        }

        emit('atualiza');
    } catch (error) {
        console.error('Erro ao pagar a conta:', error);
        toast.error('Erro ao pagar a conta');
    }
};

const excluirConta = async (id) => {
    if (!props.dados || !props.dados.id) {
        toast.error('Erro: Dados da conta não encontrados.');
        return;
    }

    try {
        // Fazendo a requisição DELETE para excluir a conta
        const response = await axios.delete(
            `/api/cursto/contas-a-pagar/${props.dados.id}`
        );

        // Exibir uma notificação de sucesso
        toast.success('Conta excluída com sucesso');
        emit('voltar');
        // Aqui você pode recarregar a lista de contas ou redirecionar o usuário
    } catch (error) {
        // Em caso de erro, exiba uma notificação de erro
        console.error('Erro ao excluir a conta:', error);
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

onMounted(async () => {
    try {
        const response = await axios.get('/api/cursto/contas-a-pagar/status-options');
        statusOptions.value = response.data.data;

        // Se o status atual não existir nos options (ex: novo valor), adiciona
        if (!statusOptions.value.includes(statusSelecionado.value)) {
            statusOptions.value.push(statusSelecionado.value);
        }
    } catch (error) {
        console.error('Erro ao carregar opções de status:', error);
    }
});
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
    position: -webkit-sticky;
    /* Para navegadores que exigem o prefixo */
    position: sticky;
    top: 0;
    /* Defina o valor para o topo de onde o elemento ficará fixo */
    z-index: 10;
    /* Para garantir que o elemento fique sobre outros */
}

/* Tornando a lista rolável com barra de rolagem invisível */
.scrollbar-hidden::-webkit-scrollbar {
    display: none;
}

.scrollbar-hidden {
    -ms-overflow-style: none;
    /* Para o IE e Edge */
    scrollbar-width: none;
    /* Para o Firefox */
}

.owner {
    font-size: 13px;
    font-family: Figtree;
    font-weight: 500;
    line-height: 18px;
    color: #6d6d6e;
}
</style>
