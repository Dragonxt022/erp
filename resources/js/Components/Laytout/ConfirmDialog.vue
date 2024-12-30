<template>
  <div v-if="isVisible" class="confirm-dialog-overlay">
    <div class="confirm-dialog">
      <!-- Ícone de alerta centralizado acima da mensagem -->
      <div class="alert-icon">
        <img src="/storage/images/warning.svg" alt="Ícone de alerta" />
      </div>

      <div class="confirm-dialog-content">
        <p>Você tem certeza que deseja {{ motivo }}</p>
        <div class="confirm-dialog-actions">
          <!-- Botão Sim -->
          <!-- Botão Não -->
          <button @click="cancel" class="btn-cancel">
            <span>CANCELAR</span>
          </button>
          <button @click="confirm" class="btn-confirm">
            <span>CONFIRMAR</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    isVisible: {
      type: Boolean,
      default: false,
    },
    motivo: {
      type: String,
      required: true,
    },
  },
  methods: {
    confirm() {
      this.$emit('confirm');
    },
    cancel() {
      this.$emit('cancel');
    },
  },
};
</script>

<style scoped>
.confirm-dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.confirm-dialog {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 450px;
  height: auto;
  text-align: center;
}

.confirm-dialog-content p {
  margin-top: 10px;
  margin-bottom: 20px;
  font-size: 16px;
  font-weight: 500;
}

.confirm-dialog-actions {
  display: flex;
  justify-content: space-around;
}

button {
  transition: all 0.2s ease;
  border: none;
  padding: 10px 20px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 10px;
  cursor: pointer;
}

button:hover {
  opacity: 0.8;
}

/* Botão "Sim" com estilo */
.btn-confirm {
  background-color: #6db631;
  color: #fff;
}

.btn-confirm:hover {
  background-color: #5a9f28;
}

/* Botão "Não" com estilo */
.btn-cancel {
  background-color: #f5f5f5;
  color: #ff0000;
}

.btn-cancel:hover {
  background-color: #f0f0f0;
}

/* Estilo para o ícone de alerta */
.alert-icon {
  margin-bottom: 15px;
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
