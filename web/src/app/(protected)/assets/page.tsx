'use client';

import { useState, useEffect } from 'react';
import { useAssets } from '@/hooks/useAssets';
import { useCategories, useSites } from '@/hooks/useMasterData';
import { AssetTable } from '@/components/assets/AssetTable';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    ASSET_STATUSES,
    type AssetFilters,
} from '@/types';

// Styling select native biar konsisten dgn Input
const selectClass =
    'h-8 rounded-lg border border-input bg-transparent px-2.5 text-sm text-foreground outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50';

export default function AssetsPage() {
    const [filters, setFilters] = useState<AssetFilters>({ per_page: 15, page: 1 });
    const [searchInput, setSearchInput] = useState('');

    const { data, isLoading, isFetching } = useAssets(filters);
    const categories = useCategories();
    const sites = useSites();

    // Debounce search → commit ke filters & reset ke hal. 1
    useEffect(() => {
        const t = setTimeout(() => {
            setFilters((f) => ({ ...f, search: searchInput || undefined, page: 1 }));
        }, 400);
        return () => clearTimeout(t);
    }, [searchInput]);

    const set = (patch: Partial<AssetFilters>) =>
        setFilters((f) => ({ ...f, ...patch, page: 1 }));

    const total = data?.total ?? 0;
    const currentPage = data?.current_page ?? 1;
    const lastPage = data?.last_page ?? 1;

    return (
        <div className="flex flex-col gap-6">
            {/* Judul + jumlah */}
            <div>
                <h1 className="text-2xl font-semibold">Daftar Aset</h1>
                <p className="text-sm text-muted-foreground">{total} aset terdaftar</p>
            </div>

            {/* Filter bar */}
            <div className="grid grid-cols-1 gap-3 rounded-lg border bg-card p-4 md:grid-cols-4">
                <Input
                    placeholder="Cari kode, nama, seri…"
                    value={searchInput}
                    onChange={(e) => setSearchInput(e.target.value)}
                    aria-label="Pencarian aset"
                />
                <select
                    className={selectClass}
                    value={filters.status ?? ''}
                    onChange={(e) => set({ status: (e.target.value || undefined) as AssetFilters['status'] })}
                    aria-label="Filter status"
                >
                    <option value="">Semua status</option>
                    {ASSET_STATUSES.map((s) => (
                        <option key={s} value={s}>{s}</option>
                    ))}
                </select>
                <select
                    className={selectClass}
                    value={filters.category_id ?? ''}
                    onChange={(e) => set({ category_id: e.target.value ? Number(e.target.value) : undefined })}
                    aria-label="Filter kategori"
                >
                    <option value="">Semua kategori</option>
                    {(categories.data ?? []).map((c) => (
                        <option key={c.id} value={c.id}>{c.name}</option>
                    ))}
                </select>
                <select
                    className={selectClass}
                    value={filters.site_id ?? ''}
                    onChange={(e) => set({ site_id: e.target.value ? Number(e.target.value) : undefined })}
                    aria-label="Filter site"
                >
                    <option value="">Semua site</option>
                    {(sites.data ?? []).map((s) => (
                        <option key={s.id} value={s.id}>{s.name}</option>
                    ))}
                </select>
            </div>

            {/* Tabel */}
            <div className="rounded-lg border bg-card p-4">
                <AssetTable assets={data?.data ?? []} loading={isLoading || isFetching} />
            </div>

            {/* Pagination */}
            {lastPage > 1 && (
                <div className="flex items-center justify-between text-sm">
                    <span className="text-muted-foreground">
                        Halaman {currentPage} dari {lastPage}
                    </span>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            disabled={currentPage <= 1}
                            onClick={() => set({ page: currentPage - 1 })}
                        >
                            Sebelumnya
                        </Button>
                        <Button
                            variant="outline"
                            disabled={currentPage >= lastPage}
                            onClick={() => set({ page: currentPage + 1 })}
                        >
                            Berikutnya
                        </Button>
                    </div>
                </div>
            )}
        </div>
    );
}