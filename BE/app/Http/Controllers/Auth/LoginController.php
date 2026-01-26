<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use App\Events\UserSessionChange;

    class LoginController extends Controller
{
    // public function showLoginForm()
    // {
    //     return view('auth.login');
    // }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            throw ValidationException::withMessages([
                'username' => 'Wrong username or password',
                'password' => 'Wrong username or password',
            ]);
        }

        $user = $request->user();

        // Access token ngắn hạn cho microservices
        $accessToken = $user->createToken('access', ['*'], Carbon::now()->/*addMinutes*/addDays(15))->plainTextToken;

        // Refresh token dài hạn, lưu trong cookie HttpOnly Secure
        $refreshToken = $user->createToken('refresh', ['refresh'], Carbon::now()->addDays(30))->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name ?? null,
            ],
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 15 * 60,
        ])->cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 30,
            '/',
            null,
            true,
            true,
            false,
            'Lax'
        );
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        if ($user) {
            // Revoke all tokens (access + refresh) for simplicity
            $user->tokens()->delete();
        }

        return response()
            ->json(['status' => 'logged_out'])
            ->withCookie(cookie()->forget('refresh_token'));
    }
}
