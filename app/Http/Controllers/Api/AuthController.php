<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->assignRole('user');
        event(new Registered($user));

        $token = $user->createToken('register-' . now()->timestamp)->plainTextToken;

        return response()->json([
            'user' => $user->load('roles'),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'totp' => ['nullable', 'string'],
            'device_name' => ['nullable', 'string', 'max:64'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        if ($user->hasTwoFactorEnabled()) {
            if (empty($data['totp'])) {
                return response()->json(['two_factor_required' => true], 423);
            }
            $valid = Google2FA::verifyKey(decrypt($user->two_factor_secret), $data['totp']);
            if (!$valid) {
                throw ValidationException::withMessages(['totp' => 'Invalid 2FA code.']);
            }
        }

        $token = $user->createToken($data['device_name'] ?? 'api')->plainTextToken;

        return response()->json([
            'user' => $user->load('roles', 'permissions'),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('roles', 'permissions'),
        ]);
    }
}
