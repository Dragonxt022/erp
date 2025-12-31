# Correção de Bug - Folha de Pagamento (Cache)

## 📅 Data: 31/12/2025

## 📋 Problema Identificado

Após a implementação da otimização de cache no `AnalyticService`, algumas unidades pararam de exibir os valores da folha de pagamento no DRE.

**Causa Raiz:**
A lógica original de cache utilizava `Cache::remember` para salvar o resultado da API de salários por 24 horas. 
Se a chamada à API falhasse (devido a token expirado, erro de rede ou ausência de dados momentânea na origem), o sistema retornava o valor `0` e salvava esse valor no cache por um dia inteiro. Mesmo após o usuário logar novamente e renovar o token, o sistema continuava lendo o `0` do cache.

## ✅ Solução Implementada

Foi refatorado o método `fetchSalaries` no arquivo `app/Services/AnalyticService.php` com as seguintes melhorias:

1.  **Cache Condicional:** O sistema agora só armazena o valor no cache se ele for maior que `0` e se a requisição à API for bem-sucedida (`200 OK`).
2.  **Validação de Token:** Se o `rh_token` não estiver presente na sessão ou no usuário, o sistema retorna `0` imediatamente sem gravar nada no cache, permitindo que os dados sejam buscados assim que o token estiver disponível.
3.  **Tratamento de Erros:** Erros de API ou exceções agora resultam em retorno de `0` (para não quebrar o cálculo do DRE), mas sem persistir esse erro no cache por 24 horas.

## 🔧 Ações de Limpeza Realizadas

Foi executada uma rotina manual para limpar o cache das unidades que estavam travadas com valor zerado:
- Unidades afetadas: Escritório (7), Porto Velho (12), Jaru (14), Ouro Preto (16), Ariquemes (23) e Testes (27).

## 📄 Arquivos Modificados
- [AnalyticService.php](file:///home/taiksu-admin/htdocs/admin.taiksu.com.br/app/Services/AnalyticService.php)
