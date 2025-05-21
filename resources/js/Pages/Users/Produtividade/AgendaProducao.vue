<template>
    <LayoutFranqueado>

        <Head title="Painel" />
        <div class="painel-title">Agenda de produção</div>
        <div class="painel-subtitle">
            <p>Gerencie a produção da sua equipe</p>
        </div>

        <!-- Conteúdo da página -->
        <!-- <div class="mt-5 col-span-3">
      <Calendario />
    </div> -->
        <div class="mt-5">
            <CalendarAgenda />
        </div>
    </LayoutFranqueado>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import CalendarAgenda from '@/Components/EstruturaFranqueado/CalendarAgenda.vue';


</script>

<style lang="css" scoped>
.painel-title {
    font-size: 34px;
    line-height: 15px;
    font-weight: 700;
    color: #262a27;
    /* Cor escura para título */
    margin-bottom: 10px;
    /* Espaçamento inferior */
}

.painel-subtitle {
    font-size: 17px;
    line-height: 25px;
    color: #6d6d6e;
    /* Cor secundária */
    max-width: 600px;
    /* Limita a largura do subtítulo */
}

.compromissos-container {
    max-height: 170px;
    /* Defina a altura máxima desejada para a coluna */
    overflow-y: auto;
    /* Habilita rolagem vertical */
}

/* Esconde a barra de rolagem */
.compromissos-container::-webkit-scrollbar {
    display: none;
    /* Esconde a barra de rolagem no Chrome, Safari, e Edge */
}

.compromissos-container {
    -ms-overflow-style: none;
    /* Esconde a barra de rolagem no Internet Explorer */
    scrollbar-width: none;
    /* Esconde a barra de rolagem no Firefox */
}
</style>
