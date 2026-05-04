<?php

return [
    // Align Cloudinary-Laravel config with the env variables used in this repo.
    // The package expects CLOUDINARY_URL or (CLOUDINARY_KEY/CLOUDINARY_SECRET/CLOUDINARY_CLOUD_NAME).
    // We keep using CLOUDINARY_API_KEY / CLOUDINARY_API_SECRET from .env.
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    'cloud_url' => env(
        'CLOUDINARY_URL',
        'cloudinary://' . env('CLOUDINARY_API_KEY') . ':' . env('CLOUDINARY_API_SECRET') . '@' . env('CLOUDINARY_CLOUD_NAME')
    ),

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
    'upload_route' => env('CLOUDINARY_UPLOAD_ROUTE'),
    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),
];
