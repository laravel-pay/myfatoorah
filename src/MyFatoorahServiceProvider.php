<?php

namespace LaravelPay\MyFatoorah;

use LaravelPay\MyFatoorah\Facades\Payment as PaymentFacade;
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

    public function boot()
    {
        $this->app->singleton(PaymentFacade::class, function ($app) {
            return new Payment(
                apiKey: ('myfatoorah.api_key'),
                countryMode: ('myfatoorah.country_mode'),
                isTest: ('myfatoorah.is_test'),
            );
        });
    }
}
