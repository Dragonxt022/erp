require('../config/env');

const Config = require('../config/Config');
const { EventosProcessados } = require('../models');
const logger = require('../utils/logger');
const brokerBaseUrl = Config.integrations.eventBroker.url.replace(/\/api\/event\/?$/, '');

async function marcaProcessando(deliveryId) {
  try {
    await fetch(`${brokerBaseUrl}/api/delivery/processando`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'service-token': Config.integrations.eventBroker.serviceToken
      },
      body: JSON.stringify({
        delivery_id: deliveryId
      })
    });

    logger.info('Evento marcado como processando');
  } catch (error) {
    logger.error(error);
    logger.error('Erro ao marcar evento como processando');
  }
}

module.exports = marcaProcessando;
