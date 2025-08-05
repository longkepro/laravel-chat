<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  @vite(['resources/css/app.css']) <!-- nếu dùng Vite -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100 font-sans">
  <div class="flex w-11/12 max-w-5xl h-[600px] bg-white rounded-lg shadow-lg overflow-hidden">

    <!-- Ảnh bên trái -->
    <div class="w-1/2 bg-cover bg-center" style="background-image: url('/background-pictures/pexels-furkanelveren-27390908.jpg');"></div>

    <!-- Form bên phải -->
    <form method="POST" action="/register" class="w-1/2 p-10 flex flex-col justify-center items-center bg-white bg-opacity-90 space-y-4">
      @csrf <!-- Laravel CSRF token -->
      <x-form-title>Sign up</x-form-title>

      <div class="w-4/5">
        <label for="userName" class="block text-sm font-medium text-gray-700">
          <i class="fas fa-user mr-1"></i> Username:
        </label>
        <x-form-input type="text" id="userName" name="userName" />
        <x-form-error name="userName" />
      </div>

      <div class="w-4/5 space-y-1">
        <label for="email" class="block text-sm font-medium text-gray-700">
          <i class="fas fa-envelope mr-1"></i> Email:
        </label>
        <x-form-input type="email" id="email" name="email" />
        <x-form-error name="email" />
      </div>

      <div class="w-4/5 space-y-1">
        <label for="password" class="block text-sm font-medium text-gray-700">
          <i class="fas fa-lock mr-1"></i> Password:
        </label>
        <x-form-input type="password" id="password" name="password" />
        <x-form-error name="password" />
      </div>

      <div class="w-4/5 space-y-1">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
          <i class="fas fa-lock mr-1"></i> Re-enter Password:
        </label>
        <x-form-input type="password" id="password_confirmation" name="password_confirmation" />
        <x-form-error name="password_confirmation" />
      </div>

      <x-form-button class="mt-4 mb-2"> Sign up</x-form-button>

      <x-social-auth-button url="{{ url('/login/google')}}" logo="logos/google-logo-png-google-icon-logo-png-transparent-svg-vector-bie-supply-14.png"/>

      <div class="text-sm mt-4 text-gray-700">
        Already have an account? <a href="/login" class="text-blue-500 hover:underline"> Log in</a>
      </div>
    </form>
  </div>
</body>
</html>
