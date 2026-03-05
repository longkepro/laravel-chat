<?php

// namespace App\Http\Controllers\Auth;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

// use Laravel\Socialite\Facades\Socialite;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Str;
// use App\Models\User;
// use Illuminate\Support\Facades\Log;
// use PHPUnit\TextUI\Configuration\Php;

// class SocialAuthController extends Controller
// {
//     public function redirectToGoogle()
//     {
//         return Socialite::driver('google')
//         ->stateless()
//         ->with(['prompt' => 'select_account'])
//         ->redirectUrl(route('GoogleCallback'))
//         ->redirect();
//     }
//     public function redirectToFaceBook()
//     {
//         return Socialite::driver('facebook')
//         ->redirect();
//     }

//     public function handleGoogleCallback()
// {
//     try {
//         // 1. Lấy user từ Google (Có thể văng lỗi ở đây nếu user hủy hoặc token sai)
//         $googleUser = Socialite::driver('google')->stateless()->user();

//         $user = User::where('email', $googleUser->getEmail())->first();

//         if (!$user) {
//             $user = User::create([
//                 'username' => $googleUser->getEmail(), // Hoặc xử lý name logic
//                 'email' => $googleUser->getEmail(),
//                 'password' => bcrypt(Str::random(16)),
//                 'google_id' => $googleUser->getId(),
//             ]);
//         }

//         $token = $user->createToken('google-auth')->plainTextToken;

//         // 2. Trả về View SUCCESS
//         return view('callback', [
//             'status' => 'success',
//             'token' => $token,
//             'user' => $user,
//         ]);

//     } catch (\Exception $e) {
//         // 3. Log lỗi để dev kiểm tra
//         Log::error('Google Login Error: ' . $e->getMessage());

//         // 4. Trả về View ERROR
//         return view('callback', [
//             'status' => 'error',
//             'message' => 'Đăng nhập Google thất bại hoặc bị hủy.',
//         ]);
//     }
// }

//     public function handleFaceBookCallback()
//     {
//          try {
//             $faceBookUser = Socialite::driver('facebook')->stateless()->user();//bỏ qua được, Intelephense đoán kiểu trả về là Laravel\Socialite\Contracts\Provider, tức là interface: nên sẽ báo lỗi stateless(), nhưng PHP không cần biết kiểu trả về tại thời điểm biên dịch như Java/C#. Miễn là object thực sự có method stateless() thì PHP vẫn chạy tốt.

//             $user = User::where('email', $faceBookUser->getEmail())->first();

//             //dd($faceBookUser, $user);

//             if (!$user) {
//                 $user = User::create([
//                     'username' => $faceBookUser->getName() . '_' . $faceBookUser->getId(),

//                     'email' => $faceBookUser->getEmail(),
//                     'password' => bcrypt(Str::random(16)), // giả lập mật khẩu
//                     'facebook_id' => $faceBookUser->getId(),
//                 ]);
//             }

//             // Auth::login($user);

//             // return redirect('/');
//         $token = $user->createToken('facebook-auth')->plainTextToken;
//         return view('callback', [
//             'token' => $token,
//             'user' => $user,
//             'status' => 'success'
//         ]);

//         // Xử lý login ở đây
//         // ...
//         } catch (\Exception $e) {
//         // Ghi log nếu cần debug
//         Log::error('Facebook Login Error: ' . $e->getMessage());

//         // Quay lại trang login với thông báo lỗi
//         // return redirect('/login')->with('error', 'Đăng nhập Facebook thất bại hoặc bị huỷ.');
//         return view('callback', [
//             'status' => 'error',
//             'message' => 'Đăng nhập Facebook thất bại hoặc bị huỷ.'
//         ]);
//         }
//     }
// }

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    // ... (Các hàm redirect giữ nguyên) ...
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->with(['prompt' => 'select_account'])
            ->redirectUrl(route('api.google.callback'))
            ->redirect();
    }

    public function redirectToFaceBook()
    {
         return Socialite::driver('facebook')
            ->stateless()
            ->with(['prompt' => 'select_account'])
            ->redirectUrl(route('api.facebook.callback'))
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')
            ->stateless()
            ->user();//bỏ qua được, Intelephense đoán kiểu trả về là Laravel\Socialite\Contracts\Provider, tức là interface: nên sẽ báo lỗi stateless(), nhưng PHP không cần biết kiểu trả về tại thời điểm biên dịch như Java/C#. Miễn là object thực sự có method stateless() thì PHP vẫn chạy tốt.
            //dd($googleUser);
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'username'     => 'google_' . $googleUser->getId(),
                    'email'        => $googleUser->getEmail(),
                    'profile_name' => $googleUser->getName() ?? $googleUser->getEmail(),
                    'google_id'    => $googleUser->getId(),
                    'avatar'       => $googleUser->getAvatar(),
                    'password'     => bcrypt(Str::random(16)),
                ]);
            }

            Auth::login($user); // Bỏ 'true' vì bảng users không có column remember_token
            request()->session()->regenerate(); // Chống Session Fixation

            return view('callback', [
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('Google Login Error: ' . $e->getMessage());

            return view('callback', [
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function handleFaceBookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')
            ->stateless()
            ->user();//bỏ qua được, Intelephense đoán kiểu trả về là Laravel\Socialite\Contracts\Provider, tức là interface: nên sẽ báo lỗi stateless(), nhưng PHP không cần biết kiểu trả về tại thời điểm biên dịch như Java/C#. Miễn là object thực sự có method stateless() thì PHP vẫn chạy tốt.
            //dd($googleUser);
            $user = User::where('email', $facebookUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'username'     => 'facebook_' . $facebookUser->getId(),
                    'email'        => $facebookUser->getEmail(),
                    'profile_name' => $facebookUser->getName() ?? $facebookUser->getEmail(),
                    'facebook_id'  => $facebookUser->getId(),
                    'avatar'       => $facebookUser->getAvatar(),
                    'password'     => bcrypt(Str::random(16)),
                ]);
            }

            Auth::login($user); // Bỏ 'true' vì bảng users không có column remember_token
            request()->session()->regenerate(); // Chống Session Fixation

            return view('callback', [
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('Google Login Error: ' . $e->getMessage());

            return view('callback', [
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

}

