<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceScheduleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(MaintenanceSchedule::with('assets')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'interval_days' => 'required|integer|min:1',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:asset_categories,id',
        ]);

        $schedule = MaintenanceSchedule::create([
            'name' => $request->name,
            'interval_days' => $request->interval_days,
            'last_run_date' => now(),
            'next_due_date' => now()->addDays($request->interval_days),
        ]);

        if ($request->has('category_ids')) {
            $schedule->categories()->attach($request->category_ids);
        }

        return response()->json($schedule->load('categories'), 201);
    }

    public function show(MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        return response()->json($maintenanceSchedule->load('categories', 'workOrders'));
    }

    public function update(Request $request, MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'interval_days' => 'sometimes|integer|min:1',
            'category_ids' => 'nullable|array',
        ]);

        $data = $request->only(['name', 'interval_days']);
        if (isset($data['interval_days'])) {
            $data['next_due_date'] = now()->addDays($data['interval_days']);
        }

        $maintenanceSchedule->update($data);

        if ($request->has('category_ids')) {
            $maintenanceSchedule->categories()->sync($request->category_ids);
        }

        return response()->json($maintenanceSchedule->load('categories'));
    }

    public function destroy(MaintenanceSchedule $maintenanceSchedule): JsonResponse
    {
        $maintenanceSchedule->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
