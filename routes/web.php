<?php

use Abdelhmed\SentinelAi\Http\Controllers\SentinelController;
use Illuminate\Support\Facades\Route;

// Registered by SentinelServiceProvider inside a group that adds the
// prefix, the middleware and the "sentinel." name prefix.

Route::get('/', [SentinelController::class, 'index'])->name('index');
Route::delete('/', [SentinelController::class, 'clear'])->name('clear');

Route::patch('/{log}/resolve', [SentinelController::class, 'resolve'])->whereNumber('log')->name('resolve');
Route::post('/{log}/analyze', [SentinelController::class, 'analyze'])->whereNumber('log')->name('analyze');
Route::post('/{log}/test', [SentinelController::class, 'test'])->whereNumber('log')->name('test');
Route::delete('/{log}', [SentinelController::class, 'destroy'])->whereNumber('log')->name('destroy');
