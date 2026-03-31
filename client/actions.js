
// As actions seguem o padrão: evento recebido -> Controller responsável
const { emailController } = require('../controllers');

async function actions(req, res) {
    const eventType = req.headers['event-type'];

    // Enviar email para o cliente quando um pedido for criado
    if (eventType == 15) {
        emailController.receivePedidoCriadoEvent(req, res);
    }

}

module.exports = actions;
