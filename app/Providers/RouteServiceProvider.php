<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
  /**
   * The path to the "home" route for your application.
   *
   * This is used by Laravel authentication to redirect users after login.
   */
  public const HOME = '/dashboard';

  /**
   * Define your route model bindings, pattern filters, and other route configuration.
   */
  public function boot(): void
  {
    $this->configureRateLimiting();

    $this->routes(function () {
      Route::middleware('api')
        ->prefix('api')
        ->group(base_path('routes/api.php'));

      Route::middleware('web')
        ->group(base_path('routes/web.php'));

      // Register the student routes
      Route::middleware('web')
        ->group(base_path('routes/student.php'));
    });
  }

  /**
   * Configure the rate limiters for the application.
   */
  protected function configureRateLimiting(): void
  {
    RateLimiter::for('api', function (Request $request) {
      $user = $request->user();

      return [
        Limit::perMinute(60)->by($user?->id),
        Limit::perMinute(10)->by($request->ip())->response(function () {
          throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => ['Too many requests. Please slow down.'],
          ]);
        }),
      ];
    });
  }
}
