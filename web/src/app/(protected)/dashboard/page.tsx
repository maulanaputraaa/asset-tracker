'use client';

import Link from 'next/link';
import { Boxes, CheckCircle, Clock, Loader2, Wrench } from 'lucide-react';
import { Card } from '@/components/ui/card';
import { useAssets } from '@/hooks/useAssets';
import { usePendingApprovals } from '@/hooks/useMasterData';

function StatCard({
    icon, label, value, hint, loading,
}: {
    icon: React.ReactNode;
    label: string;
    value: number;
    hint: string;
    loading: boolean;
}) {
    return (
        <Card className="gap-2 py-5">
            <div className="flex items-center justify-between">
                <p className="text-sm text-muted-foreground">{label}</p>
                <div className="flex size-9 items-center justify-center rounded-lg bg-muted text-foreground">
                    {icon}
                </div>
            </div>
            {loading ? (
                <Loader2 className="size-6 animate-spin text-muted-foreground" />
            ) : (
                <p className="text-3xl font-semibold tracking-tight">{value}</p>
            )}
            <p className="text-xs text-muted-foreground">{hint}</p>
        </Card>
    );
}

export default function DashboardPage() {
    const totalQ = useAssets({ per_page: 1 });
    const inUseQ = useAssets({ status: 'In Use', per_page: 1 });
    const maintQ = useAssets({ status: 'Under Maintenance', per_page: 1 });
    const pendingQ = usePendingApprovals();

    const total = totalQ.data?.total ?? 0;
    const inUse = inUseQ.data?.total ?? 0;
    const maint = maintQ.data?.total ?? 0;
    const pending = pendingQ.data?.length ?? 0;

    return (
        <div className="flex flex-col gap-6">
            <div>
                <h1 className="text-2xl font-semibold">Dashboard</h1>
                <p className="text-sm text-muted-foreground">Ringkasan aset &amp; aktivitas tenant kamu</p>
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard icon={<Boxes className="size-4" />} label="Total Aset" value={total} hint="Seluruh site" loading={totalQ.isLoading} />
                <StatCard icon={<Clock className="size-4" />} label="In Use" value={inUse} hint="Sedang dipakai" loading={inUseQ.isLoading} />
                <StatCard icon={<Wrench className="size-4" />} label="Under Maintenance" value={maint} hint="Dalam perbaikan" loading={maintQ.isLoading} />
                <StatCard icon={<CheckCircle className="size-4" />} label="Menunggu Approval" value={pending} hint="Butuh keputusan" loading={pendingQ.isLoading} />
            </div>

            <Card className="gap-2 py-5">
                <p className="text-sm text-muted-foreground">Mulai dari</p>
                <Link href="/assets" className="text-sm font-medium text-primary underline-offset-4 hover:underline">
                    Lihat daftar aset &rarr;
                </Link>
            </Card>
        </div>
    );
}