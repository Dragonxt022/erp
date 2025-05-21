<!-- Calendar.vue -->
<template>
    <div class="flex flex-col gap-4">
        <!-- Navegação de mês -->
        <div v-if="currentDate" class="flex items-center justify-end text-xl font-bold text-gray-700 gap-5 mr-12">
            <button @click="prevMonth" class="text-indigo-600 hover:underline">
                <img src="/storage/images/arrow_drop_down_circle.svg" alt="icone drop" class="w-5 h-5" />
            </button>
            {{ safeFormat(currentDate, "MMMM 'de' yyyy") }}
            <button @click="nextMonth" class="text-indigo-600 hover:underline">
                <img src="/storage/images/arrow_drop_direita_circle.svg" alt="icone drop" class="w-5 h-5" />
            </button>
        </div>

        <!-- Container principal -->
        <div class="calendar-container flex h-[calc(100vh-160px)] gap-4">
            <!-- Grade do calendário -->
            <div class="grid grid-cols-7 gap-2 flex-1">
                <!-- Cabeçalhos -->
                <div v-for="(wd, i) in weekDays" :key="i"
                    class="text-center text-[20px] text-[#949494] font-semibold py-2 py-7 bg-[#EFEFEF] rounded-lg">
                    {{ wd }}
                </div>

                <!-- Células de dia -->
                <div v-for="date in days" :key="date.toISOString()" @click="selectDate(date)" class="h-28 p-3 cursor-pointer bg-white rounded-lg
                 flex flex-col items-center justify-center transition-transform transform hover:scale-105
                 transition-colors" :class="{
                    'opacity-40': !isSameMonth(date, monthStart),
                    'ring-2 ring-green-400': isSameDay(date, selectedDate),
                }">
                    <div class="text-[30px] font-extrabold">
                        {{ format(date, 'd') }}
                    </div>
                    <div class="text-[17px] font-bold text-gray-600">
                        {{ getBlocoCount(date) }} blocos
                    </div>

                    <!-- Barra de status -->
                    <div v-if="getBlocoCount(date) > 0" class="w-20 h-2 mt-2 rounded" :class="{
                        'bg-green-500': getBlocoCount(date) <= 5,
                        'bg-yellow-400': getBlocoCount(date) > 5 && getBlocoCount(date) <= 17,
                        'bg-red-500': getBlocoCount(date) > 17
                    }"></div>
                </div>
            </div>

            <!-- Sidebar com botão fixo embaixo -->
            <div class="w-80 bg-white rounded-lg p-5 flex flex-col justify-between ">

                <div>
                    <h2 class="font-bold mb-3 text-center text-lg text-gray-700 text-uppercase">
                        {{ format(selectedDate, "dd 'de' MMMM", { locale: ptBR }) }}
                    </h2>
                    <ul class="compromissos-container max-h-[calc(100vh-160px)] overflow-y-auto">
                        <li v-for="bloco in blocosDoDia" :key="bloco.id"
                            class="border-b py-2 flex justify-between items-center overflow-hidden">
                            <div>
                                <p class="font-semibold">{{ bloco.title }}</p>
                                <p class="text-sm text-gray-500">
                                    <span>
                                        <img src="/storage/images/account_circle.svg" alt="icone user"
                                            class="w-4 h-4 inline " />
                                    </span>
                                    {{ bloco.owner }}
                                </p>
                            </div>
                            <button @click="reloadBloco(bloco.id)">
                                <img src="/storage/images/loop.svg" alt="icone loop" class="w-5 h-5" />
                            </button>

                        </li>
                    </ul>
                </div>
                <ButtonPrimaryMedio class="mt-4 w-full py-2" text="Criar novo bloco" @click="createBloco" />

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, defineEmits } from 'vue'
import {
    startOfMonth, endOfMonth,
    startOfWeek, endOfWeek,
    addDays, addMonths,
    isSameMonth, isSameDay,
    format
} from 'date-fns'
import { ptBR } from 'date-fns/locale'

import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue'


// Estado
const today = new Date()
const currentDate = ref(new Date())
const selectedDate = ref(new Date())
const blocosDoDia = ref([])

// Dias da semana
const weekDays = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB']

// Datas para grid
const monthStart = computed(() => startOfMonth(currentDate.value))
const monthEnd = computed(() => endOfMonth(currentDate.value))
const gridStart = computed(() => startOfWeek(monthStart.value, { weekStartsOn: 0 }))
const gridEnd = computed(() => endOfWeek(monthEnd.value, { weekStartsOn: 0 }))

const days = computed(() => {
    const arr = []
    let dt = gridStart.value
    while (dt <= gridEnd.value) {
        arr.push(dt)
        dt = addDays(dt, 1)
    }
    return arr
})

function reloadBloco(id) {
    const key = format(selectedDate.value, 'yyyy-MM-dd')
    const blocos = blocosMock[key] || []
    console.log(`Recarregar bloco ${id} do dia ${key}`, blocos)
    // Aqui você poderia simular alguma atualização ou chamada à API.
}

function createBloco() {
    const key = format(selectedDate.value, 'yyyy-MM-dd')
    if (!blocosMock[key]) blocosMock[key] = []

    const novoId = blocosMock[key].length + 1
    blocosMock[key].push({
        id: Date.now(), // ou uuid se quiser algo mais robusto
        title: `Bloco #${novoId}`,
        owner: 'Usuário Atual',
    })

    blocosDoDia.value = [...blocosMock[key]]
}


// Formatação de data
function safeFormat(date, fmt) {
    if (!(date instanceof Date) || isNaN(date.getTime())) return ''
    return format(date, fmt, { locale: ptBR })
}





// Mock de blocos
const blocosMock = {
    '2025-05-28': [
        { id: 1, title: 'Bloco #1', owner: 'Bárbara Santos' },
        { id: 2, title: 'Bloco #2', owner: 'Wesley Silva' },
    ],
}

watch(selectedDate, date => {
    const key = format(date, 'yyyy-MM-dd')
    blocosDoDia.value = blocosMock[key] || []
})

function getBlocoCount(date) {
    const key = format(date, 'yyyy-MM-dd')
    return (blocosMock[key] || []).length
}
function hasStatus(date) {
    const count = getBlocoCount(date)
    if (count === 0) return null
    if (count < 5) return 'green'
    if (count < 8) return 'yellow'
    return 'red'
}
function selectDate(date) {
    selectedDate.value = date
}
function prevMonth() {
    currentDate.value = addMonths(currentDate.value, -1)
}
function nextMonth() {
    currentDate.value = addMonths(currentDate.value, +1)
}
</script>

<style scoped>
.calendar-container {
    height: 100%;
}

.compromissos-container {
  max-height: 550px; /* Defina a altura máxima desejada para a coluna */
  overflow-y: auto; /* Habilita rolagem vertical */
}

/* Esconde a barra de rolagem */
.compromissos-container::-webkit-scrollbar {
  display: none; /* Esconde a barra de rolagem no Chrome, Safari, e Edge */
}

.compromissos-container {
  -ms-overflow-style: none; /* Esconde a barra de rolagem no Internet Explorer */
  scrollbar-width: none; /* Esconde a barra de rolagem no Firefox */
}
</style>
