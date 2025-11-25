<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Users Configuration
    |--------------------------------------------------------------------------
    |
    | These values are used to create default users when seeding the database.
    |
    */

    'admin' => [
        'first_name' => env('ADMIN_FIRST_NAME', 'Admin'),
        'last_name' => env('ADMIN_LAST_NAME', 'User'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

    'user' => [
        'first_name' => env('USER_FIRST_NAME', 'Regular'),
        'last_name' => env('USER_LAST_NAME', 'User'),
        'email' => env('USER_EMAIL', 'user@example.com'),
        'password' => env('USER_PASSWORD', 'password'),
    ],

];
