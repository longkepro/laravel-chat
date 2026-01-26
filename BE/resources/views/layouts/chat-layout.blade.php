<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat UI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/chat.js'])
    <!--nên import file js trước dòng sử dụng hay nạp biến-->
</head>
<body class="bg-gray-100 container-fluid h-full">
    <script>
        window.Laravel = {
            userId: {{ auth()->id() }}
        }
    </script>
    <div id="notification" class="fixed top-5 right-5 max-w-sm px-4 py-3 rounded-2xl shadow-lg border
            bg-green-500 text-white text-sm font-medium
            transition-all duration-500 ease-in-out invisible opacity-0">
    </div>
    {{$slot}}

</body>
</html>


