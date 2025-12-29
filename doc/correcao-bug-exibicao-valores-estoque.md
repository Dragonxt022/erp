# Correção do Bug de Exibição de Valores no Histórico de Estoque

**Data:** 2025-12-29  
**Desenvolvedor:** Sistema Antigravity  
**Arquivo Modificado:** `app/Http/Controllers/UnidadeEstoqueController.php`  
**Função Alterada:** `painelInicialEstoque`

---

## 📋 Sumário Executivo

Esta documentação descreve a correção implementada para resolver o problema de **dupla multiplicação** na exibição de valores do histórico de movimentações de estoque, especificamente para operações de **Retirada**.

### Problema Original
- Valores de **preço unitário** e **valor total** exibidos incorretamente para retiradas
- Causa: Função `consumirEstoque` salva valor total no campo `preco_insumo`, mas a exibição assumia que era preço unitário
- Resultado: Dupla multiplicação nos valores exibidos

### Solução Implementada
- Ajuste na lógica de exibição em `painelInicialEstoque`
- Detecta operação "Retirada" e calcula preço unitário corretamente
- Mantém compatibilidade com 2 anos de dados históricos
- Sem alteração de dados no banco

---

## 🔍 Análise do Problema

### Como o Bug Ocorria

**Exemplo prático:**
- Retirada de 5 kg de arroz a R$ 10,00/kg
- Valor total correto: R$ 50,00

**Fluxo com bug:**

1. **Função `consumirEstoque` (linha 87-95):**
```php
$valorConsumido = $estoque->preco_insumo * $quantidadeConsumir;
// $valorConsumido = 10 * 5 = 50

MovimentacoesEstoque::create([
    'quantidade' => 5,
    'preco_insumo' => 50,  // ❌ Salvou valor total ao invés de unitário
    'operacao' => 'Retirada',
]);
```

2. **Função `painelInicialEstoque` (ANTES da correção):**
```php
return [
    'preco_unitario' => 50,           // ❌ Exibe 50 (deveria ser 10)
    'valor_total' => 5 * 50 = 250,    // ❌ Exibe 250 (deveria ser 50)
];
```

**Resultado:** Valores incorretos exibidos ao usuário!

---

## 🛠️ Solução Implementada

### Mudança no Código

**Arquivo:** `app/Http/Controllers/UnidadeEstoqueController.php`  
**Função:** `painelInicialEstoque`  
**Linhas modificadas:** 473-512

### Código Anterior (com bug)

```php
$historicoMovimentacoes = MovimentacoesEstoque::with(['insumo', 'usuario'])
    ->where('unidade_id', $unidadeId)
    ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
    ->orderBy('id', 'desc')
    ->get()
    ->map(function ($estoque) {
        $quantidade = match ($estoque->operacao) {
            'Entrada' => $estoque->quantidade,
            'Retirada' => -$estoque->quantidade,
            default => $estoque->quantidade,
        };

        if ($quantidade == 0) {
            return null;
        }

        return [
            'operacao' => $estoque->operacao,
            'unidade' => $estoque->unidade,
            'quantidade' => $quantidade,
            'preco_unitario' => $estoque->preco_insumo,  // ❌ Assume sempre unitário
            'valor_total' => abs($quantidade) * $estoque->preco_insumo,  // ❌ Multiplica novamente
            'item' => $estoque->insumo->nome ?? 'N/A',
            'data' => $estoque->created_at->format('d/m/Y - H:i:s'),
            'responsavel' => $estoque->usuario->name ?? 'Desconhecido',
        ];
    })->filter();
```

### Código Corrigido

```php
$historicoMovimentacoes = MovimentacoesEstoque::with(['insumo', 'usuario'])
    ->where('unidade_id', $unidadeId)
    ->whereBetween('created_at', [$startDateConverted, $endDateConverted])
    ->orderBy('id', 'desc')
    ->get()
    ->map(function ($estoque) {
        $quantidade = match ($estoque->operacao) {
            'Entrada' => $estoque->quantidade,
            'Retirada' => -$estoque->quantidade,
            default => $estoque->quantidade,
        };

        if ($quantidade == 0) {
            return null;
        }

        // ✅ CORREÇÃO: Detectar como preco_insumo está armazenado
        // IMPORTANTE: Como preco_insumo é armazenado de forma diferente:
        // - "Retirada": preco_insumo = valor total (bug antigo da função consumirEstoque)
        // - "Entrada", "Ajuste - *": preco_insumo = preço unitário (correto)
        if ($estoque->operacao === 'Retirada') {
            // Para Retirada: preco_insumo já é o valor total
            $valorTotal = $estoque->preco_insumo;
            $precoUnitario = abs($quantidade) > 0 
                ? $estoque->preco_insumo / abs($quantidade) 
                : 0;
        } else {
            // Para Entrada e Ajustes: preco_insumo é o preço unitário
            $precoUnitario = $estoque->preco_insumo;
            $valorTotal = abs($quantidade) * $estoque->preco_insumo;
        }

        return [
            'operacao' => $estoque->operacao,
            'unidade' => $estoque->unidade,
            'quantidade' => $quantidade,
            'preco_unitario' => $precoUnitario,  // ✅ Correto
            'valor_total' => $valorTotal,         // ✅ Correto
            'item' => $estoque->insumo->nome ?? 'N/A',
            'data' => $estoque->created_at->format('d/m/Y - H:i:s'),
            'responsavel' => $estoque->usuario->name ?? 'Desconhecido',
        ];
    })->filter();
```

### Diferenças Principais

| Aspecto | Antes | Depois |
|---------|-------|--------|
| **Lógica** | Assume sempre preço unitário | Detecta tipo de operação |
| **Retirada - Unitário** | `$estoque->preco_insumo` ❌ | `$estoque->preco_insumo / quantidade` ✅ |
| **Retirada - Total** | `quantidade * preco_insumo` ❌ | `$estoque->preco_insumo` ✅ |
| **Entrada/Ajuste** | Funcionava ✅ | Continua funcionando ✅ |

---

## 📊 Análise Completa por Tipo de Operação

### 1. Operação: **Entrada**

**Função responsável:** `armazenarEntrada` (linhas 566-640)

**Como armazena:**
```php
$precoPorUnidade = ($unidadeMedida === 'kg' && $quantidade > 0)
    ? floatval($item['valorUnitario']) / $quantidade
    : floatval($item['valorUnitario']);

MovimentacoesEstoque::create([
    'preco_insumo' => $precoPorUnidade,  // ✅ Preço unitário
    'operacao' => 'Entrada',
]);
```

**Status:** ✅ Sempre funcionou corretamente

---

### 2. Operação: **Retirada**

**Função responsável:** `consumirEstoque` (linhas 37-142)

**Como armazena:**
```php
$valorConsumido = $estoque->preco_insumo * $quantidadeConsumir;

MovimentacoesEstoque::create([
    'preco_insumo' => $valorConsumido,  // ❌ Valor total (BUG)
    'operacao' => 'Retirada',
]);
```

**Status:** ❌ Bug no armazenamento (não corrigido por decisão do usuário)  
**Solução:** Exibição ajustada para calcular corretamente

---

### 3. Operações: **Ajuste - Adição / Redução / Exclusão**

**Função responsável:** `update` (linhas 654-798)

**Como armazena:**
```php
MovimentacoesEstoque::create([
    'preco_insumo' => $lote->preco_insumo,  // ✅ Preço unitário
    'operacao' => $tipoOperacao,
]);
```

**Status:** ✅ Sempre funcionou corretamente

---

## 💡 Exemplo Prático da Correção

### Cenário: Retirada de 5 kg de arroz a R$ 10,00/kg

**Banco de dados (não alterado):**
```
movimentacoes_estoques:
  id: 12345
  quantidade: 5
  preco_insumo: 50.00  (valor total)
  operacao: 'Retirada'
```

**Exibição ANTES da correção:**
```json
{
  "preco_unitario": 50.00,   // ❌ Errado
  "valor_total": 250.00      // ❌ Errado (5 * 50)
}
```

**Exibição DEPOIS da correção:**
```json
{
  "preco_unitario": 10.00,   // ✅ Correto (50 / 5)
  "valor_total": 50.00       // ✅ Correto
}
```

---

## 🎯 Decisões de Design

### Por que não corrigir a função `consumirEstoque`?

**Opções consideradas:**

1. **Opção A: Corrigir código + migrar dados históricos**
   - ✅ Solução definitiva
   - ❌ Risco de corromper 2 anos de dados
   - ❌ Requer SQL em produção
   - ❌ Difícil de reverter

2. **Opção B: Ajustar apenas a exibição** ← **ESCOLHIDA**
   - ✅ Sem risco aos dados históricos
   - ✅ Sem SQL em produção
   - ✅ Compatível com dados antigos e novos
   - ✅ Fácil de reverter se necessário
   - ❌ Mantém inconsistência no armazenamento

**Decisão:** Opção B escolhida pelo usuário por ser mais segura.

---

## 🔐 Segurança e Compatibilidade

### Dados Históricos
✅ **Preservados:** Nenhum dado foi alterado no banco de dados  
✅ **Compatibilidade:** Funciona com registros de 2 anos atrás  
✅ **Reversível:** Mudança pode ser revertida facilmente  

### Sistemas Dependentes
✅ **Sem impacto:** Outros sistemas que leem diretamente do banco não são afetados  
✅ **CMV/DRE:** Continuam funcionando normalmente  
✅ **Relatórios:** Não foram impactados  

### Novas Movimentações
⚠️ **Nota:** Novas retiradas continuarão salvando valor total em `preco_insumo` (comportamento da função `consumirEstoque` não foi alterado), mas agora serão exibidas corretamente.

---

## 🧪 Testes Recomendados

### 1. Testar Retiradas Antigas
```
1. Acessar painel de estoque
2. Filtrar por período com retiradas antigas
3. Verificar se preco_unitario e valor_total estão corretos
4. Comparar com valores esperados
```

### 2. Testar Entradas
```
1. Verificar entradas antigas
2. Confirmar que valores continuam corretos
3. Criar nova entrada de teste
4. Verificar exibição
```

### 3. Testar Ajustes
```
1. Verificar ajustes antigos (Adição/Redução/Exclusão)
2. Confirmar valores corretos
3. Fazer novo ajuste de teste
4. Verificar exibição
```

### 4. Testar Nova Retirada
```
1. Fazer nova retirada de estoque
2. Verificar se é exibida corretamente
3. Confirmar que bug não reaparece
```

---

## 📈 Impacto e Benefícios

### Antes da Correção
- ❌ Valores de retirada exibidos incorretamente
- ❌ Confusão para usuários
- ❌ Possíveis erros de gestão baseados em valores errados
- ❌ Perda de confiança nos dados do sistema

### Depois da Correção
- ✅ Todos os valores exibidos corretamente
- ✅ Dados históricos preservados
- ✅ Compatibilidade total
- ✅ Sem risco de perda de dados
- ✅ Solução simples e elegante

---

## 🔮 Melhorias Futuras (Opcional)

Se no futuro for necessário padronizar completamente o armazenamento de `preco_insumo`:

### 1. Corrigir a função `consumirEstoque`

```php
// REMOVER estas linhas (86-87):
// $valorConsumido = $estoque->preco_insumo * $quantidadeConsumir;

// MUDAR linha 95 de:
'preco_insumo' => $valorConsumido,

// PARA:
'preco_insumo' => $estoque->preco_insumo,
```

### 2. Migrar dados históricos (SQL)

```sql
-- Backup primeiro
CREATE TABLE movimentacoes_estoques_backup_20251229 AS 
SELECT * FROM movimentacoes_estoques WHERE operacao = 'Retirada';

-- Corrigir dados
UPDATE movimentacoes_estoques
SET preco_insumo = preco_insumo / quantidade
WHERE operacao = 'Retirada' 
  AND quantidade > 0
  AND quantidade != 1;
```

### 3. Simplificar exibição

```php
// Remover o if/else, usar sempre:
$precoUnitario = $estoque->preco_insumo;
$valorTotal = abs($quantidade) * $estoque->preco_insumo;
```

---

## 📝 Resumo Técnico

| Item | Detalhes |
|------|----------|
| **Arquivo modificado** | `app/Http/Controllers/UnidadeEstoqueController.php` |
| **Função alterada** | `painelInicialEstoque` |
| **Linhas modificadas** | 473-512 |
| **Tipo de mudança** | Lógica de exibição |
| **Dados alterados** | Nenhum |
| **Risco** | Baixo |
| **Reversibilidade** | Alta |
| **Compatibilidade** | Total (2 anos de histórico) |

---

## ✅ Conclusão

A correção implementada resolve completamente o problema de exibição de valores no histórico de movimentações de estoque, mantendo:

- ✅ **Segurança:** Sem alteração de dados
- ✅ **Eficácia:** Valores exibidos corretamente
- ✅ **Simplicidade:** Mudança localizada e clara
- ✅ **Compatibilidade:** Funciona com dados antigos e novos
- ✅ **Manutenibilidade:** Código bem documentado

A solução escolhida foi a mais adequada considerando o contexto de 2 anos de dados históricos e a necessidade de uma implementação segura e sem riscos.

---

**Documentação gerada em:** 2025-12-29  
**Versão:** 1.0  
**Autor:** Sistema Antigravity
