<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
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
            ->fields(['id', 'name', 'email', 'picture.type(large)'])
            ->with(['prompt' => 'select_account'])
            ->redirectUrl(route('api.facebook.callback'))
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                    'username' => 'google_'.$googleUser->getId(),
                    'email' => $googleUser->getEmail(),
                    'profile_name' => $googleUser->getName() ?? $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt(Str::random(16)),
                ]);
            } else {
                $user->forceFill([
                    'google_id' => $user->google_id ?: $googleUser->getId(),
                    'avatar' => $user->avatar ?: $googleUser->getAvatar(),
                ])->save();
            }

            return $this->oauthCallbackView($user, 'google-auth');
        } catch (\Exception $e) {
            Log::error('Google login error: '.$e->getMessage());

            return view('callback', [
                'status' => 'error',
                'message' => $e->getMessage(),
                'frontendUrl' => config('app.frontend_url'),
            ]);
        }
    }

    public function handleFaceBookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')
                ->stateless()
                ->user();

            $defaultAvatar = 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y';
            $avatar = $facebookUser->getAvatar() ?: $defaultAvatar;

            $user = User::where('email', $facebookUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                    'username' => 'facebook_'.$facebookUser->getId(),
                    'email' => $facebookUser->getEmail(),
                    'profile_name' => $facebookUser->getName() ?? $facebookUser->getEmail(),
                    'facebook_id' => $facebookUser->getId(),
                    'avatar' => $avatar,
                    'password' => bcrypt(Str::random(16)),
                ]);
            } else {
                $user->forceFill([
                    'facebook_id' => $user->facebook_id ?: $facebookUser->getId(),
                    'avatar' => $user->avatar ?: $avatar,
                ])->save();
            }

            return $this->oauthCallbackView($user, 'facebook-auth');
        } catch (\Exception $e) {
            Log::error('Facebook login error: '.$e->getMessage());

            return view('callback', [
                'status' => 'error',
                'message' => $e->getMessage(),
                'frontendUrl' => config('app.frontend_url'),
            ]);
        }
    }

    protected function oauthCallbackView(User $user, string $tokenName)
    {
        $token = $user->createToken($tokenName)->plainTextToken;

        return view('callback', [
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->profile_name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
            'frontendUrl' => config('app.frontend_url'),
        ]);
    }
}
