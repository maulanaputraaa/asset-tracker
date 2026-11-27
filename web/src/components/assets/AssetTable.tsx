import Link from 'next/link';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import type { Asset, AssetStatus } from '@/types';


const fmtIDR = new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
});

// Warna badge per status (FR-3.2)
const statusVariant: Record<AssetStatus, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    'Registered': 'secondary',
    'Available': 'default',
    'In Use': 'outline',
    'In Transit': 'secondary',
    'Under Maintenance': 'destructive',
    'Damaged': 'destructive',
    'Lost': 'destructive',
    'Disposed': 'secondary',
};

export function AssetTable({ assets, loading }: { assets: Asset[]; loading?: boolean }) {
    if (loading) {
        return (
            <div className="space-y-2">
                {Array.from({ length: 5 }).map((_, i) => (
                    <Skeleton key={i} className="h-10 w-full" />
                ))}
            </div>
        );
    }

    if (!assets.length) {
        return (
            <div className="flex h-40 items-center justify-center rounded-lg border text-sm text-muted-foreground">
                Tidak ada aset yang cocok
            </div>
        );
    }

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Kode</TableHead>
                    <TableHead>Nama</TableHead>
                    <TableHead>Kategori</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Site</TableHead>
                    <TableHead className="text-right">Harga</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {assets.map((a) => (
                    <TableRow key={a.id} className="cursor-pointer hover:bg-muted/40">
                        <TableCell className="font-mono text-xs">{a.asset_code}</TableCell>
                        <TableCell className="font-medium">
                            <Link href={`/assets/${a.id}`}
                                className="text-primary underline-offset-4 hover:underline">
                                {a.name}
                            </Link>
                        </TableCell>

                        <TableCell className="text-muted-foreground">
                            {a.category?.name ?? '-'}
                        </TableCell>
                        <TableCell>
                            <Badge variant={statusVariant[a.status]}>{a.status}</Badge>
                        </TableCell>
                        <TableCell className="text-muted-foreground">
                            {a.site?.name ?? '-'}
                        </TableCell>
                        <TableCell className="text-right">
                            {a.purchase_cost != null ? fmtIDR.format(a.purchase_cost) : '-'}
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}