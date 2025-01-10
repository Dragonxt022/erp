// stores/user.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useUserStore = defineStore('user', {
  state: () => ({
    user: null, // Não usa mais o localStorage
  }),

  actions: {
    async fetchUserProfile() {
      if (this.user) return; // Se já existe, não faz a requisição novamente
      try {
        const response = await axios.get('/api/navbar-profile');
        if (response.data.status === 'success') {
          this.user = response.data.data; // Armazena os dados do usuário diretamente
        } else {
          console.error('Erro ao carregar o perfil:', response.data.message);
        }
      } catch (error) {
        console.error('Erro ao buscar perfil:', error);
      }
    },

    clearUser() {
      this.user = null; // Limpar dados do usuário
    },
  },
});
