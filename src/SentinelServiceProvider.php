<?php

namespace Abdelhmed\SentinelAi;

use Abdelhmed\SentinelAi\Http\Middleware\Authorize;
use Abdelhmed\SentinelAi\Services\ErrorCapture;
use Abdelhmed\SentinelAi\Services\Redactor;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Throwable;

class SentinelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sentinel.php', 'sentinel');

        $this->app->singleton(Redactor::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sentinel');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/sentinel.php' => config_path('sentinel.php'),
            ], 'sentinel-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/sentinel'),
            ], 'sentinel-views');
        }

        if (! config('sentinel.enabled')) {
            return;
        }

        $this->registerRoutes();
        $this->registerReporter();
    }

    protected function registerRoutes(): void
    {
        // Loaded from a file (not closures) so `php artisan route:cache` works.
        Route::group([
            'prefix'     => config('sentinel.path', 'sentinel'),
            'as'         => 'sentinel.',
            'middleware' => array_merge((array) config('sentinel.middleware', ['web']), [Authorize::class]),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    protected function registerReporter(): void
    {
        $handler = $this->app->make(ExceptionHandler::class);

        // A fully custom handler is not supported: we only hook into Laravel's own.
        if (! $handler instanceof Handler) {
            return;
        }

        $handler->reportable(function (Throwable $e) {
            try {
                $this->app->make(ErrorCapture::class)->capture($e);
            } catch (Throwable $inner) {
                // Monitoring must never break the application.
                Log::warning('Sentinel AI could not capture an exception: ' . $inner->getMessage());
            }
            // Returning nothing keeps Laravel's normal logging.
        });
    }
}
