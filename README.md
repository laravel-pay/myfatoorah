# This is my package myfatoorah

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-pay/myfatoorah.svg?style=flat-square)](https://packagist.org/packages/laravel-pay/myfatoorah)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-pay/myfatoorah.svg?style=flat-square)](https://packagist.org/packages/laravel-pay/myfatoorah)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require laravel-pay/myfatoorah
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="myfatoorah-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="myfatoorah-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="myfatoorah-views"
```

## Usage

```php
$myFatoorah = new LaravelPay\MyFatoorah();
echo $myFatoorah->echoPhrase('Hello, LaravelPay!');
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
