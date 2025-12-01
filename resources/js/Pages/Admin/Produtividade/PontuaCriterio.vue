<template>
    <AppLayout>
        <!-- Cabeçalho da página -->

        <Head title="Setores Operacionais" />
        <Transition name="fade" mode="out-in">
            <!-- Container principal da grade com 2 colunas -->
            <div class="grid grid-cols-1 gap-[3rem] mt-3 sm:grid-cols-2">
                <!-- Coluna 1: Listar informacoess -->
                <div>
                    <!-- Componente para listar informacoess -->
                    <ListarPontuacaoCrieterio ref="listar" @cadastrar="fetch" @selecionado="dadoSelecionado" />
                </div>

                <!-- Coluna 2: Alternar entre Detalhes e Cadastro -->
                <div class="flex flex-col gap-4">
                    <!-- Mostrar Detalhes da informacoes Selecionada ou Cadastro -->
                    <template v-if="!showView">
                        <template v-if="DadosSelecionados">
                            <!-- Mostrar Detalhes da informacoes Selecionada -->
                            <DetalhesSetorOperacao @cancelar="toggleCadastro" :informacoes="DadosSelecionados" />
                        </template>

                    </template>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/LayoutFranqueadora.vue';
import { Head } from '@inertiajs/vue3';
import DetalhesSetorOperacao from '@/Components/EstruturaFranqueadora/DetalhesSetorOperacao.vue';
import ListarPontuacaoCrieterio from '@/Components/EstruturaFranqueadora/ListarPontuacaoCrieterio.vue';

const DadosSelecionados = ref(null);
const showView = ref(false);
const isEditMode = ref(false); // Para controle de edição, se necessário
const listar = ref();


const fetch = () => {
    listar.value?.fetch(); // Chama o método exposto do componente filho
};

// Alterna a visibilidade entre Cadastro e Detalhes
const toggleCadastro = () => {
    showView.value = !showView.value;

};

// Define a funcao para atualizar a lista de informacoess
const handleCadastro = () => {
    DadosSelecionados.value = null;
    showView.value = false; // Retorna para a tela de listagem

    fetch(); // Atualiza a lista de informacoess


};



// Define a informacoes selecionada
const dadoSelecionado = (dados) => {
    DadosSelecionados.value = dados; // Atribui a informacoes selecionada
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
