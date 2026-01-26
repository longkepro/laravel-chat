<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class profileController extends Controller
{
    public function getSelfProfile()
    {
        $user = User::Auth()->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'avatar' => $user->avatar,
        ]);
    }
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|url|max:255',
        ]);

        $user = User::Auth()->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->avatar = $request->input('avatar');
        $user->save();

        return response()->json(['status' => 'Avatar updated!', 'avatar' => $user->avatar]);
    }
    public function updateName(Request $request)
    {
        $request->validate([
            'profileName' => 'required|string|max:100',
        ]);

        $user = User::Auth()->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->profile_name = $request->input('profileName');
        $user->save();

        return response()->json(['status' => 'Name updated!', 'profileName' => $user->profile_name]);
    }
    public function getProfile($userID)
    {
        $user = User::find($userID);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'profileName' => $user->profile_name,
            'avatar' => $user->avatar,
        ]);
    }
}
