<?php

namespace LaravelPay\MyFatoorah\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LaravelPay\MyFatoorah\MyFatoorahApi
 */
class Payment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \LaravelPay\MyFatoorah\Payment::class;
    }
}
