# Análise da Lógica de Cálculo do CMV

**Data:** 2025-12-15
**Arquivo Responsável:** `app/Services/AnalyticService.php`
**Método:** `calculateStockMetrics`

## 1. Definições Fundamentais

### O que é considerado COMPRA?
O sistema considera como compra **apenas** as movimentações de estoque onde a operação é explicitamente classificada como **"Entrada"**.

- **Código:** `MovimentacoesEstoque::where('operacao', 'Entrada')`
- **Impacto:** Soma diretamente ao valor das compras no cálculo do CMV.

### O que é considerado VENDA (para o CMV)?
O cálculo do CMV **não utiliza dados financeiros de vendas** (como faturamento ou fechamento de caixa). Ele é calculado estritamente pela **variação física e monetária do inventário**.

- **Conceito:** As vendas são capturadas indiretamente. Quando uma venda ocorre e o produto é baixado do estoque, isso reduz o "Estoque Final".
- **Lógica:** Se o estoque final diminuiu em relação ao (Inicial + Compras), o sistema assume que essa diferença foi o custo da mercadoria vendida (CMV).

## 2. A Fórmula do Cálculo

A fórmula utilizada no método `calculateStockMetrics` é:

```
CMV = Estoque Inicial + Compras - Estoque Final
```

Onde:
1.  **Estoque Inicial:** Último saldo registrado na tabela `controle_saldo_estoques` **antes** do início do período selecionado.
2.  **Compras:** Soma do valor (`preco_insumo * quantidade`) de todas as movimentações com `operacao = 'Entrada'` **dentro** do período.
3.  **Estoque Final:** Último saldo registrado na tabela `controle_saldo_estoques` **até o final** do período selecionado.

## 3. Impacto dos Ajustes de Estoque

Como os ajustes não são "Compras", eles afetam o CMV alterando o **Estoque Final**.

### Ajustes de Entrada (Adição)
- **Ação:** Aumentam a quantidade de itens no estoque.
- **Efeito no Estoque Final:** Aumenta o valor do Estoque Final.
- **Efeito no CMV:** **Reduz o CMV**.
  - *Racional:* Se você tem mais estoque no final do que o previsto pelas compras, significa que você "gastou/vendeu" menos do que parecia. O sistema entende isso como uma correção de custo para baixo.

### Ajustes de Saída (Redução/Exclusão)
- **Ação:** Diminuem a quantidade de itens no estoque (ou excluem lotes zerados).
- **Efeito no Estoque Final:** Diminui o valor do Estoque Final.
- **Efeito no CMV:** **Aumenta o CMV**.
  - *Racional:* Se você tem menos estoque no final, a diferença desaparecida é contabilizada como custo (perda, consumo ou venda não registrada). Portanto, ajustes negativos inflam o CMV do período.

## 4. Conclusão para a Gestão

- **Para reduzir o CMV artificialmente:** Garantir que todas as entradas de mercadoria sejam lançadas corretamente como "Entrada". Ajustes de entrada posteriores funcionam, mas distorcem a métrica "Compras".
- **Cuidado com Ajustes de Saída:** Grandes ajustes de saída (perda, roubo, correção de contagem) entrarão no cálculo do CMV como se fossem mercadoria vendida, aumentando o custo do período.

---
*Documentação gerada a pedido para esclarecimento da regra de negócio atual.*
