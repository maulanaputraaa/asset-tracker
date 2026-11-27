<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Site;
use App\Models\AssetCategory;
use App\Models\Vendor;
use App\Models\Department;
use App\Models\Asset;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tenant default
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default Tenant', 'status' => 'active']
        );

        // 2. Admin user tenant ini
        User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Demo Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
                'notification_preferences' => ['email' => true, 'push' => true],
            ]
        );

        // 3. Teknisi
        User::firstOrCreate(
            ['email' => 'teknisi@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Budi Teknisi',
                'password' => bcrypt('password'),
                'role' => 'teknisi',
                'is_active' => true,
            ]
        );

        // 4. Karyawan
        User::firstOrCreate(
            ['email' => 'karyawan@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Siti Karyawan',
                'password' => bcrypt('password'),
                'role' => 'karyawan',
                'is_active' => true,
            ]
        );

        // 5. Site & master data
        Site::firstOrCreate(
            ['code' => 'SITE-001'],
            ['name' => 'Gudang Utama', 'address' => 'Jl. Industri No. 1', 'tenant_id' => $tenant->id]
        );

        // 6. Aset demo (idempoten, aman di-rerun)
        $this->call(AssetDemoSeeder::class);

        AssetCategory::firstOrCreate(['name' => 'Mesin'], ['tenant_id' => $tenant->id]);
        AssetCategory::firstOrCreate(['name' => 'Kendaraan'], ['tenant_id' => $tenant->id]);
        AssetCategory::firstOrCreate(['name' => 'Elektronik'], ['tenant_id' => $tenant->id]);

        Vendor::firstOrCreate(['name' => 'PT Supplier A'], ['tenant_id' => $tenant->id]);
        Vendor::firstOrCreate(['name' => 'CV Material B'], ['tenant_id' => $tenant->id]);

        Department::firstOrCreate(['code' => 'IT'], ['name' => 'IT Department', 'tenant_id' => $tenant->id]);
        Department::firstOrCreate(['code' => 'PRD'], ['name' => 'Produksi', 'tenant_id' => $tenant->id]);
        Department::firstOrCreate(['code' => 'FIN'], ['name' => 'Finance', 'tenant_id' => $tenant->id]);

        echo "Seed complete.\n";
    }
}
