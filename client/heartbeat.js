// This function responds webhook with 200 OK and check database connection
const { sequelize } = require('../models');
const logger = require('../utils/logger');

async function heartbeat(req, res) {
  try {
    await sequelize.authenticate();
    console.log('[Heartbeat]: Database connection successful');
    res.status(200).json({
      success: true,
      message: 'Service is online',
      time: new Date().toISOString()
    });
  } catch (error) {
    console.log('[Heartbeat]: Database connection failed');
    res.status(500).json({
      success: false,
      message: 'Service is offline',
      error: error.message,
      time: new Date().toISOString()
    });
  }
}

module.exports = heartbeat;
