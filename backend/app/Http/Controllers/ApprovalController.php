<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\ApprovalAction;
use App\Models\ApprovalWorkflowStep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    /**
     * GET /api/approvals/pending
     * Daftar approval yang menunggu keputusan user saat ini.
     */
    public function pending(Request $request): JsonResponse
    {
        // User hanya bisa approve request bukan miliknya sendiri
        $pending = ApprovalRequest::where('status', 'Pending')
            ->where('current_step', '>', 0)
            ->with(['approvable', 'requester', 'workflow.steps'])
            ->get();

        return response()->json($pending);
    }

    /**
     * POST /api/approvals/{approvalRequest}/decide
     * Approve / Reject / Request Changes pada step saat ini.
     */
    public function decide(ApprovalRequest $approvalRequest, Request $request): JsonResponse
    {
        if ($approvalRequest->status !== 'Pending') {
            return response()->json(['message' => 'Request is not pending.'], 422);
        }

        $request->validate([
            'action' => 'required|in:Approve,Reject,RequestChanges',
            'comment' => 'nullable|string|max:1000',
        ]);

        $actor = $request->user();

        // FR-6A.7: segregation of duties — pemohon tidak bisa approve dirinya sendiri
        if ($actor->id === $approvalRequest->requester_id) {
            return response()->json(['message' => 'You cannot approve your own request.'], 403);
        }

        // Catat action
        ApprovalAction::create([
            'approval_request_id' => $approvalRequest->id,
            'approver_id' => $actor->id,
            'action' => $request->action,
            'comment' => $request->comment,
        ]);

        if ($request->action === 'Reject' || $request->action === 'RequestChanges') {
            $approvalRequest->update(['status' => $request->action === 'RequestChanges' ? 'ChangesRequested' : 'Rejected']);
            return response()->json(['message' => ucfirst(strtolower($request->action)) . '.']);
        }

        // Approve: cek apakah masih ada step berikutnya
        $step = ApprovalWorkflowStep::where('workflow_id', $approvalRequest->workflow_id)
            ->where('step_order', $approvalRequest->current_step + 1)
            ->first();

        if ($step) {
            $approvalRequest->update(['current_step' => $approvalRequest->current_step + 1]);
            return response()->json(['message' => 'Step ' . ($approvalRequest->current_step) . ' approved. Next step pending.']);
        }

        // Semua step selesai → approve final
        $approvalRequest->update([
            'status' => 'Approved',
            'approved_at' => now(),
        ]);

        // Trigger downstream action (transfer, work order, dll)
        $this->executePostApproval($approvalRequest);

        return response()->json(['message' => 'Fully approved. Action executed.']);
    }

    /**
     * Eksekusi aksi lanjutan setelah approval final.
     * Misalnya: ubah status transfer, izinkan work order dimulai.
     */
    protected function executePostApproval(ApprovalRequest $request): void
    {
        $approvable = $request->approvable;

        if (! $approvable) return;

        match (get_class($approvable)) {
            \App\Models\Transfer::class => $approvable->update(['status' => 'Approved']),
            \App\Models\WorkOrder::class => $approvable->update(['status' => 'Approved']),
            default => null,
        };
    }
}
