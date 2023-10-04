<?php

namespace LaravelPay\MyFatoorah;

use Exception;
use LaravelPay\MyFatoorah\Common\Traits\HasPaymentHelpers;
use LaravelPay\MyFatoorah\Common\Traits\HasUserAgent;

class Payment extends MyFatoorahApi
{
    use HasPaymentHelpers , HasUserAgent;

    /**
     * To specify either the payment will be onsite or offsite
     * (default value: false)
     */
    protected bool $isDirectPayment = false;

    public static string $pmCachedFile = __DIR__.'/../resources/mf-methods.json';

    protected static array $paymentMethods;

    /**
     * List available Payment Gateways. (POST API)
     */
    public function getVendorGateways(float|int $invoiceValue = 0, string $displayCurrencyIso = '', bool $isCached = false): array
    {
        $postFields = [
            'InvoiceAmount' => $invoiceValue,
            'CurrencyIso' => $displayCurrencyIso,
        ];

        $json = $this->callAPI("$this->apiURL/v2/InitiatePayment", $postFields, null, 'Initiate Payment');

        $paymentMethods = $json->Data->PaymentMethods ?? [];

        if (! empty($paymentMethods) && $isCached) {
            file_put_contents(self::$pmCachedFile, json_encode($paymentMethods));
        }

        return $paymentMethods;
    }

    /**
     * List available Cached Payment Gateways.
     *
     * @return array of Cached payment methods
     */
    public function getCachedVendorGateways(): array
    {
        if (file_exists(self::$pmCachedFile)) {
            $cache = file_get_contents(self::$pmCachedFile);

            return ($cache) ? json_decode($cache) : [];
        } else {
            return $this->getVendorGateways(0, '', true);
        }
    }

    /**
     * List available Payment Gateways by type (direct, cards)
     */
    public function getVendorGatewaysByType(bool $isDirect = false): array
    {
        $gateways = $this->getCachedVendorGateways();

        $paymentMethods = [
            'cards' => [],
            'direct' => [],
        ];

        foreach ($gateways as $g) {
            if ($g->IsDirectPayment) {
                $paymentMethods['direct'][] = $g;
            } else {
                if ($g->PaymentMethodCode != 'ap') {
                    $paymentMethods['cards'][] = $g;
                } else {
                    if ($this->isAppleSystem()) {
                        //add apple payment for IOS systems
                        $paymentMethods['cards'][] = $g;
                    }
                }
            }
        }

        return ($isDirect) ? $paymentMethods['direct'] : $paymentMethods['cards'];
    }

    /**
     * List available cached  Payment Methods
     */
    public function getCachedPaymentMethods(): array
    {
        $gateways = $this->getCachedVendorGateways();
        $paymentMethods = ['all' => [], 'cards' => [], 'form' => []];
        foreach ($gateways as $g) {
            $paymentMethods = $this->fillPaymentMethodsArray($g, $paymentMethods);
        }

        return $paymentMethods;
    }

    /**
     * List available Payment Methods
     *
     *
     * @throws Exception
     */
    public function getPaymentMethodsForDisplay(float|int $invoiceValue, string $displayCurrencyIso): array
    {
        if (! empty(self::$paymentMethods)) {
            return self::$paymentMethods;
        }

        $gateways = $this->getVendorGateways($invoiceValue, $displayCurrencyIso);
        $allRates = $this->getCurrencyRates();

        self::$paymentMethods = ['all' => [], 'cards' => [], 'form' => []];

        foreach ($gateways as $g) {
            $g->GatewayData = $this->calcGatewayData($g->TotalAmount, $g->CurrencyIso, $g->PaymentCurrencyIso,
                $allRates);

            self::$paymentMethods = $this->fillPaymentMethodsArray($g, self::$paymentMethods);
        }

        return self::$paymentMethods;
    }

    /**
     * Get Payment Method Object
     *
     * @param  string  $gatewayType  ['PaymentMethodId', 'PaymentMethodCode']
     *
     * @throws Exception
     */
    public function getPaymentMethod(
        string $gateway,
        string $gatewayType = 'PaymentMethodId',
        float|int $invoiceValue = 0,
        string $displayCurrencyIso = ''
    ): object {

        $paymentMethods = $this->getVendorGateways($invoiceValue, $displayCurrencyIso);

        $pm = null;
        foreach ($paymentMethods as $method) {
            if ($gateway == $method->$gatewayType) {
                $pm = $method;
                break;
            }
        }

        if (! isset($pm)) {
            throw new Exception('Please contact Account Manager to enable the used payment method in your account');
        }

        if ($this->isDirectPayment && ! $pm->IsDirectPayment) {
            throw new Exception($pm->PaymentMethodEn.' Direct Payment Method is not activated. Kindly contact your MyFatoorah account manager or sales representative to activate it.');
        }

        return $pm;
    }

    /**
     * Get the invoice/payment URL and the invoice id
     *
     * @param  int|string  $gatewayId  (default value: 'myfatoorah')
     * @param  int|string|null  $orderId  (default value: null) used in log file
     */
    public function getInvoiceURL(array $curlData, int|string $gatewayId = 0, int|string $orderId = null, string $sessionId = null): array
    {

        $this->log('----------------------------------------------------------------------------------------------------------------------------------');

        $this->isDirectPayment = false;

        if (! empty($sessionId)) {
            return $this->embeddedPayment($curlData, $sessionId, $orderId);
        } else {
            if ($gatewayId == 'myfatoorah' || empty($gatewayId)) {
                return $this->sendPayment($curlData, $orderId);
            } else {
                return $this->executePayment($curlData, $gatewayId, $orderId);
            }
        }
    }

    /**
     * Get the direct payment URL and the invoice id (POST API)
     *
     * @param  int|string|null  $orderId  (default value: null) used in log file
     *
     * @throws Exception
     */
    public function directPayment(array $curlData, int|string $gateway, array $cardInfo, int|string $orderId = null): array
    {
        $this->log('----------------------------------------------------------------------------------------------------------------------------------');

        $this->isDirectPayment = true;

        $data = $this->executePayment($curlData, $gateway, $orderId);

        $json = $this->callAPI($data['invoiceURL'], $cardInfo, $orderId, 'Direct Payment'); //__FUNCTION__

        return ['invoiceURL' => $json->Data->PaymentURL, 'invoiceId' => $data['invoiceId']];
    }

    /**
     * Get the Payment Transaction Status (POST API)
     *
     * @param  int|string|null  $orderId  (default value: null)
     *
     * @throws Exception
     */
    public function getPaymentStatus(string $keyId, string $KeyType, int|string $orderId = null, string $price = null, string $currncy = null): object
    {
        //payment inquiry
        $curlData = ['Key' => $keyId, 'KeyType' => $KeyType];
        $json = $this->callAPI("$this->apiURL/v2/GetPaymentStatus", $curlData, $orderId, 'Get Payment Status');

        $msgLog = 'Order #'.$json->Data->CustomerReference.' ----- Get Payment Status';

        //check for the order information
        if (! $this->checkOrderInformation($json, $orderId, $price, $currncy)) {
            $err = 'Trying to call data of another order';
            $this->log("$msgLog - Exception is $err");
            throw new Exception($err);
        }

        //check invoice status (Paid and Not Paid Cases)
        if ($json->Data->InvoiceStatus == 'Paid' || $json->Data->InvoiceStatus == 'DuplicatePayment') {

            $json->Data = $this->getSuccessData($json);
            $this->log("$msgLog - Status is Paid");
        } else {
            if ($json->Data->InvoiceStatus != 'Paid') {

                $json->Data = $this->getErrorData($json, $keyId, $KeyType);
                $this->log("$msgLog - Status is ".$json->Data->InvoiceStatus.'. Error is '.$json->Data->InvoiceError);
            }
        }

        return $json->Data;
    }

    /**
     * Refund a given Payment (POST API)
     *
     * @param  int|string  $paymentId  payment id that will be refunded
     * @param  float|int|string  $amount  the refund amount
     * @param  string  $currencyCode  the refund currency
     * @param  string  $reason  reason of the refund
     * @param  int|string|null  $orderId  used in log file (default value: null)
     *
     * @throws Exception
     */
    public function refund(int|string $paymentId, float|int|string $amount, string $currencyCode, string $reason, int|string $orderId = null): object
    {
        $rate = $this->getCurrencyRate($currencyCode);
        $url = "$this->apiURL/v2/MakeRefund";

        $postFields = [
            'KeyType' => 'PaymentId',
            'Key' => $paymentId,
            'RefundChargeOnCustomer' => false,
            'ServiceChargeOnCustomer' => false,
            'Amount' => $amount / $rate,
            'Comment' => $reason,
        ];

        return $this->callAPI($url, $postFields, $orderId, 'Make Refund');
    }

    /**
     * Create an invoice using Embedded session (POST API)
     *
     * @param  array  $curlData  invoice information
     * @param  int|string  $sessionId  session id used in payment process
     * @param  int|string|null  $orderId  used in log file (default value: null)
     *
     * @throws Exception
     */
    public function embeddedPayment(array $curlData, int|string $sessionId, int|string $orderId = null): array
    {
        $curlData['SessionId'] = $sessionId;

        $json = $this->callAPI("$this->apiURL/v2/ExecutePayment", $curlData, $orderId,
            'Embedded Payment'); //__FUNCTION__

        return ['invoiceURL' => $json->Data->PaymentURL, 'invoiceId' => $json->Data->InvoiceId];
    }

    /**
     * Get session Data (POST API)
     *
     * @param  string  $userDefinedField  Customer Identifier to dispaly its saved data
     * @param  int|string|null  $orderId  used in log file (default value: null)
     *
     * @throws Exception
     */
    public function getEmbeddedSession(string $userDefinedField = '', int|string $orderId = null): array
    {
        $customerIdentifier = ['CustomerIdentifier' => $userDefinedField];

        $json = $this->callAPI("$this->apiURL/v2/InitiateSession", $customerIdentifier, $orderId,
            'Initiate Session'); //__FUNCTION__

        return $json->Data;
    }
}
