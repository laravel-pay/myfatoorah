<?php

namespace LaravelPay\MyFatoorah\Common\Contracts;

interface CurrencyRates
{
    /**
     * Get list of MyFatoorah currency rates
     */
    public function getCurrencyRates(): object;

    /**
     * Get a currency rate
     */
    public function getCurrencyRate(string $currency): float;

    public function callAPI(string $url, $postFields = null, $orderId = null, $function = null): object;
}
