require('../config/env');

const Config = require('../config/Config');
const { EventosProcessados } = require('../models');
const logger = require('../utils/logger');
const brokerBaseUrl = Config.integrations.eventBroker.url.replace(/\/api\/event\/?$/, '');

async function confirmaProcesso(deliveryId) {
  try {
    await fetch(`${brokerBaseUrl}/api/delivery/ok`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'service-token': Config.integrations.eventBroker.serviceToken
      },
      body: JSON.stringify({
        delivery_id: deliveryId
      })
    });

    await EventosProcessados.update({
      status: true
    }, {
      where: {
        delivery_id: deliveryId
      }
    });

    logger.info('Evento processado e confirmado no Event Broker');
  } catch (error) {
    logger.error(error);
    logger.error('Erro ao confirmar processamento');
  }
}

module.exports = confirmaProcesso;
