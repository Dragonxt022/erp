<template>
    <AppLayout>

        <Head title="Gestão de Atividades" />

        <Transition name="fade" mode="out-in">
            <!-- Quando estiver em modo de edição -->
            <EditarGestaoAtividade v-if="isEditMode" :isVisible="true" :informacoes="DadosSelecionados"
                @dadosinformacoes="fetchinformacoes" @cancelar="cancelEdit" key="editar" />

            <!-- Quando não estiver em modo de edição nem em modo de cadastro -->
            <div v-else-if="!showView" key="listagem" class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2">
                <div>
                    <ListarGestaoAtividade key="listar" ref="listar" @cadastrar="fetch"
                        @selecionado="dadoSelecionado" />
                </div>
                <div class="flex flex-col gap-4">
                    <template v-if="DadosSelecionados">
                        <DetalhesGestaoOperacional @editar="editarInformacoes" @cancelar="cancelEdit" :informacoes="DadosSelecionados" />
                    </template>
                    <template v-else>
                        <div class="text-gray-500 text-center mt-12">
                            <p class="text-lg">Selecione uma atividade para ver os detalhes.</p>
                            <p class="text-sm">Clique em "Cadastrar nova atividade" para adicionar uma nova.</p>
                        </div>
                    </template>
                    <div class="absolute bottom-4 right-4">
                        <ButtonPrimaryMedio text="Cadastrar nova atividade"
                            iconPath="/storage/images/arrow_left_alt.svg" @click="toggleCadastro"
                            :class="{ hidden: isEditMode }" />
                    </div>
                </div>
            </div>

            <!-- Quando estiver em modo de cadastro -->
            <CadastroGestaoAtividade v-else key="cadastro" :isVisible="showView" @cadastrar="handleCadastro"
                @cancelar="cancelEdit" />
        </Transition>

    </AppLayout>
</template>


<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/LayoutFranqueadora.vue';
import { Head } from '@inertiajs/vue3';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import DetalhesGestaoOperacional from '@/Components/EstruturaFranqueadora/DetalhesGestaoOperacional.vue';
import ListarGestaoAtividade from '@/Components/EstruturaFranqueadora/ListarGestaoAtividade.vue';
import CadastroGestaoAtividade from '@/Components/EstruturaFranqueadora/CadastroGestaoAtividade.vue';
import EditarGestaoAtividade from '@/Components/EstruturaFranqueadora/EditarGestaoAtividade.vue';

const DadosSelecionados = ref(null);
const showView = ref(false);
const isEditMode = ref(false); // Para controle de edição, se necessário

const listar = ref(null);

const fetch = () => {
    listar.value?.fetch();
};

const handleCadastro = () => {
    fetch();
};

const editarInformacoes = (dados) => {
    console.log('editar informacoes');
    console.log(dados);
    DadosSelecionados.value = dados;
    isEditMode.value = true;
    showView.value = false;
};

const cancelEdit = () => {
    console.log('cancelar');
    window.location.reload();

};
const fetchinformacoes = () => {
    listar.value?.fetch();
};

const toggleCadastro = () => {
    showView.value = !showView.value;
};


const dadoSelecionado = (dados) => {
    DadosSelecionados.value = dados;
};
</script>


<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
