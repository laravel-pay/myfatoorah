<?php

namespace LaravelPay\MyFatoorah\Common\Traits;

use Exception;

trait HasCurrencyRates
{
    /**
     * @throws Exception
     */
    public function getCurrencyRate(string $currency): float
    {
        $json = $this->getCurrencyRates();
        foreach ($json as $value) {
            if ($value->Text == $currency) {
                return $value->Value;
            }
        }
        throw new Exception('The selected currency is not supported by MyFatoorah');
    }

    /**
     * Get list of MyFatoorah currency rates
     *
     *
     * @throws Exception
     */
    public function getCurrencyRates(): object
    {
        $url = "$this->apiURL/v2/GetCurrenciesExchangeList";

        return $this->callAPI($url, null, null, 'Get Currencies Exchange List');
    }
}
