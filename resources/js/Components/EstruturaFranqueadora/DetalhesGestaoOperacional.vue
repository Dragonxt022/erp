<template>
    <div v-if="!isEditMode">
        <div class="w-full h-[180px] bg-white rounded-[20px] p-12 mb-4">
            <!-- Exibe informações da informacoes apenas quando não está no modo de edição -->
            <div class="flex items-center">
                <!-- Coluna da Imagem -->
                <div class="w-1/1 flex justify-center">
                    <img :src="getProfilePhotoUrl(informacoes.profile_photo)" alt="Imagem"
                        class="w-20 h-20 p-2 rounded-md shadow-lg" />
                </div>
                <div class="w-2/3 pl-5">
                    <div class="text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[30px] tracking-tight">
                        {{ informacoes.name || 'N/A' }}
                    </div>
                </div>
                <div class="w-1/1">
                    <ConfirmDialog :isVisible="isConfirmDialogVisible" :motivo="motivo" @confirm="handleConfirm"
                        @cancel="handleCancel" />
                    <div class="absolute top-[110px] right-[70px] cursor-pointer"
                        @click="showConfirmDialog('excluir setor operacional')">
                        <img src="/storage/images/delete.svg" alt="excluir" class="w-6 h-6" />
                    </div>
                </div>
            </div>
        </div>

        <div class="text-gray-700 text-[15px] font-semibold font-['Figtree'] mt-2 mb-2">
            Etapas da atividade
        </div>
        <div class="w-full h-[280px] bg-white rounded-[20px] px-12 pt-5 relative overflow-hidden">
            <!-- Área rolável com etapas -->
            <div class="max-h-[220px] overflow-y-auto pr-2 scrollbar-hide">
                <ul class="list-decimal list-inside space-y-2 mt-4 text-gray-800 text-[15px] font-['Figtree']">
                    <li v-for="(etapa, index) in informacoes.etapas" :key="etapa.id"
                        class="items-center bg-[#F5FAF4] p-3 rounded-lg cursor-pointer hover:bg-gray-100 transition-all ease-in-out duration-300">
                        {{ etapa.descricao }}
                    </li>
                </ul>
            </div>


        </div>



    </div>

    <!-- Exibe o formulário de edição quando isEditMode é true -->
    <div v-if="informacoes.id && !isEditMode" class="mt-4">
        <ButtonEditeMedio text="Editar informacoes" icon-path="/storage/images/border_color.svg" @click="toggleEditMode"
            class="px-4 py-2 bg-[#F8F8F8] text-white rounded-lg" />
    </div>


</template>

<script setup>
import { defineProps, ref } from 'vue';
import ButtonEditeMedio from '../Button/ButtonEditeMedio.vue';
import ConfirmDialog from '../LaytoutFranqueadora/ConfirmDialog.vue';
import { useToast } from 'vue-toastification'; // Importa o hook useToast
const toast = useToast();

const props = defineProps({
    informacoes: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['editar']);

const isConfirmDialogVisible = ref(false);
const motivo = ref('');
const comunicado = ref('');


const showConfirmDialog = (action) => {
    motivo.value = action;
    isConfirmDialogVisible.value = true;
};
const isLoading = ref(false);
const handleConfirm = () => {
    deleteUsuario();
    isConfirmDialogVisible.value = false;
};
const handleCancel = () => {
    // Lógica para cancelar a ação
    isConfirmDialogVisible.value = false;
};

const deleteUsuario = async () => {
    try {
        isLoading.value = true;
        const response = await axios.delete(`/api/admin-operacionais/${props.informacoes.id}`);
        toast.success('setor excluído com sucesso!');
        isLoading.value = false;
    } catch (error) {
        console.error('Erro ao excluir setor:', error);
        toast.error('Erro ao excluir setor.');
        isLoading.value = false;
    }
};


const getProfilePhotoUrl = (profilePhoto) => {
    return profilePhoto ? `/storage/${profilePhoto}` : '/storage/images/no-imagem.jpg';
};
const showCadastroinformacoes = ref(false);
const isEditMode = ref(false);

const fetchinformacoes = () => {
    const dadosinformacoes = ref.dadosinformacoes;
    dadosinformacoes.fetchinformacoes();
};

const toggleEditMode = () => {
    emit('editar', props.informacoes);
    isEditMode.value = !isEditMode.value;
    showCadastroinformacoes.value = false;

};

// Função de cancelamento que será emitida pelo componente de edição
const cancelEdit = () => {
    isEditMode.value = false;
    showCadastroinformacoes.value = true;
};

</script>

<style scoped>
/* Ocultar scrollbar de forma cross-browser */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    /* IE e Edge */
    scrollbar-width: none;
    /* Firefox */
}
</style>
