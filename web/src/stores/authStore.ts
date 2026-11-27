import { create } from 'zustand';
import type { User } from '@/types';

interface AuthState {
    user: User | null;
    token: string | null;
    isAuthenticated: boolean;
    isLoading: boolean;
    setUser: (user: User | null) => void;
    setToken: (token: string | null) => void;
    login: (user: User, token: string) => void;
    logout: () => void;
    setLoading: (loading: boolean) => void;
}

export const useAuthStore = create<AuthState>((set) => ({
    user: null,
    token: null,
    isAuthenticated: false,
    isLoading: true, // true saat inisialisasi pertama (cek localStorage)

    setUser: (user) =>
        set((s) => ({
            user,
            isAuthenticated: !!user && !!s.token,
        })),

    setToken: (token) =>
        set((s) => ({
            token,
            isAuthenticated: !!(s.user && token),
        })),

    login: (user, token) => {
        localStorage.setItem('aets_token', token);
        localStorage.setItem('aets_user', JSON.stringify(user));
        set({ user, token, isAuthenticated: true, isLoading: false });
    },

    logout: () => {
        localStorage.removeItem('aets_token');
        localStorage.removeItem('aets_user');
        set({ user: null, token: null, isAuthenticated: false, isLoading: false });
    },

    setLoading: (isLoading) => set({ isLoading }),
}));

// Inisialisasi dari localStorage (next.js SSR-safe)
if (typeof window !== 'undefined') {
    const storedToken = localStorage.getItem('aets_token');
    const storedUser = localStorage.getItem('aets_user');
    if (storedToken && storedUser) {
        useAuthStore.setState({
            user: JSON.parse(storedUser),
            token: storedToken,
            isAuthenticated: true,
            isLoading: false,
        });
    } else {
        useAuthStore.setState({ isLoading: false });
    }
}

export type { AuthState };
