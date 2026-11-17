import { defineStore } from 'pinia';
import http from '../http';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token'),
        usuario: JSON.parse(localStorage.getItem('usuario') || 'null'),
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
    },

    actions: {
        setSession({ token, usuario }) {
            this.token = token;
            this.usuario = usuario;
            localStorage.setItem('token', token);
            localStorage.setItem('usuario', JSON.stringify(usuario));
        },

        async login(credentials) {
            const { data } = await http.post('/login', credentials);
            this.setSession(data);
        },

        async register(payload) {
            const { data } = await http.post('/register', payload);
            this.setSession(data);
        },

        async logout() {
            try {
                await http.post('/logout');
            } finally {
                this.token = null;
                this.usuario = null;
                localStorage.removeItem('token');
                localStorage.removeItem('usuario');
            }
        },
    },
});
