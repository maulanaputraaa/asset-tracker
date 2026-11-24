<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkOrder::with(['asset', 'technician'])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->asset_id, fn($q) => $q->where('asset_id', $request->asset_id));

        return response()->json($query->latest()->paginate($request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:Preventive,Corrective,Inspection',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'technician_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date',
            'checklist' => 'nullable|array',
        ]);

        $wo = WorkOrder::create([
            'asset_id' => $request->asset_id,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'technician_id' => $request->technician_id,
            'priority' => $request->priority ?? 'medium',
            'estimated_cost' => $request->estimated_cost,
            'estimated_hours' => $request->estimated_hours,
            'due_date' => $request->due_date,
            'checklist' => $request->checklist,
            'status' => 'Open',
        ]);

        return response()->json($wo, 201);
    }

    public function show(WorkOrder $workOrder): JsonResponse
    {
        return response()->json($workOrder->load(['asset', 'technician', 'items']));
    }

    public function update(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $request->validate([
            'status' => 'sometimes|in:Open,Assigned,In Progress,Waiting Parts,Approval,Completed,Closed',
            'technician_id' => 'nullable|exists:users,id',
            'actual_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $data = $request->only(['status', 'technician_id', 'actual_cost', 'notes']);

        // Auto-set timestamps based on status change
        if ($request->input('status') === 'In Progress' && empty($workOrder->started_at)) {
            $data['started_at'] = now();
        }

        if ($request->input('status') === 'Completed') {
            $data['completed_at'] = now();
            // Auto-update asset status back to Available
            $workOrder->asset?->update(['status' => 'Available']);
        }

        $workOrder->update($data);

        return response()->json($workOrder->fresh()->load(['asset', 'technician']));
    }

    public function destroy(WorkOrder $workOrder): JsonResponse
    {
        $workOrder->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    /**
     * POST /api/work-orders/{workOrder}/start
     * Teknisi mulai mengerjakan WO.
     */
    public function start(WorkOrder $workOrder, Request $request): JsonResponse
    {
        if ($workOrder->status !== 'Assigned') {
            return response()->json(['message' => 'WO must be Assigned before starting.'], 422);
        }

        $workOrder->update(['status' => 'In Progress', 'started_at' => now()]);

        // Set aset ke Under Maintenance
        $workOrder->asset?->update(['status' => 'Under Maintenance']);

        return response()->json(['message' => 'Work order started.', 'work_order' => $workOrder->fresh()]);
    }

    /**
     * POST /api/work-orders/{workOrder}/complete
     * Teknisi menandai WO selesai.
     */
    public function complete(WorkOrder $workOrder, Request $request): JsonResponse
    {
        if ($workOrder->status !== 'In Progress') {
            return response()->json(['message' => 'WO must be In Progress before completing.'], 422);
        }

        $request->validate([
            'actual_cost' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $workOrder->update([
            'status' => 'Completed',
            'completed_at' => now(),
            'actual_cost' => $request->actual_cost,
            'notes' => $request->notes,
        ]);

        // Kembalikan aset ke Available
        $workOrder->asset?->update(['status' => 'Available']);

        return response()->json(['message' => 'Work order completed.', 'work_order' => $workOrder->fresh()]);
    }
}
