<template>
    <div>
        <!-- Sidebar -->
        <div class="sidebar fixed top-16 left-0 bg-[#164110] text-white flex flex-col p-3 overflow-y-auto h-full sm:w-64 md:w-72 lg:w-80 xl:w-80"
            ref="sidebar" @scroll="saveScrollPosition">
            <div v-for="category in filteredMenuCategories" :key="category.name" class="menu-category mb-6">
                <!-- Categoria de menu (Título escondido em telas pequenas) -->
                <div class="category-title menu-categorias text-sm sm:text-base font-medium text-[#87ba73] mb-3 pl-4"
                    v-if="category.items.length > 0">
                    {{ category.name }}
                </div>

                <!-- Itens do menu -->
                <MenuItem v-for="item in category.items" :key="item.link || 'no-link'" :label="item.label"
                    :icon="item.icon" :link="item.link" :submenuItems="item.submenuItems"
                    :isActive="item.link ? isActive(item.link) : false" :isLogout="item.isLogout"
                    :requiredPermission="item.requiredPermission" />
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted, computed, provide } from 'vue';
import MenuItem from './MenuItem.vue';
import axios from 'axios';

const sidebar = ref(null);

const userPermissions = ref({});

// Função para buscar os dados da API
const fetchPermissions = async () => {
    try {
        const response = await axios.get('/api/navbar-profile');
        userPermissions.value = response.data.data.permissions || {};
    } catch (error) {
        console.error('Erro ao carregar permissões:', error);
        userPermissions.value = {}; // Valor padrão em caso de erro
    }
};

// Fornece userPermissions para os componentes filhos
provide('userPermissions', userPermissions);

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
                requiredPermission: null,
            },
        ],
    },
    // Ferramentas
    {
        name: 'Ferramentas',
        items: [
            {
                label: 'E-mail',
                icon: '/storage/images/email.svg',
                link: 'https://arquivos.taiksu.com.br/apps/mail/box/1',
                isLogout: false,
                isActive: false,
                requiredPermission: null,
            },
            {
                label: 'Comunidade',
                icon: '/storage/images/diversity_4.svg',
                link: 'https://arquivos.taiksu.com.br/call/wprjfww7',
                isLogout: false,
                isActive: false,
                requiredPermission: null,
            },
            {
                label: 'Treinamento',
                icon: '/storage/images/treinamento.svg',
                link: 'https://treinamento.taiksu.com.br/',
                isLogout: false,
                isActive: false,
                requiredPermission: null,
            },
            {
                label: 'Mídias',
                icon: '/storage/images/perm_media.svg',
                link: 'https://arquivos.taiksu.com.br/s/P5nypCADKccgcib',
                isLogout: false,
                isActive: false,
                requiredPermission: null,
            },
        ],
    },

    // Gestão da loja
    {
        name: 'Gestão da loja',
        items: [
            {
                label: 'Gestão de estoque',
                icon: '/storage/images/estoque.svg',
                link: 'franqueado.estoque',
                isLogout: false,
                requiredPermission: 'controle_estoque',
                submenuItems: [
                    {
                        label: 'Inventário',
                        link: 'franqueado.inventario',
                        requiredPermission: 'controle_estoque',
                    },
                    {
                        label: 'Saida de estoque',
                        link: 'franqueado.controleEstoque',
                        requiredPermission: 'controle_saida_estoque',
                    },
                    {
                        label: 'Fornecedores',
                        link: 'franqueado.fornecedores',
                        requiredPermission: 'controle_estoque',
                    },
                    {
                        label: 'Novo Pedidos',
                        link: 'franqueado.pedidos',
                        requiredPermission: 'controle_estoque',
                    },
                    {
                        label: 'Histórico de Pedidos',
                        link: 'franqueado.historicoPedidos',
                        requiredPermission: 'controle_estoque',
                    },
                ],
                isActive: false,
            },

            {
                label: 'Gestão de resíduos',
                icon: '/storage/images/delete_branco.svg',
                link: 'franqueado.supervisaoResidos',
                isLogout: false,
                requiredPermission: 'gestao_salmao',

                submenuItems: [
                    {
                        label: 'Limpeza de salmão',
                        link: 'franqueado.limpesaSalmoes',
                        requiredPermission: 'gestao_salmao',
                    },
                ],
                isActive: false,
            },

            {
                label: 'Gestão de equipe',
                icon: '/storage/images/gestao_servisos.svg',
                link: 'franqueado.gestaoEquipe',
                isLogout: false,
                requiredPermission: 'gestao_equipe',
                submenuItems: [
                    {
                        label: 'Controle de ponto',
                        icon: '/storage/images/add_product.svg',
                        link: 'franqueado.controlePonto',
                        requiredPermission: 'gestao_equipe',
                    },
                    {
                        label: 'Folha de pagamento',
                        link: 'franqueado.folhaPagamento',
                        requiredPermission: 'gestao_equipe',
                    },
                ],
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

    // Gestão de Redes
    {
        name: 'Gestão de Redes',
        items: [
            // Produtividade
            {
                label: 'Produtividade',
                icon: '/storage/images/skillet.svg',
                link: 'franqueado.produtividade.geral',
                isLogout: false,
                submenuItems: [
                    {
                        label: 'Visão Geral',
                        link: 'franqueado.produtividade.geral',
                    },
                    {
                        label: 'Agenda de Produção',
                        link: 'franqueado.AgendaProducao',
                    },

                ],
                isActive: false,
            },
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
                requiredPermission: 'fluxo_caixa',
                submenuItems: [
                    {
                        label: 'Métodos de pagamento',
                        link: 'franqueado.metodosPagamentos',
                        requiredPermission: 'fluxo_caixa',
                    },
                    {
                        label: 'Canais de Vendas',
                        link: 'franqueado.canaisVendas',
                        requiredPermission: 'fluxo_caixa',
                    },
                    {
                        label: 'Histórico de Caixa',
                        link: 'franqueado.historicoCaixa',
                        requiredPermission: 'fluxo_caixa',
                    },

                    {
                        label: 'Caixa Retroativo',
                        link: 'franqueado.caixaRetroativo',
                        requiredPermission: 'fluxo_caixa',
                    },
                ],
                isActive: false,
            },
            {
                label: 'Contas a pagar',
                icon: '/storage/images/attach_money.svg',
                link: 'franqueado.contasApagar',
                isLogout: false,
                requiredPermission: 'contas_pagar',
                submenuItems: [
                    {
                        label: 'Histórico de Despesas',
                        link: 'franqueado.historicoContas',
                        requiredPermission: 'contas_pagar',
                    },
                ],
                isActive: false,
            },
            {
                label: 'DRE Gerencial',
                icon: '/storage/images/analitic.svg',
                link: 'franqueado.dreGerencial',
                isLogout: false,
                requiredPermission: 'dre',
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

const filteredMenuCategories = computed(() => {
    return menuCategories
        .map((category) => ({
            ...category,
            items: category.items.filter(
                (item) =>
                    !item.requiredPermission ||
                    userPermissions.value[item.requiredPermission] ||
                    (item.submenuItems &&
                        item.submenuItems.some(
                            (sub) =>
                                !sub.requiredPermission ||
                                userPermissions.value[sub.requiredPermission]
                        ))
            ),
        }))
        .filter((category) => category.items.length > 0);
});

// Recuperar a posição da rolagem do localStorage
onMounted(async () => {
    await fetchPermissions();

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
    height: calc(100% - 60px);
    /* Ajuste para ocupar toda a altura restante */
    top: 70px;
    width: 249px;
    /* Largura padrão para telas grandes */
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
</style>
