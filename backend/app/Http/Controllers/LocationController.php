<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Location::with('parent')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:locations,id',
        ]);

        $location = Location::create($request->only(['site_id', 'name', 'code', 'parent_id']));

        return response()->json($location, 201);
    }

    public function show(Location $location): JsonResponse
    {
        return response()->json($location->load('parent.site'));
    }

    public function update(Request $request, Location $location): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:locations,id',
        ]);

        $location->update($request->only(['name', 'code', 'parent_id']));

        return response()->json($location);
    }

    public function destroy(Location $location): JsonResponse
    {
        $location->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
