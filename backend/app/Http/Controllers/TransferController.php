<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Transfer::with(['asset', 'fromSite', 'toSite', 'requester'])
            ->when($request->status, fn($q) => $q->where('status', $request->status));

        return response()->json($query->latest()->paginate($request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:assets,id',
            'from_site_id' => 'required|exists:sites,id',
            'to_site_id' => 'required|exists:sites,id|different:from_site_id',
            'to_location_id' => 'nullable|exists:locations,id',
            'reason' => 'nullable|string',
            'planned_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->asset_ids as $assetId) {
                Transfer::create([
                    'asset_id' => $assetId,
                    'from_site_id' => $request->from_site_id,
                    'to_site_id' => $request->to_site_id,
                    'to_location_id' => $request->to_location_id,
                    'requested_by' => $request->user()->id,
                    'reason' => $request->reason,
                    'planned_date' => $request->planned_date,
                    'status' => 'Draft',
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Transfers created.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(Transfer $transfer): JsonResponse
    {
        return response()->json($transfer->load(['asset', 'fromSite', 'toSite', 'toLocation', 'requester', 'items']));
    }

    public function update(Request $request, Transfer $transfer): JsonResponse
    {
        $request->validate([
            'status' => 'sometimes|in:Pending,Approved,Rejected,In Transit,Received,Cancelled',
            'reason' => 'nullable|string',
        ]);

        if (in_array($transfer->status, ['In Transit', 'Received'])) {
            return response()->json(['message' => 'Cannot modify transfer after dispatch.'], 422);
        }

        $transfer->update($request->only(['status', 'reason']));

        return response()->json($transfer);
    }

    public function destroy(Transfer $transfer): JsonResponse
    {
        $transfer->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    /**
     * POST /api/transfers/{transfer}/dispatch
     * Confirm keluar gudang asal (scanner scan QR).
     */
    public function dispatch(Transfer $transfer, Request $request): JsonResponse
    {
        if ($transfer->status !== 'Approved') {
            return response()->json(['message' => 'Transfer must be Approved before dispatch.'], 422);
        }

        $transfer->update([
            'status' => 'In Transit',
            'dispatched_at' => now(),
        ]);

        // Update aset ke status In Transit
        $transfer->asset?->update(['status' => 'In Transit']);

        return response()->json(['message' => 'Dispatched.', 'transfer' => $transfer->fresh()]);
    }

    /**
     * POST /api/transfers/{transfer}/receive
     * Konfirmasi penerimaan di gudang tujuan.
     */
    public function receive(Transfer $transfer, Request $request): JsonResponse
    {
        if ($transfer->status !== 'In Transit') {
            return response()->json(['message' => 'Transfer must be In Transit before receive.'], 422);
        }

        $transfer->update([
            'status' => 'Received',
            'received_at' => now(),
            'to_location_id' => $request->input('to_location_id', $transfer->to_location_id),
        ]);

        // Pindahkan aset ke site & lokasi baru
        $transfer->asset?->update([
            'site_id' => $transfer->to_site_id,
            'location_id' => $transfer->to_location_id,
            'status' => 'Available',
        ]);

        return response()->json(['message' => 'Received.', 'transfer' => $transfer->fresh()]);
    }
}
