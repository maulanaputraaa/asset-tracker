<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Department::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
        ]);

        $dept = Department::create($request->only(['name', 'code']));

        return response()->json($dept, 201);
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json($department);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:departments,code,' . $department->id,
        ]);

        $department->update($request->only(['name', 'code']));

        return response()->json($department);
    }

    public function destroy(Department $department): JsonResponse
    {
        $department->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
