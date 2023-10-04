<?php

namespace LaravelPay\MyFatoorah;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MyFatoorahServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('myfatoorah')
            ->hasRoute('myfatoorah')
            ->hasConfigFile('myfatoorah');
    }

    public function boot(): void
    {
        parent::boot();
        $this->app->singleton(Payment::class, function ($app) {
            return new Payment(
                apiKey: config('myfatoorah.api_key'),
                countryMode: config('myfatoorah.country_iso'),
                isTest: config('myfatoorah.test_mode'),
            );
        });
    }
}
