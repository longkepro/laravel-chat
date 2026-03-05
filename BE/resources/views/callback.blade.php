<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Đang xử lý đăng nhập...</title>
</head>
<body>
    <p>Vui lòng chờ trong giây lát...</p>
    <script>
        // Lấy dữ liệu từ Controller
        const data = {
            status: "{{ $status }}",
            message: "{{ $message ?? '' }}"
        };

        // Gửi data về cửa sổ cha (Trang Login Vuejs)
        // '*' cho phép gửi đến mọi domain thay bằng 'http://localhost:5173' để bảo mật hơn, domain đóng vai trò là khóa xác thực
        console.log('aaaaaaa');
        if (window.opener) {
            window.opener.postMessage(data, 'http://localhost:5173')// gửi message về cho fe
        }

        // Đóng popup này lại
        window.close();
    </script>
</body>
</html>

