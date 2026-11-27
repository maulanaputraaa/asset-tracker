import { useQuery, useMutation } from '@tanstack/react-query';
import api from '@/lib/api';
import type {
    AssetFilters, AssetListResponse,
    AssetDetail, TcoResponse, QrResponse,
} from '@/types';

// Susun query string, lewati field undefined/empty
function toQuery(filters: AssetFilters): Record<string, string> {
    const q: Record<string, string> = {};
    if (filters.search) q.search = filters.search;
    if (filters.status) q.status = filters.status;
    if (filters.category_id) q.category_id = String(filters.category_id);
    if (filters.site_id) q.site_id = String(filters.site_id);
    if (filters.per_page) q.per_page = String(filters.per_page);
    if (filters.page) q.page = String(filters.page);
    return q;
}

// Hook daftar aset — query key ikut filter, jadi ganti filter otomatis refetch
export function useAssets(filters: AssetFilters = {}) {
    return useQuery({
        queryKey: ['assets', filters],
        queryFn: async () =>
            (await api.get<AssetListResponse>('/assets', { params: toQuery(filters) })).data,
        refetchOnWindowFocus: false,
    });
}

// GET /api/assets/{id} — detail + relasi
export function useAsset(id: number | null) {
    return useQuery({
        queryKey: ['asset', id],
        enabled: id != null,
        queryFn: async () =>
            (await api.get<AssetDetail>(`/assets/${id}`)).data,
        refetchOnWindowFocus: false,
    });
}

// GET /api/assets/{id}/tco — total biaya
export function useAssetTco(id: number | null) {
    return useQuery({
        queryKey: ['asset-tco', id],
        enabled: id != null,
        queryFn: async () =>
            (await api.get<TcoResponse>(`/assets/${id}/tco`)).data,
        refetchOnWindowFocus: false,
    });
}

// POST /api/assets/{id}/qr-generate — tombol generate QR
export function useGenerateQr(id: number) {
    return useMutation({
        mutationFn: async () =>
            (await api.post<QrResponse>(`/assets/${id}/qr-generate`)).data,
    });
}
