<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // Cần để lưu ảnh avatar

class ProfileController extends Controller
{
    /**
     * 1. Lấy thông tin chính mình (Auth User)
     * API: GET /api/profile/authUser
     */
    public function getAuthUser(Request $request)
    {
        // Lấy user từ Session/Cookie đã xác thực
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->profile_name ?? $user->name, // Ưu tiên profile_name
            'email' => $user->email,
            'avatar' => $user->avatar,
            'role' => $user->role ?? 'user', // Ví dụ thêm role
        ]);
    }

    /**
     * 2. Lấy thông tin người khác (Public Profile)
     * API: GET /api/profile/{userID}
     */
    public function getProfile($userID)
    {
        $user = User::findOrFail($userID); // Tự động trả về 404 nếu không tìm thấy

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->profile_name ?? $user->name,
            'avatar' => $user->avatar,
            // KHÔNG trả về email hay thông tin nhạy cảm của người khác
        ]);
    }

    /**
     * 3. Chỉnh sửa hồ sơ (Hỗ trợ Upload Ảnh)
     * API: POST /api/profile/edit-profile
     */
    public function editProfile(Request $request)
    {
        $user = $request->user();

        // Validate
        $request->validate([
            // Cho phép avatar là File ảnh (jpeg, png...) HOẶC là chuỗi String (nếu giữ nguyên link cũ)
            'avatar' => 'nullable',
            'profile_name' => 'nullable|string|max:100',
        ]);

        // Cập nhật tên hiển thị
        if ($request->has('profile_name')) {
            $user->profile_name = $request->input('profile_name');
        }

        // Xử lý Upload Avatar
        if ($request->hasFile('avatar')) {
            // 1. Xóa ảnh cũ nếu không phải ảnh mặc định (Optional)
            // if ($user->avatar && Storage::exists($user->avatar)) { ... }

            // 2. Lưu ảnh mới vào thư mục 'avatars' trong storage/app/public
            $path = $request->file('avatar')->store('avatars', 'public');

            // 3. Tạo đường dẫn URL (ví dụ: /storage/avatars/abc.jpg)
            $user->avatar = '/storage/' . $path;
        }

        $user->save();

        return response()->json([
            'status' => 'Profile updated successfully!',
            'user' => $user // Trả về user mới để Frontend cập nhật Store
        ]);
    }

    /**
     * 4. Cập nhật mật khẩu (Bảo mật cao)
     * API: POST /api/profile/update-password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed', // dùng confirmed để check field 'new_password_confirmation'
        ]);

        $user = $request->user();

        // [QUAN TRỌNG]: Kiểm tra mật khẩu cũ có đúng không
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['error' => 'Mật khẩu hiện tại không đúng.'], 400); // Bad Request
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return response()->json(['status' => 'Đổi mật khẩu thành công!']);
    }

    /**
     * 5. Tìm kiếm người dùng
     * API: GET /api/search/users?query=abc
     */
    public function searchUsers(Request $request)
    {
        $request->validate([
            'q' => 'required|string|max:100', // Axios gửi params: { q: ... }
        ]);

        $query = $request->input('q');

        // Tìm user theo username hoặc tên hiển thị
        $users = User::where('username', 'LIKE', "%{$query}%")
                     ->orWhere('profile_name', 'LIKE', "%{$query}%")
                     ->select('id', 'username', 'profile_name', 'avatar') // Chỉ lấy cột cần thiết
                     ->limit(20) // Giới hạn kết quả để không nặng server
                     ->get();

        // Map lại dữ liệu để chuẩn hóa output
        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->profile_name ?? $user->username,
                'avatar' => $user->avatar,
            ];
        });

        // Trả về mảng results (đúng format interface frontend)
        return response()->json(['results' => $results]);
    }
}
