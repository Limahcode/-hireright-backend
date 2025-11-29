<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use App\Exceptions\Handler;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ExceptionHandler::class, Handler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Gate::define('employer', function (User $user) {
            // Log::info("---- 'Can' called. User Role:   " . $user->app_role);
            if ($user->app_role !== 'employer') {
                throw new AuthorizationException('This endpoint is restricted to employer accounts only.');
            }
            return true;
        });
    
        Gate::define('candidate', function (User $user) {
            //Log::info("---- 'Can' called. User Role:   " . $user->app_role);
            if ($user->app_role !== 'candidate') {
                throw new AuthorizationException('This endpoint is restricted to candidate accounts only.');
            }
            return true;
        });

        Gate::define('admin', function (User $user) {
            //Log::info("---- 'Can' called. User Role:   " . $user->app_role);
            if ($user->app_role !== 'admin') {
                throw new AuthorizationException('This endpoint is restricted to admin accounts only.');
            }
            return true;
        });

    }
}
