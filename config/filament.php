<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Filament Path
    |--------------------------------------------------------------------------
    |
    | The default is `admin` but you can change it to whatever works best and
    | doesn't conflict with the routing in your application.
    |
    */

  'path' => env('FILAMENT_PATH', 'admin'),

  /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    |
    | This is the configuration for the authentication guard that will be used by
    | Filament. The authentication guard needs to be listed in your auth.php
    |
    */

  'auth' => [
    'guard' => env('FILAMENT_AUTH_GUARD', 'web'),
  ],

  /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | You may customise the middleware that is applied to all Filament requests.
    |
    */

  'middleware' => [
    'auth' => [
      \Filament\Http\Middleware\Authenticate::class,
    ],
    'base' => [
      \Illuminate\Cookie\Middleware\EncryptCookies::class,
      \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
      \Illuminate\Session\Middleware\StartSession::class,
      \Illuminate\Session\Middleware\AuthenticateSession::class,
      \Illuminate\View\Middleware\ShareErrorsFromSession::class,
      \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
      \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
  ],
];
