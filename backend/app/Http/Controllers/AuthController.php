<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * POST /api/register
     * Mendaftarkan user baru ke tenant (tenant_id diisi otomatis dari header X-Tenant-ID).
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['nullable', 'in:admin,teknisi,karyawan'],
            'site_id' => ['nullable', 'integer'],
        ]);

        $user = User::create([
            'tenant_id' => $request->header('X-Tenant-ID'),
            'site_id' => $request->input('site_id'),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->input('role', 'karyawan'),
            'is_active' => true,
            'notification_preferences' => [
                'email' => true,
                'push' => true,
                'sound' => true,
            ],
        ]);

        $token = $user->createToken('device-' . now()->timestamp)->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user->only(['id', 'name', 'email', 'role', 'site_id']),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * POST /api/login
     * Login dengan email+password, kembalikan Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Account is deactivated.',
            ], 403);
        }

        // Revoke all existing tokens lalu buat yang baru
        $user->tokens()->delete();
        $token = $user->createToken('device-' . now()->timestamp)->plainTextToken;

        $user->update(['last_login_at' => now()]);

        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'role', 'tenant_id', 'site_id']),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * POST /api/logout
     * Hapus semua token device saat ini.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    /**
     * GET /api/me
     * Return data user yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->only(['id', 'name', 'email', 'role', 'tenant_id', 'site_id', 'phone', 'employee_id']),
        ]);
    }
}
