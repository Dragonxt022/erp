<template>
  <LayoutFranqueadoEstoque>
    <!-- Cabeçalho da página -->
    <Head title="Controle de Estoque" />
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
    </div>
    <!-- Campo de pesquisa -->
    <div
      class="search-container relative flex items-center mb-4 justify-self-end"
    >
      <!-- Ícone de pesquisa -->
      <div class="absolute left-3">
        <img
          src="/storage/images/search.svg"
          alt="Ícone de pesquisa"
          class="w-5 h-5 text-gray-500"
        />
      </div>
      <!-- Campo de pesquisa -->
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Buscar um produto"
        class="search-input pl-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 w-[500px]"
      />

      <!-- Ícone de tela cheia -->

      <div
        id="fullscreen-icon"
        class="ml-5 cursor-pointer text-gray-500 hover:text-green-500"
        title="Tela cheia"
      >
        <img src="/storage/images/fullscreen.svg" alt="fullscreen" />
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed bottom-0 left-0 right-0 w-full h-[148px] bg-white border-t-4 border-[#5a9f28] shadow-lg transform transition-all duration-500 ease-out"
      :class="showModal ? 'translate-y-0' : 'translate-y-full'"
    >
      <div class="flex justify-between items-center h-full px-6">
        <!-- Mensagem -->
        <div
          class="w-[291px] h-[58.17px] text-black text-[28px] font-bold font-['Figtree'] leading-[34px] tracking-tight"
        >
          Prosseguir com a
          <br />
          alteração de estoque?
        </div>

        <!-- Botões -->
        <div class="flex gap-4">
          <ButtonCancelar @click="fecharModal" text="Cancelar" />

          <ButtonPrimaryMedio
            text="Continuar"
            iconPath="/storage/images/arrow_left_alt.svg"
            @click="prosseguirAlteracao"
          />
        </div>
      </div>
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
              @click="enviarCarrinho"
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

    <div class="grid">
      <table class="min-w-full table-auto">
        <thead>
          <tr>
            <th
              class="px-4 py-4 text-xs text-left font-medium text-gray-500 uppercase tracking-wider TrRedonEsquerda"
              @click="ordenarProdutos"
            >
              <div class="flex items-center gap-2">
                <span>Item</span>
                <img
                  src="/storage/images/sync_alt.svg"
                  class="w-[19px] h-[19px]"
                />
              </div>
            </th>
            <th
              class="px-4 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Estoque
            </th>
            <th
              class="px-4 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider TrRedonDireita"
            >
              Retirada
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="produto in filteredProdutos" :key="produto.id">
            <!-- Nome do Item -->
            <td class="text-[16px] text-left text-gray-900 font-semibold">
              <div class="flex items-center gap-4">
                <img
                  :src="`/${produto.profile_photo}`"
                  alt="Imagem do Produto"
                  class="w-12 h-12"
                />
                <span>{{ produto.nome }}</span>
              </div>
            </td>

            <!-- Estoque -->
            <td
              class="text-[16px] text-gray-900 font-semibold text-center border-l border-r border-[#c4c4c4]"
            >
              {{ produto.quantidade_total }}
              {{ produto.unidadeDeMedida === 'a_granel' ? 'KG' : 'UN' }}
            </td>

            <!-- Retirada -->
            <td class="text-[16px] text-gray-900 font-semibold text-center">
              <div
                class="relative w-[300px] h-[56.76px] mx-auto flex items-center justify-between bg-[#efeff4] rounded-xl px-2"
              >
                <!-- Botão de menos -->
                <button
                  class="w-[40px] h-[30px] flex justify-center items-center text-[#6d6d6d] rounded-full"
                  @click="alterarCarrinho(produto.id, -1)"
                  :disabled="carrinho[produto.id]?.quantidade <= 0"
                >
                  <span class="text-[50px] font-bold">−</span>
                </button>

                <!-- Input numérico -->
                <input
                  type="number"
                  class="w-[150px] h-[42px] text-xl text-center text-[#6d6d6d] bg-transparent border border-gray-100 rounded"
                  :value="carrinho[produto.id]?.quantidade ?? 0"
                  @input="
                    (event) =>
                      atualizarQuantidade(produto.id, event.target.value)
                  "
                  :max="produto.quantidade_total"
                  :aria-invalid="isInvalid(produto.id) ? 'true' : 'false'"
                  step="0.01"
                />

                <!-- Botão de mais -->
                <button
                  class="w-[40px] h-[30px] flex justify-center items-center"
                  @click="alterarCarrinho(produto.id, 1)"
                  :disabled="
                    carrinho[produto.id]?.quantidade >= produto.quantidade_total
                  "
                >
                  <span>
                    <img
                      class="w-[28px] h-[28px]"
                      src="/storage/images/Plus_verde.svg"
                    />
                  </span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </LayoutFranqueadoEstoque>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import LayoutFranqueadoEstoque from '@/Layouts/LayoutFranqueadoEstoque.vue';
import ButtonPrimaryMedio from '@/Components/Button/ButtonPrimaryMedio.vue';
import ButtonCancelar from '@/Components/Button/ButtonCancelar.vue';

const toast = useToast();

// Dados dos produtos e estado do carrinho
const produtos = ref([]);
const carrinho = ref({});
const searchQuery = ref('');
let ordenacaoInvertida = ref(false);
const showModal = ref(false);
const showAuthModal = ref(false);
const isLoading = ref(false);

const pin = ref('');

// Carregar lista de produtos
const fetchProdutos = async () => {
  try {
    const response = await axios.get('/api/estoque/lista-produtos');
    produtos.value = Object.values(response.data.produtos);
  } catch (error) {
    console.error('Erro ao carregar os produtos:', error);
  }
};

// Manipular o carrinho
const alterarCarrinho = (produtoId, quantidade) => {
  const produto = produtos.value.find((p) => p.id === produtoId);
  if (!produto) return;

  const itemCarrinho = carrinho.value[produtoId] || {
    id: produto.id,
    nome: produto.nome,
    quantidade: 0,
  };

  itemCarrinho.quantidade += quantidade;

  if (itemCarrinho.quantidade > 0) {
    carrinho.value[produtoId] = itemCarrinho;
    showModal.value = true;
  } else {
    delete carrinho.value[produtoId];
  }
};

// Função para verificar se a quantidade do produto é inválida
const isInvalid = (produtoId) => {
  const itemCarrinho = carrinho.value[produtoId];
  const produto = produtos.value.find((p) => p.id === produtoId);
  return (
    itemCarrinho &&
    (itemCarrinho.quantidade < 0 ||
      itemCarrinho.quantidade > produto.quantidade_total)
  );
};

// Atualizar quantidade no carrinho
const atualizarQuantidade = (produtoId, quantidade) => {
  const produto = produtos.value.find((p) => p.id === produtoId);
  if (!produto) {
    toast.error('Produto não encontrado.');
    return;
  }

  // Remove caracteres inválidos e converte vírgula para ponto
  quantidade = quantidade.replace(',', '.');

  // Converte a quantidade para número decimal
  let quantidadeNumerica = parseFloat(quantidade);

  // Valida se a quantidade é um número válido
  if (isNaN(quantidadeNumerica)) {
    toast.warning('Quantidade inválida.');
    return;
  }

  // Se a unidade for 'a_granel', permite números fracionados (3 casas decimais)
  if (produto.unidadeDeMedida === 'a_granel') {
    // Limita a 3 casas decimais para quantidades fracionadas
    quantidadeNumerica = parseFloat(quantidadeNumerica.toFixed(3));
  } else {
    // Para unidades, apenas números inteiros são permitidos
    quantidadeNumerica = Math.floor(quantidadeNumerica); // Força a quantidade para inteiro
  }

  // Verifica se a quantidade está dentro do limite permitido
  if (quantidadeNumerica < 0 || quantidadeNumerica > produto.quantidade_total) {
    toast.warning(
      `Quantidade inválida. Deve ser entre 0 e ${produto.quantidade_total} ${
        produto.unidadeDeMedida === 'a_granel' ? 'kg' : 'unidades'
      }.`
    );
    return;
  }

  // Atualiza a quantidade no carrinho
  carrinho.value[produtoId] = {
    ...carrinho.value[produtoId],
    id: produto.id,
    nome: produto.nome,
    quantidade: quantidadeNumerica,
  };

  showModal.value = true;

  // Remove o item do carrinho se a quantidade for 0 ou NaN
  if (quantidadeNumerica === 0 || isNaN(quantidadeNumerica)) {
    delete carrinho.value[produtoId];
  }
};

// Carregar produtos ao montar o componente
onMounted(() => {
  const fullscreenIcon = document.getElementById('fullscreen-icon');

  if (fullscreenIcon) {
    fullscreenIcon.addEventListener('click', () => {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
      } else {
        document.exitFullscreen();
      }
    });
  }
  fetchProdutos();
});

// Produtos formatados
const produtosListados = computed(() =>
  produtos.value.map((produto) => ({
    ...produto,
    quantidade_total: produto.quantidade_total || 0,
  }))
);

// Filtrando os produtos com base na pesquisa
const filteredProdutos = computed(() =>
  produtosListados.value.filter((produto) =>
    produto.nome.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
);

// Ordenar os produtos com base na inversão da ordem
const ordenarProdutos = () => {
  ordenacaoInvertida.value = !ordenacaoInvertida.value;
  produtos.value = [...produtos.value].sort((a, b) => {
    if (ordenacaoInvertida.value) {
      return b.nome.localeCompare(a.nome); // Ordem invertida
    } else {
      return a.nome.localeCompare(b.nome); // Ordem normal
    }
  });
};

// Fechar a modal
const fecharModal = () => {
  showModal.value = false;
};

// Ação ao prosseguir com a alteração
const prosseguirAlteracao = () => {
  // Fecha a primeira modal e abre a segunda
  showModal.value = false;
  showAuthModal.value = true;
};

// Fechar a segunda modal
const fecharAuthModal = () => {
  showAuthModal.value = false;
};

// Enviar carrinho para o backend
const enviarCarrinho = async () => {
  if (!pin.value) {
    toast.info('Por favor, insira seu PIN.');
    return;
  }

  const carrinhoData = Object.values(carrinho.value);
  if (carrinhoData.length === 0) {
    toast.warning('O carrinho está vazio. Adicione itens antes de continuar.');
    return;
  }

  isLoading.value = true;

  try {
    console.log('Itens do carrinho:', carrinhoData);
    console.log('PIN:', pin.value);

    const response = await axios.post('/api/estoque/consumir-estoque', {
      itens: carrinhoData,
      pin: pin.value, // Envia o PIN junto com os itens do carrinho
    });

    if (response.status === 200) {
      toast.success(response.data.message);
      carrinho.value = {}; // Limpa o carrinho
      pin.value = ''; // Limpa o PIN após o envio
      fecharAuthModal(); // Fecha a modal de autenticação

      // Redirecionar para a página de login
      if (response.data.redirect_url) {
        window.location.href = response.data.redirect_url;
      }
    } else {
      console.error('Erro:', error);
    }
  } catch (error) {
    toast.error('Ouve um erro ao tentar enviar');

    if (error.response) {
      // Erro retornado pelo backend
      const { status, data } = error.response;
      if (status === 403) {
        toast.error('PIN incorreto. Verifique e tente novamente.');
      } else if (status === 400) {
        toast.error(
          data.error || 'Estoque insuficiente para um ou mais itens.'
        );
      } else {
        console.error('Erro:', error);
      }
    } else {
      // Outro tipo de erro (conexão, etc.)
      toast.error('Ops! Ouve um erro.');
    }
  } finally {
    isLoading.value = false; // Desativa o indicador de carregamento
  }
};
</script>

<!-- estilos -->
<style lang="css" scoped>
.fixed {
  z-index: 1000;
}
/* Estilizando a tabela */
table {
  margin-top: px;
  border-collapse: collapse;
}

th,
td {
  padding: 17px;

  padding-left: 2rem;
}

th {
  background-color: #164110;
  color: #ffffff;
  padding-left: 3rem;
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

/* Estilos mantidos */
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

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #6db631;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
