<?php
namespace Abdelhmed\SentinelAi;

use Illuminate\Foundation\Exceptions\Handler;
use Throwable;
use Illuminate\Support\ServiceProvider;
use Abdelhmed\SentinelAi\Services\ErrorAnalyzer;
use Abdelhmed\SentinelAi\Models\SentinelLog;
use Illuminate\Support\Facades\Route;

class SentinelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sentinel.php', 'sentinel'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/sentinel.php' => config_path('sentinel.php'),
            ], 'sentinel-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'sentinel-migrations');
        }

        $this->app[Handler::class]->reportable(function (Throwable $e) {
            (new ErrorAnalyzer())->handle($e);
        });

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sentinel');

        Route::get('sentinel/logs', function () {
            $logs = SentinelLog::latest()->paginate(10);
            return view('sentinel::index', compact('logs'));
        });
        Route::delete('sentinel/logs', function () {
            SentinelLog::truncate();
            return redirect()->back();
        });
    }
}