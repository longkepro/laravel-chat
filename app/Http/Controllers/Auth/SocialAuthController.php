<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }
    public function redirectToFaceBook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();//bỏ qua được, Intelephense đoán kiểu trả về là Laravel\Socialite\Contracts\Provider, tức là interface: nên sẽ báo lỗi stateless(), nhưng PHP không cần biết kiểu trả về tại thời điểm biên dịch như Java/C#. Miễn là object thực sự có method stateless() thì PHP vẫn chạy tốt.

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'userName' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(Str::random(16)), // giả lập mật khẩu
                'google_id' => $googleUser->getId(),
            ]);
        }

        Auth::login($user);

        return redirect('/');
    }

    public function handleFaceBookCallback()
    {
        $faceBookUser = Socialite::driver('facebook')->stateless()->user();//bỏ qua được, Intelephense đoán kiểu trả về là Laravel\Socialite\Contracts\Provider, tức là interface: nên sẽ báo lỗi stateless(), nhưng PHP không cần biết kiểu trả về tại thời điểm biên dịch như Java/C#. Miễn là object thực sự có method stateless() thì PHP vẫn chạy tốt.

        $user = User::where('email', $faceBookUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'userName' => $faceBookUser->getName(),
                'email' => $faceBookUser->getEmail(),
                'password' => bcrypt(Str::random(16)), // giả lập mật khẩu
                'facebook_id' => $faceBookUser->getId(),
            ]);
        }

        Auth::login($user);

        return redirect('/');
    }
}
