#!/bin/bash

set -u

EVENT_ID="${1:-101}"
UNIDADE_ID="${2:-8}"
USER_ID="${3:-broker-test}"
PRIORITY="${4:-medium}"

echo "=========================================="
echo "Teste de publicação no Broker"
echo "=========================================="
echo "Event ID: ${EVENT_ID}"
echo "Unidade ID: ${UNIDADE_ID}"
echo "User ID: ${USER_ID}"
echo "Priority: ${PRIORITY}"
echo ""

php artisan broker:test "${EVENT_ID}" "${UNIDADE_ID}" --userId="${USER_ID}" --priority="${PRIORITY}"
STATUS=$?

echo ""
echo "=========================================="
echo "Saída do comando: ${STATUS}"
echo "Log detalhado: storage/logs/broker_test.log"
echo "Log Laravel: storage/logs/laravel.log"
echo "=========================================="

exit ${STATUS}
