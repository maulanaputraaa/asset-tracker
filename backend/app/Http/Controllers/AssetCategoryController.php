<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(AssetCategory::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:asset_categories,name',
            'custom_fields' => 'nullable|array',
        ]);

        $category = AssetCategory::create([
            'name' => $request->name,
            'custom_fields' => $request->input('custom_fields'),
        ]);

        return response()->json($category, 201);
    }

    public function show(AssetCategory $assetCategory): JsonResponse
    {
        return response()->json($assetCategory);
    }

    public function update(Request $request, AssetCategory $assetCategory): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255|unique:asset_categories,name,' . $assetCategory->id,
            'custom_fields' => 'nullable|array',
        ]);

        $assetCategory->update($request->only(['name', 'custom_fields']));

        return response()->json($assetCategory);
    }

    public function destroy(AssetCategory $assetCategory): JsonResponse
    {
        $assetCategory->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
