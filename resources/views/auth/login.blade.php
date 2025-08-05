<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login</title>
    @vite('resources/css/app.css') {{-- Nếu dùng Vite --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="flex w-11/12 max-w-5xl h-[600px] bg-white rounded-lg shadow-lg overflow-hidden">
        
        {{-- Left image --}}
        <div class="w-1/2 bg-cover bg-top" style="background-image: url('{{ asset('background-pictures/pexels-furkanelveren-27390908.jpg') }}')">
        </div>

        {{-- Right login form --}}
        <form method="POST" action="/login" class="w-1/2 p-10 flex flex-col justify-center items-center bg-white/90 space-y-6">
            @csrf
            <x-form-title for='title'> Sign In </x-form-title>

            <div class="w-4/5 space-y-1">
                <label for="userName" class="text-sm font-medium text-gray-700">
                    <i class="fas fa-user mr-2"></i>User name:
                </label>
                <x-form-input id="userName" name="userName" type="text"  />
                <x-form-error name="userName" />
            </div>

            <div class="w-4/5 space-y-1">
                <label for="password" class="text-sm font-medium text-gray-700">
                    <i class="fas fa-lock mr-2"></i>Password:
                </label>
                <x-form-input id="password" name="password" type="password" /> 
                <x-form-error name="password" /> 
            </div>

            <x-form-button>Log In</x-form-button>
            <div class="flex space-y-3 gap-4">
                <x-social-auth-button url="{{ url('/login/google')}}" logo="logos/google-logo-png-google-icon-logo-png-transparent-svg-vector-bie-supply-14.png"></x-social-auth-button>

                <x-social-auth-button url="{{ url('/login/facebook')}}" logo="logos/Facebook_Logo_2023.png"></x-social-auth-button>
            </div>
            <p class="text-sm mt-4">
                Don't have an account yet? <a href="/register" class="text-blue-500 hover:underline">Sign Up</a>
            </p>
        </form>
    </div>
</body>
</html>
