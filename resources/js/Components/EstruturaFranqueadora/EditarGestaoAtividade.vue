<template>
    <div v-if="isVisible" class="sidebar-container">
        <div v-if="isLoading" class="loading-overlay">
            <div class="spinner"></div>
        </div>

        <!-- Título principal -->
        <div class="painel-title">Editar atividade</div>

        <!-- Subtítulo da página -->
        <div class="painel-subtitle">
            <p>Edite atividade para os processos da franquia</p>
        </div>

        <div v-if="!showView" key="listagem" class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2">

            <!-- Bloco de informações -->
            <div class="w-full h-full bg-white rounded-[20px] p-12">
                <div class="flex justify-center mb-6 relative group">
                    <!-- Quadrado com a imagem ou ícone -->
                    <div class="w-[110px] h-[110px] bg-[#f3f8f3] rounded-xl flex items-center justify-center cursor-pointer overflow-hidden relative"
                        @click="openFileSelector">
                        <template v-if="profilePhotoUrl">
                            <!-- Exibe a imagem selecionada -->
                            <img :src="profilePhotoUrl" alt="Imagem selecionada"
                                class="w-full h-full p-2 object-cover" />
                            <!-- Fundo escuro e botão de remoção ao passar o mouse -->
                            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                                @click.stop>
                                <button @click.stop="removeImage"
                                    class="w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                                    X
                                </button>
                            </div>
                        </template>
                        <template v-else>
                            <!-- Exibe o ícone se nenhuma imagem foi selecionada -->
                            <img src="/storage/images/arrow_upload_ready.svg" alt="Ícone de upload" />
                        </template>
                    </div>
                    <input type="file" ref="fileInput" accept="image/*" class="hidden" @change="handleImageUpload" />
                </div>
                <LabelModel text="Nome da atividade" />
                <InputModel v-model="name" placeholder="Limpar camarão" />
            </div>

            <!-- Bloco de Etapas -->
            <div class="w-full h-full bg-white rounded-[20px] p-12">
                <LabelModel text="Etapas da atividade" />
                <InputModel v-model="novaEtapa" placeholder="Descreva a etapa aqui..." />

                <p class="text-right text-xs mt-1" :class="{
                    'text-gray-500': novaEtapa.length <= 42,
                    'text-red-500': novaEtapa.length > 42,
                }">
                    {{ novaEtapa.length }}/42 caracteres
                </p>

                <ul class="mt-4 space-y-2">
                    <li v-for="(etapa, index) in etapas" :key="index"
                        class="flex justify-between items-center bg-gray-100 p-3 rounded">
                        <!-- Adicionando número visualmente -->
                        <span>{{ index + 1 }}. {{ etapa.descricao }}</span>

                        <button @click="removerEtapa(index)" class="text-red-600 font-bold hover:text-red-800">
                            <img src="/storage/images/delete.svg" alt="excluir" class="w-4 h-4" />
                        </button>
                    </li>
                </ul>

                <ButtonPrimaryMedio @click="adicionarEtapa" text="Adicionar nova etapa" class="w-full mt-3" />
            </div>

            <!-- Bloco de Setor e Tempo Estimado -->
            <div class="w-full h-full bg-white rounded-[20px] p-12">
                <div class="flex-1">
                    <LabelModel text="Setor" />
                    <select v-model="setorSelecionado"
                        class="w-full py-2 bg-[#F3F8F3] border border-gray-300 rounded-lg outline-none text-base text-gray-700 focus:ring-2 focus:ring-green-500 font-['Figtree']">
                        <option disabled value="">Selecione um setor</option>
                        <option v-for="setor in setores" :key="setor.id" :value="setor.id">
                            {{ setor.name }}
                        </option>
                    </select>
                </div>

                <LabelModel text="Tempo estimado em minutos" />
                <div class="relative">
                    <InputModel v-model="tempoEstimado" placeholder="0" />
                    <span class="absolute right-[130px] top-1/2 transform -translate-y-1/2 text-gray-500">minutos</span>
                </div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-6 right-6 flex space-x-4 z-10">
        <ButtonCancelar text="Cancelar" @click="cancelForm" />
        <ButtonPrimaryMedio @click="submitForm" text="Atualizar atividade" />
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { defineProps, defineEmits } from 'vue';
import InputModel from '../Inputs/InputModel.vue';
import LabelModel from '../Label/LabelModel.vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import { useToast } from 'vue-toastification'; // Importa o hook useToast
import ButtonCancelar from '../Button/ButtonCancelar.vue';

const toast = useToast(); // Cria a instância do toast

const props = defineProps({
    informacoes: Object,
    isVisible: Boolean
})

const emit = defineEmits(['cancelar']);
const showView = ref(false);
const name = ref('');
const profilePhotoUrl = ref('');
const selectedFile = ref(null);
const fileInput = ref(null);

const setores = ref([]);
const setorSelecionado = ref(null);
const etapas = ref([]);
const novaEtapa = ref('');
const tempoEstimado = ref('');

const errorMessage = ref('');

const isLoading = ref(false);

watch(() => props.informacoes, (novoValor) => {
    if (novoValor) {
        name.value = novoValor.name || '';
        setorSelecionado.value = novoValor.setor_id || null;
        tempoEstimado.value = novoValor.tempo_estimated || '';
        etapas.value = novoValor.etapas || [];
        profilePhotoUrl.value = novoValor.profile_photo_url || '';
    }
}, { immediate: true }); // A opção 'immediate: true' garante que a função será chamada imediatamente quando o componente for montado


// Buscar setores operacionais ao montar
onMounted(async () => {
    try {
        const response = await axios.get('/api/admin-operacionais');
        setores.value = response.data.data;
    } catch (error) {
        toast.error('Erro ao carregar setores operacionais.');
    }
});

// Adicionar nova etapa
const adicionarEtapa = () => {
    if (etapas.value.length >= 5) {
        toast.warning('Você só pode adicionar no máximo 5 etapas.');
        return;
    }

    if (novaEtapa.value.trim() !== '') {
        etapas.value.push({ descricao: novaEtapa.value });
        novaEtapa.value = '';
    }
};

// Remover etapa
const removerEtapa = (index) => {
    etapas.value.splice(index, 1);
};

// Cancela e reseta o formulário
const cancelForm = () => {
    resetForm();
    emit('cancelar');
};

// Reseta os valores do formulário
const resetForm = () => {
    name.value = '';
    errorMessage.value = '';
};

// Valida os campos do formulário
const validateForm = () => {
    if (!name.value) {
        toast.error('Por favor, preencha todos os campos obrigatórios.');
        errorMessage.value = 'Por favor, preencha todos os campos obrigatórios.';
        return false;
    }
    return true;
};

// Envia os dados do formulário
const submitForm = async () => {
    if (!validateForm()) return;

    try {
        isLoading.value = true;
        const formData = new FormData();
        formData.append('name', name.value);
        formData.append('setor_id', setorSelecionado.value || '');
        formData.append('tempo_estimated', parseInt(tempoEstimado.value) || 0);

        etapas.value.forEach((etapa) => {
            formData.append('etapas[][descricao]', etapa.descricao);
        });

        if (selectedFile.value) {
            formData.append('profile_photo', selectedFile.value);
        }

        // Log do FormData
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        const response = await axios.put(`/api/atividades/${props.informacoes.id}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        toast.success('Atividade atualizada com sucesso!');
        cancelForm();
    } catch (error) {
        console.error('Erro ao atualizar atividade:', error.response?.data || error.message);
        toast.error('Erro ao atualizar atividade: ' + (error.response?.data?.message || error.message));
    } finally {
        isLoading.value = false;
    }
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
</script>

<style scoped>
.painel-title {
    font-size: 34px;
    font-weight: 700;
    color: #262a27;

}

.painel-subtitle {
    font-size: 17px;
    margin-bottom: 25px;
    line-height: 5px;
    color: #6d6d6e;
    max-width: 600px;
}

.form-buttons {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.error-message {
    color: red;
    font-size: 14px;
    margin-top: 10px;
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
    border: 4px solid #f3f3f3;
    /* Cor de fundo */
    border-top: 4px solid #6db631;
    /* Cor do topo */
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
