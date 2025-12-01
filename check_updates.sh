#!/bin/bash

# Defina o caminho do projeto
PROJECT_DIR="/home/beta-testes/htdocs/beta.taiksu.com.br/erp"

# Vá para o diretório do projeto
cd "$PROJECT_DIR" || { echo "Diretório do projeto não encontrado!"; exit 1; }

echo "Verificando atualizações no repositório Git..."

# Buscar últimas alterações no repositório remoto
git fetch origin

# Verificar se há diferença entre o branch local e o remoto
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse @{u})

if [ "$LOCAL" = "$REMOTE" ]; then
    echo "O branch local está atualizado."
else
    echo "Há atualizações no repositório remoto disponíveis."
    echo "Para atualizar, execute: git pull"
fi

echo "Verificando atualizações das dependências PHP..."

# Verifica se composer está instalado
if command -v composer >/dev/null 2>&1; then
    # Checar atualizações sem instalá-las
    composer outdated
else
    echo "Composer não está instalado."
fi
