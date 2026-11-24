<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::select(['id', 'name', 'email', 'role', 'site_id', 'is_active', 'phone', 'last_login_at'])
            ->when($request->site_id, fn($q) => $q->where('site_id', $request->site_id))
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->is_active !== null, fn($q) => $q->where('is_active', $request->boolean('is_active')));

        return response()->json($query->latest()->paginate($request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,teknisi,karyawan',
            'site_id' => 'nullable|exists:sites,id',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'tenant_id' => $request->header('X-Tenant-ID'),
            'site_id' => $request->site_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
            'notification_preferences' => ['email' => true, 'push' => true],
        ]);

        return response()->json($user->makeHidden('password'), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->makeHidden('password'));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'role' => 'sometimes|in:admin,teknisi,karyawan',
            'site_id' => 'nullable|exists:sites,id',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'notification_preferences' => 'nullable|array',
        ]);

        $user->update($request->only([
            'name', 'role', 'site_id', 'phone', 'is_active', 'notification_preferences',
        ]));

        return response()->json($user->makeHidden('password'));
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
