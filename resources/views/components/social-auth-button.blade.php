@props(['url','logo'])

<a href="{{ $url }}" {{ $attributes->merge(['class' => 'flex items-center px-4 py-2 rounded-lg shadow-sm bg-white hover:bg-gray-100']) }}>
                <img src='{{asset($logo)}}' alt="Logo" class="h-5 w-5 mr-3">
</a>