import { useToast } from 'vue-toastification';

const notify = {
  success(message, options = {}) {
    const toast = useToast(); // Obtém a instância do toast no contexto correto
    toast.success(message, options);
  },
  error(message, options = {}) {
    const toast = useToast();
    toast.error(message, options);
  },
  info(message, options = {}) {
    const toast = useToast();
    toast.info(message, options);
  },
  warning(message, options = {}) {
    const toast = useToast();
    toast.warning(message, options);
  },
};

export default {
  install(app) {
    app.config.globalProperties.$notify = notify; // Adiciona o `notify` ao Vue globalmente
  },
};
