'use strict';
const {
  Model
} = require('sequelize');

module.exports = (sequelize, DataTypes) => {
  class EventosProcessados extends Model {
    // helper method for defining associations
    static associate(models) {
      // define association here
    }
  }

  EventosProcessados.init({
    delivery_id: {
      type: DataTypes.UUID,
      primaryKey: true,
      allowNull: false
    },
    event_type: {
      type: DataTypes.STRING,
      allowNull: false
    },
    status: {
      type: DataTypes.BOOLEAN,
      allowNull: false,
      defaultValue: false,
      comment: 'true = processado, false = não processado'
    },
    processando_em: {
      type: DataTypes.DATE,
      allowNull: true
    },
    processado_em: {
      type: DataTypes.DATE,
      allowNull: true
    }
  }, {
    sequelize,
    modelName: 'EventosProcessados',
    tableName: 'eventos_processados',
  });

  return EventosProcessados;
};
