<template>
  <div>
    <!-- Sidebar -->
    <div
      class="sidebar fixed top-16 left-0 bg-[#164110] text-white flex flex-col p-3 overflow-y-auto h-full sm:w-64 md:w-72 lg:w-80 xl:w-80"
      ref="sidebar"
      @scroll="saveScrollPosition"
    >
      <div
        v-for="category in menuCategories"
        :key="category.name"
        class="menu-category mb-6"
      >
        <!-- Categoria de menu (Título escondido em telas pequenas) -->
        <div
          class="category-title menu-categorias text-sm sm:text-base font-medium text-[#87ba73] mb-3 pl-4"
        >
          {{ category.name }}
        </div>

        <!-- Itens do menu (Mostrar apenas os ícones em telas pequenas) -->
        <MenuItem
          v-for="item in category.items"
          :key="item.link || 'no-link'"
          :label="item.label"
          :icon="item.icon"
          :link="item.link ? route(item.link) : null"
          :submenuItems="item.submenuItems"
          :isActive="item.link ? isActive(route(item.link)) : false"
          :isLogout="item.isLogout"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MenuItem from './MenuItem.vue';

// Referência da sidebar
const sidebar = ref(null);

// Definindo as categorias de menu
const menuCategories = [
  // Inicio
  {
    name: '',
    items: [
      {
        label: 'Inicio',
        icon: '/storage/images/inicio.svg',
        link: 'franqueado.painel',
        isLogout: false,
        isActive: false,
      },
    ],
  },
  // Ferramentas
  // {
  //   name: 'Ferramentas',
  //   items: [
  //     {
  //       label: 'E-mail',
  //       icon: '/storage/images/email.svg',
  //       link: 'franqueado.email',
  //       isLogout: false,
  //       isActive: false,
  //     },
  //     {
  //       label: 'Comunidade',
  //       icon: '/storage/images/diversity_4.svg',
  //       link: 'franqueado.comunidade',
  //       isLogout: false,
  //       isActive: false,
  //     },
  //     {
  //       label: 'Mídias',
  //       icon: '/storage/images/perm_media.svg',
  //       link: 'franqueado.midias',
  //       isLogout: false,
  //       isActive: false,
  //     },
  //   ],
  // },

  // Gestão da loja
  {
    name: 'Gestão da loja',
    items: [
      {
        label: 'Controle de estoque',
        icon: '/storage/images/estoque.svg',
        link: 'franqueado.estoque',
        isLogout: false,
        submenuItems: [
          {
            label: 'Inventário',
            icon: '/storage/images/add_product.svg',
            link: 'franqueado.inventario',
          },
          {
            label: 'Saida de estoque',
            link: 'login.pagina.estoque',
          },
          {
            label: 'Fornecedores',
            link: 'franqueado.fornecedores',
          },

          {
            label: 'Novo Pedidos',
            link: 'franqueado.pedidos',
          },
          {
            label: 'Histórico de Pedidos',
            link: 'franqueado.historicoPedidos',
          },
        ],
        isActive: false,
      },

      // {
      //   label: 'Supervisão de resíduos',
      //   icon: '/storage/images/delete_branco.svg',
      //   link: 'franqueado.supervisaoResidos',
      //   isLogout: false,
      //   isActive: false,
      // },

      {
        label: 'Gestão de equipe',
        icon: '/storage/images/gestao_servisos.svg',
        link: 'franqueado.gestaoEquipe',
        isLogout: false,
        submenuItems: [
          {
            label: 'Controle de ponto',
            icon: '/storage/images/add_product.svg',
            link: 'franqueado.controlePonto',
          },
          {
            label: 'Folha de pagamento',
            link: 'franqueado.folhaPagamento',
          },
        ],
        isActive: false,
      },
      {
        label: 'DRE Gerencial',
        icon: '/storage/images/analitic.svg',
        link: 'franqueado.dreGerencial',
        isLogout: false,
        isActive: false,
      },

      // {
      //   label: 'Taiksu IA',
      //   icon: '/storage/images/TAIKSU_IA_ICONE.svg',
      //   link: 'franqueado.midias',
      //   isLogout: false,
      //   isActive: false,
      // },
    ],
  },

  // Financeiro
  {
    name: 'Financeiro',
    items: [
      {
        label: 'Fluxo de caixa',
        icon: '/storage/images/fluxo_caixa.svg',
        link: 'franqueado.abrirCaixa',
        isLogout: false,
        submenuItems: [
          {
            label: 'Métodos de pagamento',
            link: 'franqueado.metodosPagamentos',
          },
          {
            label: 'Canais de Vendas',
            link: 'franqueado.canaisVendas',
          },
          {
            label: 'Histórico de Caixa',
            link: 'franqueado.historicoCaixa',
          },
        ],
        isActive: false,
      },
      {
        label: 'Contas a pagar',
        icon: '/storage/images/attach_money.svg',
        link: 'franqueado.contasApagar',
        isLogout: false,
        submenuItems: [
          {
            label: 'Histórico de Despesas',
            link: 'franqueado.historicoContas',
          },
        ],
        isActive: false,
      },
    ],
  },

  // Rota de saida da aplicação
  {
    name: '',
    items: [
      {
        label: 'Sair',
        icon: '/storage/images/log-out.png',
        link: 'logout',
        isLogout: true,
      },
    ],
  },
];

// Recuperar a posição da rolagem do localStorage
onMounted(() => {
  const savedScrollPosition = localStorage.getItem('sidebarScrollPosition');
  if (savedScrollPosition && sidebar.value) {
    sidebar.value.scrollTop = savedScrollPosition;
  }
});

// Salvar a posição da rolagem no localStorage sempre que a sidebar for rolada
const saveScrollPosition = () => {
  if (sidebar.value) {
    localStorage.setItem('sidebarScrollPosition', sidebar.value.scrollTop);
  }
};

// Função para verificar se o link está ativo
const isActive = (link) => {
  const currentPath = window.location.pathname;
  const resolvedPath = new URL(link, window.location.origin).pathname;
  return currentPath === resolvedPath || currentPath.startsWith(resolvedPath);
};
</script>

<style scoped>
.sidebar {
  height: calc(100% - 60px); /* Ajuste para ocupar toda a altura restante */
  top: 70px;
  width: 250px; /* Largura padrão para telas grandes */
  padding-top: 27px;
  padding-bottom: 27px;
  background-color: #164110;
  display: flex;
  flex-direction: column;
  color: white;
  overflow-y: scroll;
  scrollbar-width: thin;
  scrollbar-color: transparent transparent;
}

/* Personalizando a barra de rolagem */
.sidebar::-webkit-scrollbar {
  width: 8px;
}

.sidebar::-webkit-scrollbar-thumb {
  background-color: transparent;
}

.sidebar::-webkit-scrollbar-track {
  background: transparent;
}

/* Ajuste para tornar a sidebar responsiva */
@media (max-width: 640px) {
  .sidebar {
    height: calc(100% - 11%); /* Ajuste para ocupar toda a altura restante */
    width: 15%;
    padding-top: none;
    padding-bottom: none;
    background-color: #164110;
  }

  /* Ocultar as categorias e títulos em telas pequenas */
  .menu-categorias {
    display: none;
  }

  /* Ocultar os itens de menu em telas pequenas e exibir somente os ícones */
  .menu-category {
    display: block;
  }

  .menu-item {
    justify-content: center; /* Centralizar os ícones */
  }

  .menu-item .label {
    display: none; /* Esconder o texto da label */
  }

  .menu-item .icon {
    width: 30px;
    height: 30px;
  }
}
</style>
