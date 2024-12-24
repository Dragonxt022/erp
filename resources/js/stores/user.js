// stores/user.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useUserStore = defineStore('user', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null, // Carregar do localStorage, se existir
  }),
  actions: {
    async fetchUserProfile() {
      if (this.user) return; // Se já existe, não faz a requisição novamente
      try {
        const response = await axios.get('/api/profile');
        if (response.data.status === 'success') {
          this.user = response.data.data; // Armazena os dados do usuário
          localStorage.setItem('user', JSON.stringify(this.user)); // Salva no localStorage
        } else {
          console.error('Erro ao carregar o perfil:', response.data.message);
        }
      } catch (error) {
        console.error('Erro ao buscar perfil:', error);
      }
    },
    clearUser() {
      this.user = null;
      localStorage.removeItem('user'); // Limpar o localStorage quando o usuário fizer logout
    },
  },
});
