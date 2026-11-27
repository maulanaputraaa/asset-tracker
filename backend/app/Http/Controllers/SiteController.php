<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Site::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:sites,code',
            'address' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $site = Site::create($request->only(['name', 'code', 'address', 'manager_id']));

        return response()->json($site, 201);
    }

    public function show(Site $site): JsonResponse
    {
        return response()->json($site->load(['assets', 'locations', 'manager']));
    }

    public function update(Request $request, Site $site): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:sites,code,' . $site->id,
            'address' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $site->update($request->only(['name', 'code', 'address', 'manager_id']));

        return response()->json($site);
    }

    public function destroy(Site $site): JsonResponse
    {
        $site->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
