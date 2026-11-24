<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\JsonData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Vendor::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $vendor = Vendor::create($request->only(['name', 'contact_person', 'phone', 'email', 'address']));

        return response()->json($vendor, 201);
    }

    public function show(Vendor $vendor): JsonResponse
    {
        return response()->json($vendor);
    }

    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $vendor->update($request->only(['name', 'contact_person', 'phone', 'email', 'address']));

        return response()->json($vendor);
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $vendor->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
