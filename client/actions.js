// As actions seguem o padrão: evento recebido -> Controller responsável
const { estoqueController } = require('../controllers');
const confirmaProcesso = require('./confirmaProcesso');

const eventHandlers = {
    '101': (req, res) => estoqueController.receiveEntradaItemEstoqueEvent(req, res),
    '102': (req, res) => estoqueController.receiveSaidaItemEstoqueEvent(req, res),
};

async function actions(req, res) {
    const eventType = String(req.headers['event-type'] || '');
    const deliveryId = String(req.headers['delivery-id'] || '');
    const handler = eventHandlers[eventType];

    if (!handler) {
        return;
    }

    await handler(req, res);

    if (deliveryId && res.statusCode < 400) {
        await confirmaProcesso(deliveryId);
    }
}

module.exports = actions;
