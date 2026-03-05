<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            throw ValidationException::withMessages([
                'username' => ['Wrong username or password'],
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        return response()->json([
            'user' => [
                'id'       => $user->id,
                'username' => $user->username,
                'name'     => $user->profile_name,
            ],
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }
}
