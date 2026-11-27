<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Site;
use App\Models\AssetCategory;
use App\Models\Vendor;
use App\Models\Department;
use App\Models\Asset;
use App\Models\CostEntry;
use Illuminate\Database\Seeder;

class AssetDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'default')->first();
        if (!$tenant) {
            $this->command?->warn('Tenant "default" tidak ditemukan. Jalankan DatabaseSeeder dulu.');
            return;
        }

        // Master data — fallback create kalau belum ada
        $site = Site::where('code', 'SITE-001')->first()
            ?? Site::create(['code' => 'SITE-001', 'name' => 'Gudang Utama', 'tenant_id' => $tenant->id]);

        $cat    = fn (string $n) => AssetCategory::firstOrCreate(['name' => $n], ['tenant_id' => $tenant->id]);
        $mesin  = $cat('Mesin');
        $kend   = $cat('Kendaraan');
        $elek   = $cat('Elektronik');

        $vendor = Vendor::firstOrCreate(['name' => 'PT Supplier A'], ['tenant_id' => $tenant->id]);
        $dept   = Department::firstOrCreate(['code' => 'PRD'], ['name' => 'Produksi', 'tenant_id' => $tenant->id]);

        // 10 aset demo — status disebarkan biar filter & stat dashboard teruji
        $base = ['tenant_id' => $tenant->id, 'site_id' => $site->id,
                 'vendor_id' => $vendor->id, 'department_id' => $dept->id];

        $rows = [
            ['asset_code' => 'AST-DEMO-0001', 'name' => 'CNC Milling 3 Axis', 'category_id' => $mesin->id, 'status' => 'In Use', 'brand' => 'Haas', 'model' => 'VF-2Y', 'serial_number' => 'HAAS-88231', 'purchase_cost' => 450000000, 'purchase_date' => '2024-03-10', 'warranty_expiry_date' => '2026-03-10', 'specifications' => ['kapasitas' => '300 mm/min', 'tegangan' => '380V']],
            ['asset_code' => 'AST-DEMO-0002', 'name' => 'Laptop Engineering', 'category_id' => $elek->id, 'status' => 'Available', 'brand' => 'Dell', 'model' => 'Precision 5480', 'serial_number' => 'DL-55210', 'purchase_cost' => 28000000, 'purchase_date' => '2025-01-15', 'warranty_expiry_date' => '2028-01-15', 'documents' => ['https://example.com/invoice-002.pdf']],
            ['asset_code' => 'AST-DEMO-0003', 'name' => 'Press Hidrolik 200T', 'category_id' => $mesin->id, 'status' => 'In Use', 'brand' => 'Tokyo Steel', 'model' => 'TP-200', 'serial_number' => 'TS-2019-44', 'purchase_cost' => 620000000, 'purchase_date' => '2019-06-01', 'salvage_value' => 45000000],
            ['asset_code' => 'AST-DEMO-0004', 'name' => 'Kombi Workshop', 'category_id' => $kend->id, 'status' => 'Under Maintenance', 'brand' => 'Daihatsu', 'model' => 'Gran Max', 'serial_number' => 'B1021XYZ', 'purchase_cost' => 210000000, 'purchase_date' => '2022-08-20'],
            ['asset_code' => 'AST-DEMO-0005', 'name' => 'Forklift 3 Ton', 'category_id' => $kend->id, 'status' => 'Available', 'brand' => 'Toyota', 'model' => '8FDM30', 'serial_number' => 'TY-3390', 'purchase_cost' => 350000000, 'purchase_date' => '2021-11-05'],
            ['asset_code' => 'AST-DEMO-0006', 'name' => 'Kompressor Udara 5HP', 'category_id' => $mesin->id, 'status' => 'Under Maintenance', 'brand' => 'Atlas Copco', 'model' => 'ZR5', 'serial_number' => 'AC-77120', 'purchase_cost' => 95000000, 'purchase_date' => '2020-04-18'],
            ['asset_code' => 'AST-DEMO-0007', 'name' => 'Proyektor Meeting', 'category_id' => $elek->id, 'status' => 'Registered', 'brand' => 'Epson', 'model' => 'EB-X500', 'serial_number' => 'EP-20251', 'purchase_cost' => 8500000, 'purchase_date' => '2026-02-01'],
            ['asset_code' => 'AST-DEMO-0008', 'name' => 'Gerinda Industri', 'category_id' => $mesin->id, 'status' => 'Damaged', 'brand' => 'Makita', 'model' => 'GA9020', 'serial_number' => 'MK-99170', 'purchase_cost' => 2100000, 'purchase_date' => '2018-07-12'],
            ['asset_code' => 'AST-DEMO-0009', 'name' => 'Mobil Operasional', 'category_id' => $kend->id, 'status' => 'In Transit', 'brand' => 'Mitsubishi', 'model' => 'L300', 'serial_number' => 'B2201KAA', 'purchase_cost' => 180000000, 'purchase_date' => '2023-05-30'],
            ['asset_code' => 'AST-DEMO-0010', 'name' => 'Server Rack 42U', 'category_id' => $elek->id, 'status' => 'Available', 'brand' => 'Schneider', 'model' => 'SR642', 'serial_number' => 'SN-2024-88', 'purchase_cost' => 120000000, 'purchase_date' => '2024-09-01'],
        ];

        foreach ($rows as $row) {
            // firstOrCreate key asset_code (uniku) → aman di-rerun
            Asset::firstOrCreate(
                ['asset_code' => $row['asset_code']],
                $row + $base
            );
        }

        // Cost entries buat 1 aset (tab "Biaya" jadi ada isinya) — idempoten
        $target = Asset::where('asset_code', 'AST-DEMO-0001')->first();
        if ($target && CostEntry::where('asset_id', $target->id)->count() === 0) {
            CostEntry::create(['asset_id' => $target->id, 'category' => 'Acquisition', 'amount' => 450000000, 'transaction_date' => '2024-03-10', 'description' => 'Pembelian mesin']);
            CostEntry::create(['asset_id' => $target->id, 'category' => 'Maintenance', 'amount' => 12000000, 'transaction_date' => '2025-06-15', 'description' => 'Servis PM kuartalan']);
            CostEntry::create(['asset_id' => $target->id, 'category' => 'Sparepart', 'amount' => 3500000, 'transaction_date' => '2025-09-02', 'description' => 'Penggantian chuck']);
        }

        $this->command?->info('Seed 10 aset + 3 cost entry selesai.');
    }
}