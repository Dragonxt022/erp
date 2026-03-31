require('../config/env');

const Config = require('../config/Config');
const { EventosProcessados } = require('../models');
const marcaProcessando = require('./marcaProcessando');
const logger = require('../utils/logger');
const brokerBaseUrl = Config.integrations.eventBroker.url.replace(/\/api\/event\/?$/, '');

async function checkEvent(req, res, next) {
  logger.info('Verificando se o evento ja foi processado');
  let event = null;
  const deliveryId = req.headers['delivery-id'];
  const eventType = req.headers['event-type'];

  if (!deliveryId || !eventType) {
    logger.warn('Header delivery-id ou event-type nao informado');
    return res.status(400).json({ status: 'error', message: 'Header delivery-id ou event-type não informado' });
  }

  event = await EventosProcessados.findOne({
    where: {
      delivery_id: deliveryId,
    }
  });

  if (!event) {
    logger.info('Evento novo recebido');
    await EventosProcessados.create({
      delivery_id: deliveryId,
      event_type: eventType,
      status: false
    });
    return next();
  }

  if (event.status == true) {
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
    logger.info('Evento ja processado, reenviando confirmacao ao broker');
    return res.status(409).json({ status: 'error', message: 'Evento já processado' });
  } else if (event.status == false) {
    logger.info('Evento em processamento');
    await marcaProcessando(deliveryId);
    return res.status(409).json({ status: 'error', message: 'Evento em processamento' });
  }

  next();
}
module.exports = checkEvent;
