<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => '帳號或密碼錯誤'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => ['email' => $user->email, 'role' => $user->role],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => '已登出']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'email' => $request->user()->email,
            'role' => $request->user()->role,
        ]);
    }
}
