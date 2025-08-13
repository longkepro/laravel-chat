<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Events\UserSessionChange;

    class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

        if (Auth::attempt($credentials)) {

            request()->session()->regenerate();

            $user = Auth::user();

            //broadcast(new UserSessionChange("{$user->username} is online", "success"));

            return redirect()->intended('/'); 
        }

        else {
            throw ValidationException::withMessages([
                'username' => 'Wrong username or password',
                'password' => 'Wrong username or password'
            ]);
        }
    }
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();        // Hủy toàn bộ session
        $request->session()->regenerateToken();   // Tạo CSRF token mới

    return redirect('/');
    }
}
