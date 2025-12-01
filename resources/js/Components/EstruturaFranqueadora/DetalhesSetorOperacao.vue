<template>
    <div v-if="!isEditMode">
        <div class="w-full h-[180px] bg-white rounded-[20px] p-12 mb-4">
            <!-- Exibe informações da informacoes apenas quando não está no modo de edição -->
            <div class="flex items-center">
                <!-- Coluna da Imagem -->
                <div class="w-1/1 flex justify-center">
                    <img :src="iconePontuacao" alt="Ícone pontuação" class="w-20 h-20 p-2 rounded-md shadow-lg" />
                </div>
                <div class="w-2/3 pl-5">
                    <div class="text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[30px] tracking-tight">
                        {{ informacoes.name || 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full h-[200px] bg-white rounded-[20px] py-3 px-12">
            <LabelModel text="Pontuação" />
            <select v-model="pontuacaoSelecionada" @change="emitirPontuacao"
                class="p-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                <option disabled value="">Selecione a pontuação</option>
                <option v-for="n in 10" :key="'neg-' + n" :value="-n">-{{ n }}</option>
                <option v-for="n in 10" :key="'pos-' + n" :value="n">+{{ n }}</option>
            </select>


            <ButtonPrimaryMedio text="Salvar Critério" class="w-full mt-5" @click="atualizarPonto" />
            <div class="text-gray-700 text-sm text-center">As próximas execuções da produção já utilizarão os novos
                valores.</div>
        </div>

    </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, watch, computed } from 'vue';
import ButtonPrimaryMedio from '../Button/ButtonPrimaryMedio.vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import LabelModel from '../Label/LabelModel.vue';

const emit = defineEmits(['cancelar', 'pontuacaoSelecionada']);

const toast = useToast();

const props = defineProps({
    informacoes: {
        type: Object,
        required: true,
    },
});

const pontuacaoSelecionada = ref(props.informacoes.pontos || '');

// Sincroniza o valor local quando a prop mudar (ex: ao abrir a edição com pontuação diferente)
watch(() => props.informacoes.pontos, (novoVal) => {
    pontuacaoSelecionada.value = novoVal || '';
});

const emitirPontuacao = () => {
    emit('pontuacaoSelecionada', pontuacaoSelecionada.value);
};

const iconePontuacao = computed(() => {
    // Se a pontuação for 0 ou vazia, você pode definir uma imagem neutra ou deixar vazio
    if (!pontuacaoSelecionada.value || pontuacaoSelecionada.value === 0) {
        return '/storage/images/neutro.svg'; // ou algum outro ícone neutro, opcional
    }

    return pontuacaoSelecionada.value > 0
        ? '/storage/images/positivo.svg'
        : '/storage/images/negativo.svg';
});


const atualizarPonto = async () => {
  if (pontuacaoSelecionada.value === '' || pontuacaoSelecionada.value === null) {
    toast.error('Selecione uma pontuação válida antes de salvar.');
    return;
  }

  try {
    const formData = new FormData();
    formData.append('pontos', pontuacaoSelecionada.value);
    formData.append('_method', 'PUT');

    await axios.post(`/api/pontos/${props.informacoes.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    toast.success('Pontuação atualizada com sucesso!');
    emit('pontuacaoSelecionada', pontuacaoSelecionada.value);
    window.location.reload();
  } catch (error) {
    console.error('Erro ao atualizar pontuação:', error);
    toast.error('Erro ao atualizar pontuação.');
  }
};

const isEditMode = ref(false);
</script>


<style scoped></style>
