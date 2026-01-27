<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class profileController extends Controller
{
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
    public function updatePassword(Request $request)
    {
        $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8',
            'confirmNewPassword' => 'required|string|same:newPassword',
        ]);

        $user = User::Auth()->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->password = Hash::make($request->input('newPassword'));
        $user->save();

        return response()->json(['status' => 'Password updated!']);
    }
    public function searchProfiles(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:100',
        ]);

        $query = $request->input('query');

        $users = User::where('username', 'LIKE', "%{$query}%")
                     ->orWhere('profile_name', 'LIKE', "%{$query}%")
                     ->get(['id', 'username', 'profile_name', 'avatar']);

        return response()->json(['results' => $users]);
    }
}
