<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
        ->stateless()
        ->with(['prompt' => 'select_account'])
        ->redirectUrl(route('GoogleCallback'))
        ->redirect();
    }
    public function redirectToFaceBook()
    {
        return Socialite::driver('facebook')
        ->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')
        ->stateless()
        ->user();//bỏ qua được, Intelephense đoán kiểu trả về là Laravel\Socialite\Contracts\Provider, tức là interface: nên sẽ báo lỗi stateless(), nhưng PHP không cần biết kiểu trả về tại thời điểm biên dịch như Java/C#. Miễn là object thực sự có method stateless() thì PHP vẫn chạy tốt.
        //dd($googleUser);
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'username' => $googleUser->getEmail(),
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(Str::random(16)), // giả lập mật khẩu
                'google_id' => $googleUser->getId(),
            ]);
        }

        // Auth::login($user);

        // return redirect('/');
        $token = $user->createToken('google-auth')->plainTextToken;
        return view('callback', [
            'token' => $token,
            'user' => $user,
            'status' => 'success'
        ]);
    }

    public function handleFaceBookCallback()
    {
         try {
            $faceBookUser = Socialite::driver('facebook')->stateless()->user();//bỏ qua được, Intelephense đoán kiểu trả về là Laravel\Socialite\Contracts\Provider, tức là interface: nên sẽ báo lỗi stateless(), nhưng PHP không cần biết kiểu trả về tại thời điểm biên dịch như Java/C#. Miễn là object thực sự có method stateless() thì PHP vẫn chạy tốt.

            $user = User::where('email', $faceBookUser->getEmail())->first();

            //dd($faceBookUser, $user);

            if (!$user) {
                $user = User::create([
                    'username' => $faceBookUser->getName() . '_' . $faceBookUser->getId(),

                    'email' => $faceBookUser->getEmail(),
                    'password' => bcrypt(Str::random(16)), // giả lập mật khẩu
                    'facebook_id' => $faceBookUser->getId(),
                ]);
            }

            // Auth::login($user);

            // return redirect('/');
        $token = $user->createToken('facebook-auth')->plainTextToken;
        return view('callback', [
            'token' => $token,
            'user' => $user,
            'status' => 'success'
        ]);

        // Xử lý login ở đây
        // ...
        } catch (\Exception $e) {
        // Ghi log nếu cần debug
        Log::error('Facebook Login Error: ' . $e->getMessage());

        // Quay lại trang login với thông báo lỗi
        // return redirect('/login')->with('error', 'Đăng nhập Facebook thất bại hoặc bị huỷ.');
        return view('callback', [
            'status' => 'error',
            'message' => 'Đăng nhập Facebook thất bại hoặc bị huỷ.'
        ]);
        }
    }
}
