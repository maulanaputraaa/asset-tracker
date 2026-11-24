<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Asset::with(['category', 'vendor', 'site', 'location', 'department', 'parents', 'children'])
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->site_id, fn($q) => $q->where('site_id', $request->site_id))
            ->when($request->search, fn($q) => $q->where(fn($q2) => $q2->where('name', 'like', "%{$request->search}%")
                ->orWhere('asset_code', 'like', "%{$request->search}%")
                ->orWhere('serial_number', 'like', "%{$request->search}%")));

        $assets = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json($assets);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'site_id' => 'nullable|exists:sites,id',
            'location_id' => 'nullable|exists:locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_cost' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_expiry_date' => 'nullable|date',
            'salvage_value' => 'nullable|numeric|min:0',
            'specifications' => 'nullable|array',
            'documents' => 'nullable|array',
            'status' => 'sometimes|in:Registered,Available,In Use,In Transit,Under Maintenance,Damaged,Lost,Disposed',
        ]);

        $code = $this->generateAssetCode();

        $asset = Asset::create(array_merge(
            $request->only([
                'name', 'category_id', 'vendor_id', 'site_id', 'location_id', 'department_id',
                'brand', 'model', 'serial_number', 'purchase_cost', 'purchase_date',
                'warranty_expiry_date', 'salvage_value', 'specifications', 'documents',
            ]),
            ['asset_code' => $code, 'status' => 'Registered']
        ));

        return response()->json($asset->load(['category', 'site', 'location']), 201);
    }

    public function show(Asset $asset): JsonResponse
    {
        return response()->json($asset->load([
            'category', 'vendor', 'site', 'location', 'department',
            'parents', 'children', 'assignments', 'transfers',
            'workOrders', 'costEntries',
        ]));
    }

    public function update(Request $request, Asset $asset): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:asset_categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'site_id' => 'nullable|exists:sites,id',
            'location_id' => 'nullable|exists:locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_cost' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_expiry_date' => 'nullable|date',
            'salvage_value' => 'nullable|numeric|min:0',
            'specifications' => 'nullable|array',
            'status' => 'sometimes|in:Registered,Available,In Use,In Transit,Under Maintenance,Damaged,Lost,Disposed',
        ]);

        $asset->update($request->except(['_token']));

        return response()->json($asset->load(['category', 'site', 'location']));
    }

    public function destroy(Asset $asset): JsonResponse
    {
        $asset->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    /**
     * POST /api/assets/{asset}/qr-generate
     * Generate signed QR payload for asset.
     */
    public function generateQr(Asset $asset): JsonResponse
    {
        $payload = [
            'aets' => 'asset',
            'slug' => optional($asset->tenant)->slug ?? '',
            'uuid' => $asset->uuid,
            'ts' => time(),
        ];

        // Signed JWT-like payload — simple base64 signing untuk MVP
        $sign = base64_encode(hash_hmac('sha256', json_encode($payload), env('APP_KEY'), true));

        return response()->json([
            'qr_payload' => json_encode($payload),
            'signed_url' => route('assets.qr.show', $asset->uuid) . '?s=' . $sign,
            'asset_uuid' => $asset->uuid,
        ]);
    }

    private function generateAssetCode(): string
    {
        $prefix = 'AST';
        $count = Asset::count() + 1;
        return sprintf('%s-%s-%06d', $prefix, now()->format('Y'), $count);
    }
}
