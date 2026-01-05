#!/bin/bash

# Script de Teste - API Contas a Pagar
# Este script testa a API externa de criação de contas a pagar

# Configurações
API_URL="https://admin.taiksu.com.br/api/contas-a-pagar"
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIwMTk4ZDk1NC03OWYxLTczOTMtODRlYy1iNGYwYTQzNmNmNTMiLCJqdGkiOiI4MjI5MzI3ZDg0OGQ5ZmI5YWU1ZGQyODFjNGNkMmMyZmQzYjdkYTI5OWYyZWY4YTY4NjdmYTY4NzE5ODAwNzYyNGE0NzlhMDgzN2EzODM5NSIsImlhdCI6MTc2NzYxNDAyNC40NzE3MTYsIm5iZiI6MTc2NzYxNDAyNC40NzE3MTgsImV4cCI6MTc5OTE1MDAyNC40NTU5MDgsInN1YiI6IjE5Iiwic2NvcGVzIjpbXX0.an803WvIjitACtjQVobuqffSkyGViuCuSJ4sRm9RGx5cMXPcl6_lazw70hgxopgrQSRwSwxu6Mxz77SouAe5sCFA8Tur6w5EhwQIUwyPA_7Pg0qTBxPlvymiEdt0v2LGOtV4XUN4E1JwhwJuFRIw3IlCpbJutHmbb0_Oaxukf_HEFjdnhPOTz6KIgtAe7YEg2i0CeVyxuT7OFJQjxaeJAdUu8arCzKLqQwu9gWqJkZSIP-3KXtt2zXIf3l5AlOCzIPwwDzLZMICLwv_I1MAxI0eJbmM1a5DYkgWWY2sAPvNo5Rf8pyMMu0cj9RXOR4JoiZen3C3KBZEPLkLBQ3GOlrN4ROOFhk5E88tLSnnJrh5FC-1BaXfYZltZ6YMF5TcE6R5LGMPlKaY1pojBVpNfP-l2GrOWx7VCpNLxJSSN9ByCVRqZUg8VrcvcbTkKqBciTG2Yi_QG9SamoXh4DCeRzvsh6HTcAhdlAMn302UtBc34XpkrZ9PpqNEYvkbfpHEe2Wt--DAxUAE7VH1S7Z-aA0UkiuvGG-eLvKftY8OtUsqD_jYqzwBDTTQ3cp1O1JSoY5qXHLRXgW48MNY96eprE1Ylom-ggE1oO4phFMk_cW8czxXBbKeoIK9bI7WAE_zWafdJfpgm_ib1O7eijJ-Ze0oufWoz_hLPysgouhtMj-g"

echo "=========================================="
echo "Teste 1: Requisição sem token (espera 401)"
echo "=========================================="
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n\n" \
  -s | jq '.'

echo ""
echo "=========================================="
echo "Teste 2: Token inválido (espera 401)"
echo "=========================================="
curl -X POST "$API_URL" \
  -H "Authorization: Bearer token_invalido_123" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n\n" \
  -s | jq '.'

echo ""
echo "=========================================="
echo "Teste 3: Dados incompletos (espera 422)"
echo "=========================================="
curl -X POST "$API_URL" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Teste Incompleto"
  }' \
  -w "\nHTTP Status: %{http_code}\n\n" \
  -s | jq '.'

echo ""
echo "=========================================="
echo "Teste 4: Data de vencimento anterior à emissão (espera 422)"
echo "=========================================="
curl -X POST "$API_URL" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Conta com data inválida",
    "valor": 100.00,
    "emitida_em": "2026-02-01",
    "vencimento": "2026-01-01",
    "dias_lembrete": 3,
    "unidade_id": 1,
    "categoria_id": 1
  }' \
  -w "\nHTTP Status: %{http_code}\n\n" \
  -s | jq '.'

echo ""
echo "=========================================="
echo "Teste 5: Requisição válida completa (espera 201)"
echo "=========================================="
curl -X POST "$API_URL" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Conta de Energia - Janeiro 2026",
    "valor": 450.75,
    "emitida_em": "2026-01-05",
    "vencimento": "2026-01-20",
    "descricao": "Conta de energia elétrica referente ao mês de janeiro",
    "dias_lembrete": 5,
    "status": "pendente",
    "unidade_id": 1,
    "categoria_id": 1
  }' \
  -w "\nHTTP Status: %{http_code}\n\n" \
  -s | jq '.'

echo ""
echo "=========================================="
echo "Teste 6: Requisição válida mínima (espera 201)"
echo "=========================================="
curl -X POST "$API_URL" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Fornecedor XYZ",
    "valor": 1250.00,
    "emitida_em": "2026-01-05",
    "vencimento": "2026-02-05",
    "dias_lembrete": 3,
    "unidade_id": 1,
    "categoria_id": 1
  }' \
  -w "\nHTTP Status: %{http_code}\n\n" \
  -s | jq '.'

echo ""
echo "Testes concluídos!"
