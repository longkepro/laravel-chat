<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as RulesPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    // public function showRegisterForm()
    // {
    //     return view('auth.register');
    // }

    public function Register(Request $request)
    {
        //$credentials = $request->only('username','email', 'password','password_confirmation');

        $request->validate([
                'username' => 'required|string',
                'password' => ['required', 'string',RulesPassword::default(),'confirmed'],
                'email' => 'required|email|'
            ]);

        $attributes = $request->only('username','email', 'password');
        $attributes['password'] = Hash::make($attributes['password']);
        User::create($attributes);

        // //log in
        // Auth::login($user);

        // //redirect
        // return redirect('/');
        return response()->json(['status' => 'User Registered!']);

    }
}
