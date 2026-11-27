'use client';

import { useParams } from 'next/navigation';
import Link from 'next/link';
import { useState } from 'react';
import {
    useAsset, useAssetTco, useGenerateQr,
} from '@/hooks/useAssets';
import type { AssetDetail, QrResponse } from '@/types';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

type TabKey = 'info' | 'riwayat' | 'pemeliharaan' | 'biaya' | 'dokumen';
const TABS: { key: TabKey; label: string }[] = [
    { key: 'info', label: 'Info' },
    { key: 'riwayat', label: 'Riwayat Lokasi' },
    { key: 'pemeliharaan', label: 'Pemeliharaan' },
    { key: 'biaya', label: 'Biaya' },
    { key: 'dokumen', label: 'Dokumen' },
];

const fmtIDR = new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
});

function InfoTab({ asset }: { asset: AssetDetail }) {
    return (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <Field label="Kode Aset" value={asset.asset_code} />
            <Field label="Nama" value={asset.name} />
            <Field label="Kategori" value={asset.category?.name ?? '-'} />
            <Field label="Status" value={<Badge>{asset.status}</Badge>} />
            <Field label="Merek / Model" value={`${asset.brand ?? ''} ${asset.model ?? ''}`.trim() || '-'} />
            <Field label="Serial Number" value={asset.serial_number ?? '-'} />
            <Field label="Site" value={asset.site?.name ?? '-'} />
            <Field label="Lokasi" value={asset.location?.name ?? '-'} />
            <Field label="Vendor" value={asset.vendor?.name ?? '-'} />
            <Field label="Departemen" value={asset.department?.name ?? '-'} />
            <Field label="Tanggal Beli" value={asset.purchase_date ?? '-'} />
            <Field label="Harga Perolehan" value={asset.purchase_cost != null ? fmtIDR.format(asset.purchase_cost) : '-'} />
            <Field label="Garansi s/d" value={asset.warranty_expiry_date ?? '-'} />
            <Field label="Nilai Sisa" value={asset.salvage_value != null ? fmtIDR.format(asset.salvage_value) : '-'} />
        </div>
    );
}

function Field({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="flex flex-col gap-1">
            <span className="text-xs text-muted-foreground">{label}</span>
            <span className="text-sm">{value || '-'}</span>
        </div>
    );
}

export default function AssetDetailPage() {
    const params = useParams<{ id: string }>();
    const id = Number(params.id);
    const [tab, setTab] = useState<TabKey>('info');
    const [qr, setQr] = useState<QrResponse | null>(null);

    const { data: asset, isLoading, isError } = useAsset(id);
    const tco = useAssetTco(id);
    const qrMutation = useGenerateQr(id);

    if (isLoading) {
        return (
            <div className="flex flex-col gap-4">
                <Skeleton className="h-8 w-48" />
                <Skeleton className="h-64 w-full" />
            </div>
        );
    }

    if (isError || !asset) {
        return (
            <div className="flex flex-col gap-4">
                <p className="text-sm text-muted-foreground">Aset tidak ditemukan atau gagal dimuat.</p>
                <Link href="/assets" className="text-sm font-medium text-primary underline-offset-4 hover:underline">
                    &larr; Kembali ke daftar
                </Link>
            </div>
        );
    }

    const handleGenerateQr = async () => {
        const res = await qrMutation.mutateAsync();
        setQr(res);
    };

    return (
        <div className="flex flex-col gap-6">
            {/* Judul + QR */}
            <div className="flex flex-wrap items-center justify-between gap-4">
                <div className="flex items-center gap-3">
                    <h1 className="text-2xl font-semibold">{asset.name}</h1>
                    <Badge>{asset.status}</Badge>
                </div>
                <div className="flex items-center gap-2">
                    <Button variant="outline" onClick={handleGenerateQr} disabled={qrMutation.isPending}>
                        {qrMutation.isPending ? 'Generating…' : 'Generate QR'}
                    </Button>
                    <Link href="/assets">
                        <Button variant="ghost">Kembali</Button>
                    </Link>
                </div>
            </div>

            {qr && (
                <Card className="gap-2">
                    <p className="text-xs text-muted-foreground">QR Payload</p>
                    <pre className="overflow-auto rounded bg-muted p-2 text-xs">{qr.qr_payload}</pre>
                    <a href={qr.signed_url} target="_blank" rel="noreferrer" className="text-xs text-primary underline">
                        {qr.signed_url}
                    </a>
                </Card>
            )}

            {/* Tab */}
            <div className="flex flex-wrap gap-1 border-b">
                {TABS.map((t) => (
                    <button
                        key={t.key}
                        onClick={() => setTab(t.key)}
                        className={
                            'px-3 py-2 text-sm font-medium transition-colors ' +
                            (tab === t.key
                                ? 'border-b-2 border-primary text-primary'
                                : 'text-muted-foreground hover:text-foreground')
                        }
                    >
                        {t.label}
                    </button>
                ))}
            </div>

            {/* Konten tab */}
            <div className="min-h-[200px]">
                {tab === 'info' && <InfoTab asset={asset} />}

                {tab === 'riwayat' && (
                    <div className="flex flex-col gap-2">
                        {(asset.transfers ?? []).length === 0 &&
                            <p className="text-sm text-muted-foreground">Belum ada riwayat perpindahan.</p>}
                        {(asset.transfers ?? []).map((t) => (
                            <Card key={t.id} className="gap-1 py-4">
                                <div className="flex items-center gap-2">
                                    <span className="font-mono text-xs">#{t.id}</span>
                                    <Badge variant="secondary">{t.status}</Badge>
                                </div>
                                {t.reason && <p className="text-sm text-muted-foreground">{t.reason}</p>}
                            </Card>
                        ))}
                    </div>
                )}

                {tab === 'pemeliharaan' && (
                    <div className="flex flex-col gap-2">
                        {(asset.workOrders ?? []).length === 0 &&
                            <p className="text-sm text-muted-foreground">Belum ada work order.</p>}
                        {(asset.workOrders ?? []).map((w) => (
                            <Card key={w.id} className="gap-1 py-4">
                                <div className="flex items-center gap-2">
                                    <span className="font-medium">{w.title ?? `WO #${w.id}`}</span>
                                    <Badge variant="outline">{w.type}</Badge>
                                    <Badge variant="secondary">{w.status}</Badge>
                                </div>
                                {w.actual_cost != null && (
                                    <p className="text-xs text-muted-foreground">
                                        Biaya aktual: {fmtIDR.format(w.actual_cost)}
                                    </p>
                                )}
                            </Card>
                        ))}
                    </div>
                )}

                {tab === 'biaya' && (
                    <div className="flex flex-col gap-4">
                        <Card className="flex-row items-center justify-between py-4">
                            <div>
                                <p className="text-xs text-muted-foreground">Total Biaya (TCO)</p>
                                <p className="text-2xl font-semibold">
                                    {tco.data ? fmtIDR.format(tco.data.total_cost) : '…'}
                                </p>
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {tco.data?.count ?? 0} entri
                            </p>
                        </Card>
                        <div className="flex flex-col gap-2">
                            {(asset.costEntries ?? []).map((c) => (
                                <div key={c.id} className="flex items-center justify-between rounded border p-3 text-sm">
                                    <span>{c.category}</span>
                                    <span className="font-medium">{fmtIDR.format(c.amount)}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {tab === 'dokumen' && (
                    <div className="flex flex-col gap-2">
                        {(asset.documents ?? []).length === 0 &&
                            <p className="text-sm text-muted-foreground">Belum ada dokumen terlampir.</p>}
                        {(asset.documents ?? []).map((url, i) => (
                            <a key={i} href={url} target="_blank" rel="noreferrer"
                               className="text-sm text-primary underline underline-offset-4">
                                Dokumen {i + 1}
                            </a>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}