<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Processing login...</title>
</head>
<body>
    <p>Please wait...</p>
    <script>
        const data = @json([
            'status' => $status,
            'message' => $message ?? '',
            'token' => $token ?? null,
            'user' => $user ?? null,
        ]);
        const targetOrigin = @json($frontendUrl ?? 'http://localhost:5173');

        if (window.opener) {
            window.opener.postMessage(data, targetOrigin);
        }

        window.close();
    </script>
</body>
</html>
