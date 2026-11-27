// Enum untuk status aset sesuai PRD FR-3.2
export type AssetStatus =
  | 'Registered'    // baru didaftarkan
  | 'Available'     // siap dipakai
  | 'In Use'        // sedang dipakai karyawan
  | 'In Transit'    // sedang dipindahkan
  | 'Under Maintenance'  // sedang dirawat
  | 'Damaged'       // rusak
  | 'Lost'          // hilang
  | 'Disposed';     // dibuang/dijual

// Representasi kategori aset dari backend
export interface AssetCategory {
    id: number;
    name: string;
    tenant_id: number;
}

// Representasi vendor dari backend
export interface Vendor {
    id: number;
    name: string;
    contact_person?: string;
    phone?: string;
    email?: string;
    tenant_id: number;
}

// Representasi department dari backend
export interface Department {
    id: number;
    code: string;
    name: string;
    tenant_id: number;
}

// Representasi site/gudang dari backend
export interface Site {
    id: number;
    code: string;
    name: string;
    address?: string;
    tenant_id: number;
}

// Representasi lokasi (rak/bin) dalam site
export interface Location {
    id: number;
    site_id: number;
    name: string;
    description?: string;
    tenant_id: number;
}

// REPRESENTASI ASSET — ini yang paling penting
export interface Asset {
    // ID unik sistem
    id: number;
    uuid: string;              // QR payload identifier
    tenant_id: number;         // isolasi multi-tenant

    // Relasi (foreign key)
    category_id: number;
    vendor_id?: number;
    department_id?: number;
    site_id?: number;
    location_id?: number;

    // Field identifikasi
    asset_code: string;        // auto-generate: AST-2026-000001
    name: string;              // nama aset (wajib)
    brand?: string;            // merek
    model?: string;            // model
    serial_number?: string;    // no seri

    // Lifecycle
    status: AssetStatus;       // sesuai enum di atas

    // Financial
    purchase_date?: string;         // ISO date
    purchase_cost?: number;         // nominal
    warranty_expiry_date?: string;  // tanggal jaminan
    salvage_value?: number;         // nilai sisa

    // Metadata
    specifications?: Record<string, unknown>;  // JSON custom fields
    photo?: string;                      // URL foto
    documents?: string[];                // array URL dokumen

    // Timestamps
    created_at: string;
    updated_at: string;

    // RELATIONS (opsional, load saat detail)
    category?: AssetCategory;
    vendor?: Vendor;
    department?: Department;
    site?: Site;
    location?: Location;
}

// DTO untuk create — field wajib + opsional tanpa ID/uuid/timestamp
export type CreateAssetDto = Omit<
    Asset,
    'id' | 'uuid' | 'tenant_id' | 'asset_code' | 'created_at' | 'updated_at'
>;

// DTO untuk update — semua field opsional (partial)
export type UpdateAssetDto = Partial<CreateAssetDto>;

// Response paginasi GET /api/assets (format Laravel paginator)
export interface AssetListResponse {
    data: Asset[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

// Param filter untuk daftar aset (FR-3.4)
export interface AssetFilters {
    search?: string;
    status?: AssetStatus;
    category_id?: number;
    site_id?: number;
    per_page?: number;
    page?: number;
}

// Daftar status aset untuk dropdown filter
export const ASSET_STATUSES: AssetStatus[] = [
    'Registered', 'Available', 'In Use', 'In Transit',
    'Under Maintenance', 'Damaged', 'Lost', 'Disposed',
];

// Relasi ringkas untuk tab
export interface TransferRow {
    id: number;
    status: string;
    reason?: string;
}
export interface WorkOrderRow {
    id: number;
    type: string;
    status: string;
    title?: string;
    estimated_cost?: number;
    actual_cost?: number;
}
export interface CostEntryRow {
    id: number;
    category: string;
    amount: number;
    transaction_date?: string;
    description?: string;
}
// Aset penuh dengan semua relasi yang di-load oleh GET /api/assets/{id}
export interface AssetDetail extends Asset {
    category?: AssetCategory;
    vendor?: Vendor;
    site?: Site;
    location?: Location;
    department?: Department;
    parents?: Asset[];
    children?: Asset[];
    transfers?: TransferRow[];
    workOrders?: WorkOrderRow[];
    costEntries?: CostEntryRow[];
}
// Response TCO — GET /api/assets/{id}/tco
export interface TcoResponse {
    asset_id: number;
    total_cost: number;
    by_category: Record<string, number>;
    count: number;
}
// Response QR generate — POST /api/assets/{id}/qr-generate
export interface QrResponse {
    qr_payload: string;
    signed_url: string;
    asset_uuid: string;
}