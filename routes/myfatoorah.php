<?php

use Illuminate\Support\Facades\Route;

if (config('myfatoorah.enable_routes')) {
    Route::controller(MyFatoorahController::class)
        ->prefix('myfatoorah')
        ->name('myfatoorah.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('callback', 'callback')->name('callback');
        });
}
