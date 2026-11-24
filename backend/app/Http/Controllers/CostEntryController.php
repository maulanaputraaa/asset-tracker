<?php

namespace App\Http\Controllers;

use App\Models\CostEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CostEntryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CostEntry::with(['asset', 'workOrder', 'transfer', 'vendor', 'budget'])
            ->when($request->asset_id, fn($q) => $q->where('asset_id', $request->asset_id))
            ->when($request->work_order_id, fn($q) => $q->where('work_order_id', $request->work_order_id))
            ->when($request->category, fn($q) => $q->where('category', $request->category));

        $perPage = $request->input('per_page', 15);
        $costs = $query->latest()->paginate($perPage);

        return response()->json($costs);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id' => 'nullable|exists:assets,id',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'transfer_id' => 'nullable|exists:transfers,id',
            'category' => 'required|in:Acquisition,Maintenance,Repair,Sparepart,Logistics,Operational,Disposal',
            'amount' => 'required|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
            'invoice_number' => 'nullable|string|max:255',
        ]);

        $entry = CostEntry::create($request->only([
            'asset_id', 'work_order_id', 'transfer_id', 'category',
            'amount', 'currency', 'transaction_date', 'description', 'invoice_number',
        ]));

        return response()->json($entry, 201);
    }

    public function show(CostEntry $costEntry): JsonResponse
    {
        return response()->json($costEntry->load(['asset', 'workOrder', 'transfer', 'vendor']));
    }

    public function update(Request $request, CostEntry $costEntry): JsonResponse
    {
        $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'category' => 'sometimes|in:Acquisition,Maintenance,Repair,Sparepart,Logistics,Operational,Disposal',
            'transaction_date' => 'sometimes|date',
            'description' => 'nullable|string',
        ]);

        $costEntry->update($request->only(['amount', 'category', 'transaction_date', 'description']));

        return response()->json($costEntry);
    }

    public function destroy(CostEntry $costEntry): JsonResponse
    {
        $costEntry->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    /**
     * GET /api/assets/{asset}/tco
     * Total Cost of Ownership per aset.
     */
    public function tco(int $assetId): JsonResponse
    {
        $total = CostEntry::where('asset_id', $assetId)->sum('amount');
        $byCategory = CostEntry::where('asset_id', $assetId)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return response()->json([
            'asset_id' => $assetId,
            'total_cost' => $total,
            'by_category' => $byCategory,
            'count' => CostEntry::where('asset_id', $assetId)->count(),
        ]);
    }
}
