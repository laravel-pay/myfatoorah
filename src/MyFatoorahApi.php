<?php

namespace LaravelPay\MyFatoorah;

use Exception;
use LaravelPay\MyFatoorah\Common\Contracts\CurrencyRates;
use LaravelPay\MyFatoorah\Common\Traits\HasApiError;
use LaravelPay\MyFatoorah\Common\Traits\HasCountries;
use LaravelPay\MyFatoorah\Common\Traits\HasCurrencyRates;
use LaravelPay\MyFatoorah\Common\Traits\HasLog;

class MyFatoorahApi implements CurrencyRates
{
    use HasApiError , HasCountries , HasCurrencyRates , HasLog;

    /**
     * The URL used to connect to MyFatoorah test/live API server
     */
    protected string $apiURL = '';

    /**
     * The API Token Key is the authentication which identify a user that is using the app
     * To generate one follow instruction here https://myfatoorah.readme.io/docs/live-token
     */
    protected string $apiKey;

    /**
     * Constructor
     * Initiate new MyFatoorah API process
     */
    public function __construct(string $apiKey, string $countryMode = 'KWT', bool $isTest = false, string|object $loggerObj = null, string $loggerFunc = null)
    {
        $mfCountries = $this->getMyFatoorahCountries();

        $code = strtoupper($countryMode);
        if (isset($mfCountries[$code])) {
            $this->apiURL = ($isTest) ? $mfCountries[$code]['testv2'] : $mfCountries[$code]['v2'];
        } else {
            $this->apiURL = ($isTest) ? 'https://apitest.myfatoorah.com' : 'https://api.myfatoorah.com';
        }

        $this->apiKey = trim($apiKey);
        $this->loggerObj = $loggerObj;
        $this->loggerFunc = $loggerFunc;
    }

    public function callAPI(string $url, $postFields = null, $orderId = null, $function = null): object
    {
        //to prevent json_encode adding lots of decimal digits
        ini_set('precision', 14);
        ini_set('serialize_precision', -1);

        $request = isset($postFields) ? 'POST' : 'GET';
        $fields = json_encode($postFields);

        $msgLog = "Order #$orderId ----- $function";

        if ($function != 'Direct Payment') {
            $this->log("$msgLog - Request: $fields");
        }

        //***************************************
        //call url
        //***************************************
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $request,
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $this->apiKey", 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $res = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        //example set a local ip to host apitest.myfatoorah.com
        if ($err) {
            $this->log("$msgLog - cURL Error: $err");
            throw new Exception($err);
        }

        $this->log("$msgLog - Response: $res");

        $json = json_decode($res);

        //***************************************
        //check for errors
        //***************************************

        $error = $this->getAPIError($json, $res);
        if ($error) {
            $this->log("$msgLog - Error: $error");
            throw new Exception($error);
        }

        //***************************************
        //Success
        //***************************************
        return $json;
    }

    public static function isSignatureValid($dataArray, $secret, $signature, $eventType = 0): bool
    {
        if ($eventType == 2) {
            unset($dataArray['GatewayReference']);
        }

        uksort($dataArray, 'strcasecmp');

        $output = implode(',', array_map(
            function ($v, $k) {
                return sprintf('%s=%s', $k, $v);
            },
            $dataArray,
            array_keys($dataArray)
        ));

        // generate hash of $field string
        $hash = base64_encode(hash_hmac('sha256', $output, $secret, true));

        if ($signature === $hash) {
            return true;
        } else {
            return false;
        }
    }

    protected function calcGatewayData($totalAmount, $currency, $paymentCurrencyIso, $allRatesData): array
    {
        foreach ($allRatesData as $data) {
            if ($data->Text == $currency) {
                $baseCurrencyRate = $data->Value;
            }
            if ($data->Text == $paymentCurrencyIso) {
                $gatewayCurrencyRate = $data->Value;
            }
        }

        if (isset($baseCurrencyRate) && isset($gatewayCurrencyRate)) {

            $baseAmount = ceil(((int) ($totalAmount * 1000)) / $baseCurrencyRate / 10) / 100;

            return [
                'GatewayTotalAmount' => round(($baseAmount * $gatewayCurrencyRate), 3),
                'GatewayCurrency' => $paymentCurrencyIso,
            ];
        } else {
            return [
                'GatewayTotalAmount' => $totalAmount,
                'GatewayCurrency' => $currency,
            ];
        }
    }
}
