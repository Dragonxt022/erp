#!/bin/bash

# Diretório do projeto
BASE_DIR="/home/bruno/htdocs/admin.taiksu.com.br"

# Verifica se o diretório existe
cd $BASE_DIR || { echo "❌ Diretório não encontrado: $BASE_DIR"; exit 1; }

# Atualiza os arquivos do repositório
echo "🔄 Atualizando repositório..."
git reset --hard || { echo "❌ Falha ao resetar repositório"; exit 1; }
git pull origin main || { echo "❌ Erro ao fazer git pull"; exit 1; }

# Executa comandos do Laravel
echo "📦 Rodando migrations..."
/usr/bin/php $BASE_DIR/artisan migrate --force || { echo "❌ Falha ao rodar migrations"; exit 1; }

echo "🚀 Otimizando o Laravel..."
/usr/bin/php $BASE_DIR/artisan optimize || { echo "❌ Falha ao otimizar o Laravel"; exit 1; }

# (Opcional) Registro no log
echo "[$(date)] Deploy executado com sucesso" >> /var/log/deploy.log

echo "✅ Deploy concluído com sucesso."
