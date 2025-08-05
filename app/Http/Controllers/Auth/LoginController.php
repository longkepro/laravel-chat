<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

    class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('userName', 'password');

        $request->validate([
                'userName' => 'required|string',
                'password' => 'required|string|',
            ]);

        if (Auth::attempt($credentials)) {

            request()->session()->regenerate();

            return redirect()->intended('/'); // hoặc route bất kỳ
        }

        else {
            throw ValidationException::withMessages([
                'userName' => 'Wrong username or password',
                'password' => 'Wrong username or password'
            ]);
        }
    }
    public function destroy()
    {
        Auth::logout();
        return redirect()->intended('/');
    }
}
