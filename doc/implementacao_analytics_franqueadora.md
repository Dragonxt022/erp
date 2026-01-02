# Implementação de Analytics e DRE Franqueadora

Este documento descreve as alterações realizadas para criar a nova seção de Analytics e o DRE específico para o painel da Franqueadora.

## 1. Novas Funcionalidades

### Seção de Analytics
- Foi criada uma nova categoria "Analytics" no menu lateral do painel da Franqueadora.
- Itens disponíveis: **DRE Gerencial** e **Faturamento**.

### DRE Gerencial (Anteriormente apenas "DRE")
- **Página Dedicada**: Localizada em `resources/js/Pages/Admin/Analytics/Dre.vue`.
- **Seletor de Unidades**: A franqueadora pode alternar entre todas as unidades da rede.
- **Carregamento Inteligente**: Unidade do usuário logado e mês anterior por padrão.
- **Visualização**: Tabelas financeiras, gráficos de faturamento por categoria e chatbot de feedback.

### Faturamento Analítico
- **Página Dedicada**: Localizada em `resources/js/Pages/Admin/Analytics/Faturamento.vue`.
- **Análise de 12 Meses**: Exibe um gráfico de barras comparativo dos últimos 12 meses.
- **Indicadores de Variação**: O gráfico mostra o faturamento mensal e a diferença nominal em relação ao mês anterior (barra de variação).
- **Detalhamento**: Tabela com valores exatos de faturamento, variação nominal (R$) e variação percentual (%) mês a mês.

## 2. Alterações Técnicas

### Backend (PHP/Laravel)
- **PainelAnaliticos.php**:
    - `analitycsDRE`: Modificada para aceitar `unidade_id` e data padrão do mês anterior.
    - `faturamentoAnalitico`: NOVO método que calcula o faturamento dos últimos 12 meses, incluindo as diferenças entre períodos.
- **Routes**: Adicionadas as rotas `/franqueadora/analytics/dre` e `/franqueadora/analytics/faturamento`.

### Frontend (Vue.js)
- **Slidebar.vue**: Renomeado "DRE" para "DRE Gerencial" e adicionado o link para "Faturamento".
- **Faturamento.vue**: Nova interface com gráfico de barras (Chart.js) e tabela de histórico anual.

### Otimização e Cache
- O sistema utiliza o mecanismo de cache versionado implementado anteriormente, garantindo que os dados do DRE estejam sempre atualizados com base nos lançamentos de caixa e estoque, sem perda de performance.

---
**Data**: 02 de Janeiro de 2026
