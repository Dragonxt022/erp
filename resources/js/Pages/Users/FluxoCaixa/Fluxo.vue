<template>
  <LayoutFranqueado>
    <Head title="Fluxo do Caixa" />
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
    </div>
    <div class="flex justify-between items-center mb-4">
      <!-- Coluna 1: Título e subtítulo -->
      <div>
        <div class="painel-title text-2xl sm:text-3xl md:text-4xl">
          Fluxo do caixa
        </div>
        <div class="painel-subtitle">
          <p class="text-sm sm:text-base md:text-lg">
            Acompanhe seu negócio em tempo real
          </p>
        </div>
      </div>
    </div>
    <div class="mt-5">
      <!-- Ajuste do grid para ser responsivo -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6">
        <!-- Coluna 1: Métodos de Pagamento -->
        <div>
          <div class="bg-white rounded-lg px-12 py-8">
            <table class="w-full">
              <thead>
                <tr>
                  <th
                    class="text-gray-500 text-left text-sm sm:text-base md:text-lg font-semibold font-['Figtree'] leading-snug"
                  >
                    Método de pagamento
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="metodo in metodosPagamento" :key="metodo.id">
                  <td class="py-3 flex items-center gap-5">
                    <img
                      v-if="
                        metodo.default_payment_method &&
                        metodo.default_payment_method.img_icon
                      "
                      :src="`/${metodo.default_payment_method.img_icon}`"
                      :alt="
                        metodo.default_payment_method.nome ||
                        'Método de pagamento'
                      "
                      class="w-8 h-8"
                    />
                    <div
                      class="w-fill text-[#262a27] text-[17px] font-semibold font-['Figtree'] leading-snug"
                    >
                      {{
                        metodo.default_payment_method?.nome || 'Desconhecido'
                      }}
                    </div>
                  </td>

                  <td class="py-2 w-ful">
                    <InputModel
                      v-model="metodo.total_vendas_metodos_pagamento"
                      @input="(event) => formatarValor(event, metodo)"
                      class="w-[120px]"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div
            class="w-full h-[60px] bg-[#d2fac3] rounded-bl-[10px] rounded-br-[10px] px-12 flex justify-between items-center"
          >
            <div
              class="text-[#1d5915] text-xl font-bold font-['Figtree'] leading-snug"
            >
              TOTAL
            </div>
            <div
              class="text-[#1d5915] text-xl font-bold font-['Figtree'] leading-snug"
            >
              {{ totalMetodosPagamento }}
            </div>
          </div>
        </div>

        <!-- Coluna 2: Canais de Venda, Valor Vendido, Pedidos -->
        <div>
          <div class="bg-white rounded-lg px-12 py-8">
            <table class="w-full">
              <thead>
                <tr>
                  <th></th>
                  <th
                    class="px-2 text-left text-gray-500 text-sm sm:text-base md:text-lg font-semibold font-['Figtree'] leading-snug"
                  >
                    Canais de venda
                  </th>

                  <th
                    class="text-gray-500 text-sm sm:text-base md:text-lg font-semibold font-['Figtree'] leading-snug"
                  >
                    Valor vendido
                  </th>
                  <th
                    class="text-gray-500 text-sm sm:text-base md:text-lg font-semibold font-['Figtree'] leading-snug"
                  >
                    Pedidos
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="canal in canaisVendas" :key="canal.id">
                  <td class="w-[85px]">
                    <img
                      v-if="
                        canal.default_canal_de_vendas &&
                        canal.default_canal_de_vendas.img_icon
                      "
                      :src="`/${canal.default_canal_de_vendas.img_icon}`"
                      :alt="
                        canal.default_canal_de_vendas.nome || 'Canal de venda'
                      "
                      class="h-7 w-17 fill-stone-950"
                    />
                  </td>
                  <td class="py-4 px-3 flex items-center gap-5">
                    <div
                      class="w-fill text-[#262a27] text-[17px] font-semibold font-['Figtree'] leading-snug w-[170px]"
                    >
                      {{
                        canal.default_canal_de_vendas?.nome || 'Desconhecido'
                      }}
                    </div>
                  </td>
                  <td class="py-2">
                    <InputModel
                      v-model="canal.total_vendas_canais_vendas"
                      @input="(event) => formatarCanal(event, canal)"
                      class="w-[120px]"
                      placeholder="R$ 0,00"
                    />
                  </td>
                  <td class="py-2">
                    <InputModel
                      v-model="canal.quantidade_vendas_canais_vendas"
                      placeholder="0"
                      class="w-[120px]"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div
            class="w-full h-[60px] bg-[#d2fac3] rounded-bl-[10px] rounded-br-[10px] px-12 flex justify-between items-center"
          >
            <!-- Coluna para o valor total -->
            <div
              class="text-[#1d5915] text-xl font-bold font-['Figtree'] leading-snug"
            >
              <span>Total:</span>
              {{ totalCanaisVendas }}
            </div>

            <!-- Coluna para quantidade de pedidos -->
            <div
              class="text-[#1d5915] text-xl font-bold font-['Figtree'] leading-snug"
            >
              <span>Pedidos:</span>
              {{ totalPedidosCanais }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-5 bottom-0 left-[250px] w-full max-w-screen-xl mx-auto">
      <div class="w-full h-[85px] p-5 flex justify-between items-center">
        <!-- Grupo de botões "Suprimento" e "Sangria" -->
        <div class="flex gap-[13px]">
          <!-- Botão Suprimento -->

          <ButtonSuave
            class="w-full"
            text="Suprimento"
            iconPath=""
            @click="abirModalSuprimento"
          />

          <!-- Botão Sangria -->

          <ButtonSuave
            class="w-full"
            text="Sangria"
            iconPath=""
            @click="AbrirRetirada"
          />
        </div>

        <!-- Botão Fechar Caixa -->
        <div class="flex justify-center items-center gap-2.5">
          <ButtonPrimaryMedio
            class="w-full max-w-[200px]"
            text="Fechar caixa"
            iconPath="/storage/images/arrow_left_alt.svg"
            @click="abrirModalConfirmacao"
          />
        </div>
      </div>
    </div>

    <!-- modal Suprimento -->
    <div
      v-if="showSuprimento"
      @click="fechaSuprimentoModal"
      class="fixed top-0 left-0 right-0 bottom-0 bg-opacity-50 bg-gray-900 flex justify-center items-center"
    >
      <div
        @click.stop
        class="w-[405px] h-auto px-[19px] py-[20px] bg-white rounded-[20px] flex flex-col justify-center items-center gap-[20px]"
      >
        <div class="w-[280px] h-auto relative">
          <div class="w-[83.46px] h-[83.46px] mx-auto mb-8">
            <img src="/storage/images/add_circle.svg" alt="" />
          </div>
          <div
            class="text-center text-[#262a27] text-[22px] font-bold font-['Figtree']"
          >
            <p>Quanto você vai adicionar?</p>
          </div>
        </div>

        <div class="w-[267px] h-auto">
          <!-- Input para o valor -->
          <div class="w-full mb-8">
            <input
              id="valor"
              v-model="valor_entrada"
              @input="valor_entrada = formatarValorBR(valor_entrada)"
              class="w-full h-[42.16px] bg-white rounded-lg border-2 border-[#d7d7db] px-2 text-[17px] outline-none text-center"
              placeholder="R$ 0,00"
            />
          </div>

          <!-- Input para o motivo -->
          <div class="w-full mb-8">
            <label
              for="motivo"
              class="block text-gray-800 text-[14.93px] font-semibold font-['Figtree']"
            >
              Informações adicionais
            </label>
            <textarea
              id="motivo"
              v-model="motivo"
              class="w-full h-24 bg-white rounded-lg border-2 border-[#d7d7db] px-2 py-1 text-[14px] outline-none resize-none"
              placeholder="Digite aqui..."
            ></textarea>
            <p
              class="text-right text-xs mt-1"
              :class="{
                'text-gray-500': motivo.length <= 50,
                'text-red-500': motivo.length > 50,
              }"
            >
              {{ motivo.length }}/50 caracteres
            </p>
          </div>

          <!-- Botão para salvar -->
          <div class="w-full mb-8">
            <ButtonPrimaryMedio
              @click="adicionarValor"
              text="Adicionar"
              class="w-full"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Sangria -->
    <div
      v-if="showSangria"
      @click="fechaRetirada"
      class="fixed top-0 left-0 right-0 bottom-0 bg-opacity-50 bg-gray-900 flex justify-center items-center"
    >
      <div
        @click.stop
        class="w-[405px] h-auto px-[19px] py-[20px] bg-white rounded-[20px] flex flex-col justify-center items-center gap-[20px]"
      >
        <div class="w-[280px] h-auto relative">
          <div class="w-[83.46px] h-[83.46px] mx-auto mb-8">
            <img src="/storage/images/do_not_disturb_on.svg" alt="" />
          </div>
          <div
            class="text-center text-[#262a27] text-[22px] font-bold font-['Figtree']"
          >
            <p>Quanto você vai retirar?</p>
          </div>
        </div>

        <div class="w-[267px] h-auto">
          <!-- Input para o valor -->
          <div class="w-full mb-8">
            <input
              id="valor"
              v-model="valor_retirada"
              @input="valor_retirada = formatarValorBR(valor_retirada)"
              class="w-full h-[42.16px] bg-white rounded-lg border-2 border-[#d7d7db] px-2 text-[17px] outline-none text-center"
              placeholder="R$ 0,00"
            />
            <div
              class="text-center text-gray-800 text-[12.93px] font-semibold font-['Figtree'] mt-1"
            >
              <p>
                Valor disponível no caixa:
                <span class="font-bold">
                  {{ valorTotalCaixa }}
                </span>
              </p>
            </div>
          </div>

          <!-- Input para o motivo -->
          <div class="w-full mb-8">
            <label
              for="motivo"
              class="block text-gray-800 text-[14.93px] font-semibold font-['Figtree']"
            >
              Informações adicionais
            </label>
            <textarea
              id="motivo"
              v-model="motivo"
              class="w-full h-24 bg-white rounded-lg border-2 border-[#d7d7db] px-2 py-1 text-[14px] outline-none resize-none"
              placeholder="Digite aqui..."
            ></textarea>
            <p
              class="text-right text-xs mt-1"
              :class="{
                'text-gray-500': motivo.length <= 50,
                'text-red-500': motivo.length > 50,
              }"
            >
              {{ motivo.length }}/50 caracteres
            </p>
          </div>

          <!-- Botão para salvar -->
          <div class="w-full mb-8">
            <ButtonPrimaryMedioRed
              @click="retirarValor"
              text="Retirar"
              class="w-full"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmação fechamento de caixa -->
    <div
      v-if="showConfirmacao"
      class="fixed top-0 left-0 right-0 bottom-0 bg-opacity-50 bg-gray-900 flex justify-center items-center"
    >
      <div
        @click.stop
        class="w-[405px] h-auto px-[19px] py-[20px] bg-white rounded-[20px] flex flex-col justify-center items-center gap-[20px]"
      >
        <div class="w-[280px] h-auto relative p-5">
          <div class="w-[83.46px] h-[83.46px] mx-auto mb-5">
            <img src="/storage/images/thumb_up_confirmar.svg" alt="" />
          </div>
          <div
            class="text-center text-[#262a27] text-[22px] font-bold font-['Figtree']"
          >
            <p>Confirmar fechamento do caixa de hoje?</p>
          </div>
        </div>

        <div class="w-[267px] h-auto">
          <!-- Botão para salvar -->
          <div class="w-full">
            <ButtonPrimaryMedio
              @click="ConfirmarFechamentoDeCaixa"
              text="Confirmar"
              class="w-full"
            />
          </div>

          <div
            class="mt-3 text-center text-[#ff2d55] text-[15px] font-bold font-['Figtree']"
            @click="fechaModalConfirmacao"
          >
            <p class="cursor-pointer">Corrigir informações?</p>
          </div>
        </div>
      </div>
    </div>
  </LayoutFranqueado>
</template>

<script setup>
import LayoutFranqueado from '@/Layouts/LayoutFranqueado.vue';
import { Head } from '@inertiajs/vue3';
import InputModel from '@/Components/Inputs/InputModel.vue';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import ButtonSuave from '@/Components/Button/ButtonSuave.vue';
import { onMounted, ref, computed } from 'vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';
import ButtonPrimaryMedioRed from '@/Components/Button/ButtonPrimaryMedioRed.vue';

const toast = useToast();
const isLoading = ref(false);
const metodosPagamento = ref([]);
const canaisVendas = ref([]);

// Constantes das modais
const showSuprimento = ref(false);
const showSangria = ref(false);
const showConfirmacao = ref(false);

const motivo = ref('');

const valor_retirada = ref(null);
const valor_entrada = ref(null);

const valorTotalCaixa = ref('R$ 0,00');

const totalMetodosPagamento = computed(() => {
  const total = metodosPagamento.value.reduce(
    (acc, metodo) =>
      acc +
        parseFloat(
          metodo.total_vendas_metodos_pagamento
            .replace('R$', '')
            .replace(/\./g, '')
            .replace(',', '.')
        ) || 0,
    0
  );

  // Retorna o valor formatado como moeda brasileira
  return `R$ ${total.toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
});

// Computed para total de canais de vendas
const totalCanaisVendas = computed(() => {
  const total = canaisVendas.value.reduce(
    (acc, canal) =>
      acc +
        parseFloat(
          canal.total_vendas_canais_vendas
            .replace('R$', '')
            .replace(/\./g, '')
            .replace(',', '.')
        ) || 0,
    0
  );

  // Retorna o valor formatado como moeda brasileira
  return `R$ ${total.toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
});

const totalPedidosCanais = computed(() => {
  return canaisVendas.value.reduce(
    (acc, canal) =>
      acc + (parseInt(canal.quantidade_vendas_canais_vendas) || 0),
    0
  );
});

// Adiciona Suprimentos ao caixa
const adicionarValor = () => {
  // Verifica se o motivo está vazio ou tem mais de 50 caracteres
  if (!motivo.value.trim()) {
    toast.warning('Motivo não pode estar vazio!');
    return;
  }

  if (motivo.value.length > 50) {
    toast.warning('Motivo excede o limite de 50 caracteres!');
    return;
  }

  // Limpar o valor "R$" e a vírgula, e transformar em número
  const valor = parseFloat(
    valor_entrada.value
      .replace('R$', '')
      .replace('.', '')
      .replace(',', '.')
      .trim()
  );

  if (isNaN(valor)) {
    toast.warning('Valor inválido!');
    return;
  }

  // Enviar os dados para a rota
  axios
    .post('/api/caixas/adicionar-suprimento', {
      valor: valor,
      motivo: motivo.value,
    })
    .then((response) => {
      // Sucesso na requisição
      console.log('Sucesso:', response.data);
      toast.success('Suprimento adicionado com sucesso!');
    })
    .catch((error) => {
      // Erro na requisição
      console.error('Erro:', error);
      toast.error('Erro ao adicionar suprimento.');
    });

  // Limpar os campos após o envio
  valor_entrada.value = '';
  motivo.value = '';
  showSuprimento.value = false;
};

// Remove o valor do caixa
const retirarValor = () => {
  if (motivo.value.length < 5) {
    toast.warning('Precisa conter no minimo 5 caracteres!');
    return;
  }

  if (motivo.value.length > 50) {
    toast.warning('Excede o limite de 50 caracteres!');
    return;
  }

  // Limpar o valor "R$" e a vírgula, e transformar em número
  const valor = parseFloat(
    valor_retirada.value
      .replace('R$', '')
      .replace('.', '')
      .replace(',', '.')
      .trim()
  );

  if (isNaN(valor)) {
    toast.warning('Valor inválido!');
    return;
  }

  // Enviar os dados para a rota
  axios
    .post('/api/caixas/remover-suprimento', {
      valor: valor,
      motivo: motivo.value,
    })
    .then((response) => {
      // Sucesso na requisição
      console.log('Sucesso:', response.data);
      toast.success('Sangria realizada com sucesso!');
    })
    .catch((error) => {
      // Erro na requisição
      console.error('Erro:', error);
      toast.error('Erro ao realizar a Sangria.');
    });

  // Lógica para enviar o valor e motivo
  console.log('Valor retirado:', valor);
  console.log('Motivo:', motivo.value);

  // Limpar os campos após o envio
  valor_retirada.value = '';
  motivo.value = '' || 'Não informado';
  showSangria.value = false;
};

const buscarValorCaixa = async () => {
  try {
    const response = await axios.get('/api/caixas/valor-disponivel');
    if (response.data.status === 'success') {
      valorTotalCaixa.value = `R$ ${response.data.data.valor_disponivel}`;
    } else {
      toast.error('Erro ao carregar o valor do caixa.');
    }
  } catch (error) {
    console.error('Erro ao buscar valor do caixa:', error);
    toast.error('Erro ao carregar o valor do caixa.');
  }
};

// Abre a modal de Suprimentos
const abirModalSuprimento = () => {
  showSuprimento.value = true; // Corrigido para alterar a variável correta
};

// Fecha a modal de suprimentos
const fechaSuprimentoModal = () => {
  showSuprimento.value = false; // Corrigido para alterar a variável correta
  motivo.value = '';
  valor_entrada.value = null;
};

// Abrir modal de confirmação de fechamento de caixa
const abrirModalConfirmacao = () => {
  showConfirmacao.value = true; // Corrigido para alterar a variável correta
};

// Fecha modal de confirmação de fechamento de caixa
const fechaModalConfirmacao = () => {
  showConfirmacao.value = false; // Corrigido para alterar a variável correta
};

const ConfirmarFechamentoDeCaixa = () => {
  showConfirmacao.value = false;
  enviarFechamentoCaixa();
};

// Modal de retirada
const AbrirRetirada = () => {
  showSangria.value = true; // Corrigido para alterar a variável correta
  buscarValorCaixa();
};

// Fecha a modal de retirada
const fechaRetirada = () => {
  showSangria.value = false; // Corrigido para alterar a variável correta
  motivo.value = '';
  valor_retirada.value = null;
};

// Formata o valor BR
const formatarValorBR = (valor) => {
  let numero = valor.replace(/\D/g, ''); // Remove não-numéricos
  if (!numero) numero = '0';

  const inteiro = numero.slice(0, -2) || '0';
  const centavos = numero.slice(-2);

  return `R$ ${Number(inteiro).toLocaleString('pt-BR')},${centavos}`;
};

const formatarValor = (event, metodo) => {
  const input = event.target;
  let valor = input.value.replace(/\D/g, ''); // Remove tudo que não for número

  // Define o valor como "0" caso esteja vazio
  if (!valor) {
    valor = '0';
  }

  // Divide o valor em parte inteira e centavos
  const inteiro = valor.slice(0, -2) || '0'; // Parte inteira (antes dos dois últimos dígitos)
  const centavos = valor.slice(-2).padStart(2, '0'); // Parte decimal (os dois últimos dígitos)

  // Formatação do valor para exibição no formato brasileiro
  const valorFormatado = `R$ ${Number(inteiro).toLocaleString(
    'pt-BR'
  )},${centavos}`;

  // Aplica a formatação no campo de entrada
  input.value = valorFormatado;

  // Atualiza o modelo com a string formatada (mantendo-a como uma string)
  metodo.total_vendas_metodos_pagamento = valorFormatado;
};

const formatarCanal = (event, canal) => {
  const input = event.target;
  let valor = input.value.replace(/\D/g, ''); // Remove tudo que não for número

  // Define o valor como "0" caso esteja vazio
  if (!valor) {
    valor = '0';
  }

  // Divide o valor em parte inteira e centavos
  const inteiro = valor.slice(0, -2) || '0'; // Parte inteira (antes dos dois últimos dígitos)
  const centavos = valor.slice(-2).padStart(2, '0'); // Parte decimal (os dois últimos dígitos)

  // Formatação do valor para exibição no formato brasileiro
  const valorFormatado = `R$ ${Number(inteiro).toLocaleString(
    'pt-BR'
  )},${centavos}`;

  // Aplica a formatação no campo de entrada
  input.value = valorFormatado;

  // Atualiza o modelo com a string formatada (mantendo-a como uma string)
  canal.total_vendas_canais_vendas = valorFormatado;
};

// Função para buscar os métodos e canais ativos
const buscarMetodosCanaisAtivos = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/caixas/metodos-canais-ativos');
    if (response.data.status === 'success') {
      console.log('Métodos de pagamento:', response.data.metodosPagamento);
      console.log('Canais de vendas:', response.data.canaisVendas);

      metodosPagamento.value = response.data.metodosPagamento
        .filter((metodo) => metodo.default_payment_method?.img_icon) // Filtra itens inválidos
        .map((metodo) => ({
          ...metodo,
          total_vendas_metodos_pagamento: 'R$ 0,00',
        }));

      canaisVendas.value = response.data.canaisVendas
        .filter((canal) => canal.default_canal_de_vendas?.img_icon) // Filtra itens inválidos
        .map((canal) => ({
          ...canal,
          total_vendas_canais_vendas: 'R$ 0,00',
          quantidade_vendas_canais_vendas: 0,
        }));
    } else {
      toast.error('Erro ao carregar os dados');
    }
  } catch (error) {
    console.error('Erro ao buscar métodos e canais ativos:', error);
    toast.error('Erro ao carregar os dados');
  } finally {
    isLoading.value = false;
  }
};

// Função para enviar os dados para o backend
const enviarFechamentoCaixa = async () => {
  isLoading.value = true;
  try {
    const response = await axios.post('/api/caixas/fechar-caixa', {
      metodos: metodosPagamento.value,
      canais: canaisVendas.value,
      total_metodos_pagamento: totalMetodosPagamento.value,
      total_canais_vendas: totalCanaisVendas.value,
    });

    if (response.data.status === 'success') {
      toast.success('Fechamento realizado com sucesso!');
      Inertia.visit(route('franqueado.abrirCaixa'));
    } else {
      toast.error('Erro ao realizar o fechamento');
    }
  } catch (error) {
    console.error('Erro ao enviar fechamento de caixa:', error);
    toast.error('Erro ao realizar o fechamento');
  } finally {
    isLoading.value = false;
  }
};

// Função para verificar se já existe um caixa aberto
const verificarCaixaAberto = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/caixas/abertos');
    if (response.data && response.data.aberto) {
      console.log('caixa aberto, permanecendo na pagina');
    } else {
      toast.warning('Abra o caixa primeiro!');
      Inertia.visit(route('franqueado.abrirCaixa'));
    }
  } catch (error) {
    console.error('Erro ao verificar caixa aberto:', error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  verificarCaixaAberto();
  buscarMetodosCanaisAtivos();
});
</script>

<style lang="css" scoped>
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
  border: 4px solid #f3f3f3; /* Cor de fundo */
  border-top: 4px solid #6db631; /* Cor do topo */
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
.scroll-hidden {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE 10+ */
}

.scroll-hidden::-webkit-scrollbar {
  display: none; /* Chrome, Safari, Edge */
}

.painel-title {
  font-size: 34px;
  font-weight: 700;
  color: #262a27; /* Cor escura para título */
  line-height: 80%;
}

.painel-subtitle {
  font-size: 17px;
  color: #6d6d6e; /* Cor secundária */
  max-width: 600px; /* Limita a largura do subtítulo */
}
/* Estilizando a tabela */
</style>
