<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Budget::with('tenant', 'site')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'allocated_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'site_id' => 'nullable|exists:sites,id',
            'description' => 'nullable|string',
        ]);

        $budget = Budget::create($request->only(['name', 'allocated_amount', 'start_date', 'end_date', 'site_id', 'description']));

        return response()->json($budget, 201);
    }

    public function show(Budget $budget): JsonResponse
    {
        return response()->json($budget->load('costEntries'));
    }

    public function update(Request $request, Budget $budget): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'allocated_amount' => 'sometimes|numeric|min:0',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'site_id' => 'nullable|exists:sites,id',
        ]);

        $budget->update($request->only(['name', 'allocated_amount', 'start_date', 'end_date', 'site_id']));

        return response()->json($budget);
    }

    public function destroy(Budget $budget): JsonResponse
    {
        $budget->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
