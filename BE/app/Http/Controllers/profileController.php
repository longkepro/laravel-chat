<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class profileController extends Controller
{
    //chỉnh sửa hồ sơ người dùng
    public function editProfile(Request $request)
    {
        $request->validate([
            'avatar' => 'sometimes|nullable|url|max:255|required_without:profile_name',
            'profile_name' => 'sometimes|nullable|string|max:100|required_without:avatar',
        ]);

        $user = User::Auth()->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->avatar = $request->input('avatar', $user->avatar);
        $user->profile_name = $request->input('profile_name', $user->profile_name);
        $user->save();

        return response()->json(['status' => 'Avatar updated!', 'avatar' => $user->avatar]);
    }

    //lấy hồ sơ người dùng
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

    //cập nhật mật khẩu
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

    //tìm kiếm người dùng theo username hoặc profile_name
    public function searchUsers(Request $request)
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
