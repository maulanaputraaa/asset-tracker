import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import type { AssetCategory, Site } from '@/types';

export function useCategories() {
    return useQuery({
        queryKey: ['categories'],
        queryFn: async () => (await api.get<AssetCategory[]>('/categories')).data,
        refetchOnWindowFocus: false,
    });
}

export function useSites() {
    return useQuery({
        queryKey: ['sites'],
        queryFn: async () => (await api.get<Site[]>('/sites')).data,
        refetchOnWindowFocus: false,
    });
}

// Approval berstatus Pending (response = array, bukan paginasi)
export function usePendingApprovals() {
    return useQuery({
        queryKey: ['approvals', 'pending'],
        queryFn: async () => (await api.get<unknown[]>('/approvals/pending')).data,
        refetchOnWindowFocus: false,
    });
}
