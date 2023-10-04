# This is my package myfatoorah

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-pay/myfatoorah.svg?style=flat-square)](https://packagist.org/packages/laravel-pay/myfatoorah)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-pay/myfatoorah.svg?style=flat-square)](https://packagist.org/packages/laravel-pay/myfatoorah)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require laravel-pay/myfatoorah "dev-master"
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="myfatoorah-config"
```

This is the contents of the published config file:

```php
return [
    /**
     * API Token Key
     * Live Token: https://myfatoorah.readme.io/docs/live-token
     * Test Token: https://myfatoorah.readme.io/docs/test-token
     */
    'api_key' => env('MYFATOORAH_API_KEY'),
    /**
     * Test Mode
     * Accepted value: 'true' for the test mode or 'false' for the live mode
     */
    'test_mode' => env('MYFATOORAH_TEST_MODE', true),
    /**
     * Country ISO Code
     * Accepted value: KWT, SAU, ARE, QAT, BHR, OMN, JOD, or EGY.
     */
    'country_iso' => env('MYFATOORAH_COUNTRY_ISO', 'KWT'),

    'enable_routes' => true,
];

```

You can use this api key for test : 
```dotenv
MYFATOORAH_API_KEY='rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL'
```

And use Test Cards : 
```text
https://docs.myfatoorah.com/docs/test-cards
```

## Usage

If You enabled routes (Only For Local Testing) :
```php
'enable_routes' => true,
```

then you have two routes 
```
localhost/myfatoorah
localhost/myfatoorah/callback
```

Or You can Disable them and create your own routes :
```php
Route::get('pay/myfatoorah' , [MyFatoorahController::class , 'index'])->name('pay.myfatoorah.index')
Route::get('pay/myfatoorah/callback' , [MyFatoorahController::class , 'callback'])->name('pay.myfatoorah.callback')
```
```php
<?php

use Illuminate\Http\Response;
use LaravelPay\MyFatoorah\Facades\Payment;

class MyFatoorahController
{
    public function index()
    {
        try {
            $paymentMethodId = 0; // 0 for MyFatoorah invoice or 1 for Knet in test mode
            $callbackURL = route('pay.myfatoorah.callback');
            $orderId = 12345;
            $data = Payment::getInvoiceURL(
                curlData : [
                    'CustomerName' => 'FName LName',
                    'InvoiceValue' => '10',
                    'DisplayCurrencyIso' => 'KWD',
                    'CustomerEmail' => 'test@test.com',
                    'CallBackUrl' => $callbackURL,
                    'ErrorUrl' => $callbackURL,
                    'MobileCountryCode' => '+965',
                    'CustomerMobile' => '12345678',
                    'Language' => 'en',
                    'CustomerReference' => $orderId,
                    'SourceInfo' => 'Laravel '.app()::VERSION,
                ],
                gatewayId : $paymentMethodId
            );
    
            return response()->json(['IsSuccess' => 'true', 'Message' => 'Invoice created successfully.', 'Data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['IsSuccess' => 'false', 'Message' => $e->getMessage()]);
        }
    }
    
    public function callback()
    {
        try {
            $data = Payment::getPaymentStatus(request('paymentId'), 'PaymentId');
    
            if ($data->InvoiceStatus == 'Paid') {
                $msg = 'Invoice is paid.';
            } elseif ($data->InvoiceStatus == 'Failed') {
                $msg = 'Invoice is not paid due to '.$data->InvoiceError;
            } elseif ($data->InvoiceStatus == 'Expired') {
                $msg = 'Invoice is expired.';
            }
    
            return response()->json(['IsSuccess' => 'true', 'Message' => $msg, 'Data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['IsSuccess' => 'false', 'Message' => $e->getMessage()]);
        }
    }
}
```



## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Elsayed Kamal](https://github.com/laravel-pay)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
