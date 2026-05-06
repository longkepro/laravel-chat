<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as RulesPassword;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $attributes = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', RulesPassword::default(), 'confirmed'],
        ]);

        $user = User::create([
            'username' => $attributes['username'],
            'email' => $attributes['email'],
            'password' => Hash::make($attributes['password']),
            'profile_name' => $attributes['username'],
            'avatar' => 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'User registered!',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->profile_name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
        ], 201);
    }
}
