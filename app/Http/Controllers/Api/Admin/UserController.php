<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::with('roles')
            ->when($request->search, fn ($q, $s) => $q->where('email', 'like', "%{$s}%"))
            ->paginate(20);

        return response()->json($users);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['role' => ['required', 'string', 'exists:roles,name']]);
        $user->syncRoles([$data['role']]);

        return response()->json(['user' => $user->load('roles')]);
    }
}
