<?php

use Illuminate\Support\Facades\Route;
use LaravelPay\MyFatoorah\Controllers\MyFatoorahController;

if (config('myfatoorah.enable_routes') && app()->environment(['local'])) {
    Route::controller(MyFatoorahController::class)
        ->prefix('myfatoorah')
        ->name('myfatoorah.')
        ->group(function () {
            Route::get('', 'index')->name('index');
            Route::get('callback', 'callback')->name('callback');
        });
}
