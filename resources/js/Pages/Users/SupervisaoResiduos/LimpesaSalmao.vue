<template>
  <LayoutFranqueado>
    <Head title="Limpeza de Salmão" />
    <div class="flex justify-between items-center mb-4">
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
          Limpeza de Salmão
        </div>
        <div class="painel-subtitle">
          <p class="text-sm sm:text-base md:text-lg">
            Registre o aproveitamento de salmão
          </p>
        </div>
      </div>
    </div>

    <div class="mt-5">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-lg px-12 py-5">
          <div class="flex flex-col flex-1">
            <LabelModel text="Responsável" class="mb-2" />
            <select v-model="form.responsavel_id" class="input-select" required>
              <option value="" disabled selected>
                Selecione um Responsável
              </option>
              <option
                v-for="responsavel in colaboradores"
                :key="responsavel.id"
                :value="responsavel.id"
              >
                {{ responsavel.name }}
              </option>
            </select>

            <LabelModel text="Calibre do Salmão" class="mb-2" />
            <select v-model="form.calibre_id" class="input-select" required>
              <option value="" disabled selected>Selecione um calibre</option>
              <option
                v-for="calibre in calibres"
                :key="calibre.id"
                :value="calibre.id"
              >
                {{ calibre.nome }}
              </option>
            </select>

            <LabelModel text="Fornecedor" class="mb-2" />
            <select v-model="form.fornecedor_id" class="input-select" required>
              <option value="" disabled selected>Selecione um fornecedor</option>
              <option
                v-for="fornecedor in fornecedores"
                :key="fornecedor.id"
                :value="fornecedor.id"
              >
                {{ fornecedor.razao_social }}
              </option>
            </select>
          </div>
        </div>

        <div class="bg-white rounded-lg px-12 py-5">
          <LabelModel text="Aproveitamento" />
          <div class="flex items-center -mt-9">
            <span
              :class="{
                'font-bold text-[120.01px] tracking-wider': true,
                'text-[#1d5915]': aproveitamento >= 70,
                'text-red-600': aproveitamento < 70, 
              }"
            >
              {{ aproveitamento }}%
            </span>
            <svg
              class="w-[40px] h-[40px] ml-2"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <polygon
                :points="
                  aproveitamento >= 70 ? '12,2 22,20 2,20' : '12,22 22,2 2,2'
                "
                :fill="aproveitamento >= 70 ? '#6DB631' : '#DC2626'"
              />
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-lg px-12 py-5">
          <div class="mt-4 flex flex-col gap-3">
            <!-- Valor Pago -->
            <div class="flex justify-between items-center px-4 py-2 rounded-lg">
              <LabelModel text="Valor Pago" class="text-gray-800" />
              <div class="relative flex items-center w-full">
                <input
                  v-model="form.valor_pago"
                  @input="formatarValor"
                  class="input-text"
                  placeholder="R$ 0,00"
                  required
                />
              </div>
            </div>

            <!-- Peso Bruto -->
            <div class="flex justify-between items-center px-4 py-2 rounded-lg">
              <LabelModel text="Peso Bruto" class="text-gray-800" />
              <input
                v-model="form.peso_bruto"
                @input="formatarPeso('peso_bruto', $event.target.value)"
                class="input-text"
                placeholder="0,000"
                required
              />
            </div>

            <!-- Peso Limpo -->
            <div class="flex justify-between items-center px-4 py-2 rounded-lg">
              <LabelModel text="Peso Limpo" class="text-gray-800" />
              <input
                v-model="form.peso_limpo"
                @input="formatarPeso('peso_limpo', $event.target.value)"
                class="input-text"
                placeholder="0,000"
                required
              />
            </div>
          </div>
        </div>

        <div class="rounded-lg px-12 py-5">
          <LabelModel text="Desperdício" />
          <div class="flex items-center -mt-5">
            <span
              class="font-bold text-[63.36px] text-[#424242] tracking-wider"
            >
              {{ desperdicio }} kg
            </span>
          </div>
          <div
            class="flex items-center gap-2 text-[#6d6d6d] text-[15px] font-semibold -mt-4"
          >
            <p>Equivalente a {{ desperdicioValor }}</p>
          </div>
        </div>
      </div>
      <div class="absolute bottom-4 right-4 botao-container">
        <ButtonPrimaryMedio
          text="Concluir"
          iconPath="/storage/images/arrow_left_alt.svg"
          @click="prosseguirAlteracao"
        />
      </div>

      <!-- Segunda Modal (Autenticação) -->
      <div
        v-if="showAuthModal"
        @click="fecharAuthModal"
        class="fixed top-0 left-0 right-0 bottom-0 bg-opacity-50 bg-gray-900 flex justify-center items-center"
      >
        <div
          @click.stop
          class="w-[405px] h-[523px] px-[23px] py-[33px] bg-white rounded-[20px] flex-col justify-center items-center gap-[68px] inline-flex"
        >
          <div class="w-[267px] h-[229.61px] relative">
            <div
              class="left-[-0px] top-[93.84px] absolute text-center text-[#262a27] text-[42.18px] font-bold font-['Figtree'] leading-[50.61px] tracking-wide"
            >
              Autenticação
              <br />
              Obrigatória
            </div>
            <div
              class="left-[20.03px] top-[205.61px] absolute text-center text-[#6d6d6d] text-lg font-normal font-['Figtree'] leading-[23.20px]"
            >
              Identifique-se para prosseguir
            </div>
            <div class="w-[83.46px] h-[83.46px] left-[91.74px] top-0 absolute">
              <div class="w-[83.46px] h-[83.46px] left-0 top-0 absolute">
                <img src="/storage/images/security.svg" alt="" />
              </div>
            </div>
          </div>
          <div class="w-[267px] h-[124.72px] relative">
            <div class="w-[267px] h-[43.91px] left-0 top-[80.80px] absolute">
              <ButtonPrimaryMedio
                @click="submitForm"
                text="Autenticar"
                class="w-full"
              />
            </div>
            <div
              class="w-full h-[42.16px] left-0 top-[30px] absolute opacity-80 bg-white rounded-lg border-2 border-[#d7d7db] justify-start items-center inline-flex overflow-hidden"
            >
              <input
                v-model="pin"
                type="password"
                class="w-full h-full text-xs font-normal tracking-widest border-none outline-none"
                placeholder="●●●●"
                autocomplete="off"
              />
            </div>
            <div
              class="w-[68.51px] left-0 top-0 absolute text-[#262a27] text-[14.93px] font-semibold font-['Figtree'] leading-tight"
            >
              Seu PIN
            </div>
          </div>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
import LabelModel from '@/Components/Label/LabelModel.vue';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const toast = useToast();
const colaboradores = ref([]);
const calibres = ref([]);
const fornecedores = ref([]);
const aproveitamento = ref(0);
const desperdicio = ref(0);
const desperdicioValor = ref('R$ 0,00');

// Modal
const pin = ref('');
const showAuthModal = ref(false);

const prosseguirAlteracao = () => {
  showAuthModal.value = true;
};

// Fechar a segunda modal
const fecharAuthModal = () => {
  showAuthModal.value = false;
};

const form = ref({
  responsavel_id: '',
  fornecedor_id: '',
  calibre_id: '',
  valor_pago: 'R$ 0,00', // Valor inicial formatado
  peso_bruto: '0,000', // Valor inicial formatado
  peso_limpo: '0,000', // Valor inicial formatado
});

// Carregar os dados da API ao montar o componente
onMounted(async () => {
  try {
    const response = await axios.get('/api/gestao-residuos/limpeza');
    colaboradores.value = response.data.colaboradores;
    calibres.value = response.data.calibres;
    fornecedores.value = response.data.fornecedores;
  } catch (error) {
    console.error('Erro ao carregar dados:', error);
  }
});

// Função para formatar valores monetários
const formatarValor = () => {
  let valor = form.value.valor_pago.replace(/\D/g, ''); // Remove não numéricos
  if (!valor) {
    form.value.valor_pago = 'R$ 0,00';
  } else {
    let inteiro = valor.slice(0, -2) || '0';
    let centavos = valor.slice(-2).padStart(2, '0');
    form.value.valor_pago = `R$ ${Number(inteiro).toLocaleString(
      'pt-BR'
    )},${centavos}`;
  }
  calcularDesperdicioValor();
};

// Função para formatar pesos (com vírgula e três casas decimais)
const formatarPeso = (campo, valorInput) => {
  let valor = valorInput.replace(/\D/g, ''); // Remove não numéricos
  if (!valor) {
    form.value[campo] = '0,000';
  } else {
    let inteiro = valor.slice(0, -3) || '0'; // Parte inteira
    let decimal = valor.slice(-3).padStart(3, '0'); // Parte decimal
    form.value[campo] = `${Number(inteiro).toLocaleString('pt-BR')},${decimal}`;
  }
  calcularAproveitamento();
};

// Função para limpar e converter peso para número
const parsePeso = (peso) => {
  return parseFloat(peso.replace(',', '.') || '0');
};

// Calcular aproveitamento e desperdício
const calcularAproveitamento = () => {
  const pesoBruto = parsePeso(form.value.peso_bruto);
  const pesoLimpo = parsePeso(form.value.peso_limpo);

  if (pesoBruto > 0) {
    aproveitamento.value = ((pesoLimpo / pesoBruto) * 100).toFixed(2);
    desperdicio.value = (pesoBruto - pesoLimpo).toFixed(3);
  } else {
    aproveitamento.value = 0;
    desperdicio.value = 0;
  }
  calcularDesperdicioValor();
};

// Calcular o valor do desperdício
const calcularDesperdicioValor = () => {
  const valorPago = parseFloat(
    form.value.valor_pago.replace(/[^\d,]/g, '').replace(',', '.') || '0'
  );
  const pesoBruto = parsePeso(form.value.peso_bruto);
  if (pesoBruto > 0) {
    const custoPorKg = valorPago / pesoBruto;
    const desperdicioKg = parseFloat(desperdicio.value);
    const valor = (custoPorKg * desperdicioKg).toFixed(2);
    desperdicioValor.value = `R$ ${valor.replace('.', ',')}`;
  } else {
    desperdicioValor.value = 'R$ 0,00';
  }
};

// Enviar o formulário
const submitForm = async () => {
  if (!pin.value) {
    toast.info('Por favor, insira seu PIN.');
    return;
  }
  try {
    console.log('PIN:', pin.value);

    const response = await axios.post('/api/gestao-residuos/adicionar', {
      pin: pin.value,
      responsavel_id: form.value.responsavel_id,
      fornecedor_id: form.value.fornecedor_id,
      calibre_id: form.value.calibre_id,
      valor_pago: parseFloat(
        form.value.valor_pago.replace(/[^\d,]/g, '').replace(',', '.')
      ),
      peso_bruto: parsePeso(form.value.peso_bruto),
      peso_limpo: parsePeso(form.value.peso_limpo),
      aproveitamento: parseFloat(aproveitamento.value),
      desperdicio: parseFloat(desperdicio.value),
    });
    toast.success('Operação realizada com sucesso!');
    console.log('Registro salvo:', response.data);
    // Resetar o formulário após sucesso
    form.value = {
      responsavel_id: '',
      calibre_id: '',
      fornecedor_id: '',
      valor_pago: 'R$ 0,00',
      peso_bruto: '0,000',
      peso_limpo: '0,000',
    };
    aproveitamento.value = 0;
    desperdicio.value = 0;
    desperdicioValor.value = 'R$ 0,00';
    showAuthModal.value = false;
  } catch (error) {
    if (error.response) {
      // Erro retornado pelo backend
      const { status, data } = error.response;
      if (status === 403) {
        toast.error('PIN incorreto. Verifique e tente novamente.');
      } else if (status === 400) {
        toast.error(
          data.error || 'ERRO: Ops! Chame o suporte se o erro continuar.'
        );
      } else {
        console.error('Erro:', error);
      }
    } else {
      // Outro tipo de erro (conexão, etc.)
      toast.error('Ops! Ouve um erro.');
    }
  }
};
</script>

<style scoped>
.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27;
  line-height: 80%;
}

.painel-subtitle {
  font-size: 17px;
  color: #6d6d6e;
  max-width: 600px;
}

.input-text {
  width: 100%;
  padding: 8px;
  background: transparent;
  border: 1px solid #262a27;
  border-radius: 8px;
  text-align: center;
  font-size: 16px;
  color: #262a27;
  font-family: 'Figtree', sans-serif;
}

.input-select {
  width: 100%;
  height: 44px;
  background: #f3f8f3;
  border: 2px solid #d7d7db;
  padding: 8px;
  font-size: 16px;
  font-weight: bold;
  color: #6db631;
  border-radius: 8px;
  outline: none;
}

.botao-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  display: flex;
  gap: 10px;
}
</style>
