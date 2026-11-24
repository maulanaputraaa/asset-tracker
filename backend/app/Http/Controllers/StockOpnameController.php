<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockOpname::with(['site', 'items.asset'])
            ->when($request->site_id, fn($q) => $q->where('site_id', $request->site_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status));

        return response()->json($query->latest()->paginate($request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'site_id' => 'required|exists:sites,id',
        ]);

        $opname = StockOpname::create([
            'name' => $request->name,
            'site_id' => $request->site_id,
            'status' => 'Draft',
            'started_at' => now(),
        ]);

        // Buat item dari semua aset di site ini
        Asset::where('site_id', $request->site_id)->get()->each(function (Asset $asset) use ($opname) {
            StockOpnameItem::create([
                'stock_opname_id' => $opname->id,
                'asset_id' => $asset->id,
                'expected_count' => 1,
                'actual_count' => 0,
                'status' => 'Not Scanned',
            ]);
        });

        $opname->update(['status' => 'In Progress']);

        return response()->json($opname->load('items.asset'), 201);
    }

    public function show(StockOpname $stockOpname): JsonResponse
    {
        return response()->json($stockOpname->load('site', 'items.asset'));
    }

    public function update(Request $request, StockOpname $stockOpname): JsonResponse
    {
        $request->validate([
            'status' => 'sometimes|in:In Progress,Completed',
        ]);

        $stockOpname->update($request->only(['status']));

        return response()->json($stockOpname);
    }

    public function destroy(StockOpname $stockOpname): JsonResponse
    {
        $stockOpname->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    /**
     * POST /api/stock-opnames/{stockOpname}/scan
     * Scanner scan QR aset saat opname.
     */
    public function scanAsset(StockOpname $stockOpname, Request $request): JsonResponse
    {
        if ($stockOpname->status !== 'In Progress') {
            return response()->json(['message' => 'Opname must be In Progress to scan.'], 422);
        }

        $request->validate([
            'asset_uuid' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $asset = Asset::withoutTenantScope()->where('uuid', $request->asset_uuid)->first();

        if (! $asset) {
            // Catat sebagai aset tidak terdaftar
            StockOpnameItem::firstOrCreate(
                ['stock_opname_id' => $stockOpname->id, 'asset_id' => null],
                ['external_uuid' => $request->asset_uuid, 'status' => 'Not Registered']
            );
            return response()->json(['message' => 'Aset tidak terdaftar.'], 200);
        }

        $item = StockOpnameItem::firstOrNew([
            'stock_opname_id' => $stockOpname->id,
            'asset_id' => $asset->id,
        ]);

        $item->actual_count = ($item->actual_count ?? 0) + ($request->quantity ?? 1);
        $item->status = ($item->actual_count >= $item->expected_count) ? 'Found' : 'Partial';
        $item->scanned_at = now();
        $item->save();

        return response()->json([
            'message' => 'Scanned.',
            'asset' => $asset->only(['id', 'name', 'asset_code']),
            'item_status' => $item->status,
        ]);
    }

    /**
     * POST /api/stock-opnames/{stockOpname}/finalize
     * Tutup opname dan hitung selisih.
     */
    public function finalize(StockOpname $stockOpname): JsonResponse
    {
        if ($stockOpname->status !== 'In Progress') {
            return response()->json(['message' => 'Opname must be In Progress to finalize.'], 422);
        }

        $totalExpected = $stockOpname->items()->sum('expected_count');
        $totalActual = $stockOpname->items()->sum('actual_count');
        $found = $stockOpname->items()->where('status', 'Found')->count();
        $notScanned = $stockOpname->items()->where('status', 'Not Scanned')->count();

        $stockOpname->update([
            'status' => 'Completed',
            'completed_at' => now(),
            'discrepancy_expected' => $totalExpected,
            'discrepancy_actual' => $totalActual,
        ]);

        return response()->json([
            'message' => 'Opname finalized.',
            'stats' => [
                'total_items' => $stockOpname->items()->count(),
                'found' => $found,
                'not_scanned' => $notScanned,
                'expected_qty' => $totalExpected,
                'actual_qty' => $totalActual,
                'difference' => $totalExpected - $totalActual,
            ],
        ]);
    }
}
