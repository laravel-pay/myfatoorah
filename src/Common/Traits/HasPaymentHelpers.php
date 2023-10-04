<?php

namespace LaravelPay\MyFatoorah\Common\Traits;

use Exception;

trait HasPaymentHelpers
{
    protected function fillPaymentMethodsArray(object $g, array $paymentMethods): array
    {
        if ($g->PaymentMethodCode != 'ap') {

            if ($g->IsEmbeddedSupported) {
                $paymentMethods['form'][] = $g;
                $paymentMethods['all'][] = $g;
            } else {
                if (! $g->IsDirectPayment) {
                    $paymentMethods['cards'][] = $g;
                    $paymentMethods['all'][] = $g;
                }
            }
        } else {
            if ($this->isAppleSystem()) {

                //add apple payment for IOS systems
                $paymentMethods['cards'][] = $g;
                $paymentMethods['all'][] = $g;
            }
        }

        return $paymentMethods;
    }

    protected function checkOrderInformation(object $json, string $orderId = null, string $price = null, string $currncy = null): bool
    {
        //check for the order ID
        if ($orderId && $json->Data->CustomerReference != $orderId) {
            return false;
        }

        //check for the order price and currency
        $invoiceDisplayValue = explode(' ', $json->Data->InvoiceDisplayValue);
        if ($price && $invoiceDisplayValue[0] != $price) {
            return false;
        }
        if ($currncy && $invoiceDisplayValue[1] != $currncy) {
            return false;
        }

        return true;
    }

    protected function getSuccessData($json)
    {
        foreach ($json->Data->InvoiceTransactions as $transaction) {
            if ($transaction->TransactionStatus == 'Succss') {
                $json->Data->InvoiceStatus = 'Paid';
                $json->Data->InvoiceError = '';

                $json->Data->focusTransaction = $transaction;

                return $json->Data;
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function getErrorData(object $json, string $keyId, string $KeyType): object
    {
        //------------------
        //case 1: payment is Failed
        $focusTransaction = $this->{"getLastTransactionOf$KeyType"}($json, $keyId);
        if ($focusTransaction && $focusTransaction->TransactionStatus == 'Failed') {
            $json->Data->InvoiceStatus = 'Failed';
            $json->Data->InvoiceError = $focusTransaction->Error.'.';

            $json->Data->focusTransaction = $focusTransaction;

            return $json->Data;
        }

        //case 2: payment is Expired
        //all myfatoorah gateway is set to Asia/Kuwait
        $ExpiryDateTime = $json->Data->ExpiryDate.' '.$json->Data->ExpiryTime;
        $ExpiryDate = new \DateTime($ExpiryDateTime, new \DateTimeZone('Asia/Kuwait'));
        $currentDate = new \DateTime('now', new \DateTimeZone('Asia/Kuwait'));

        if ($ExpiryDate < $currentDate) {
            $json->Data->InvoiceStatus = 'Expired';
            $json->Data->InvoiceError = 'Invoice is expired since '.$json->Data->ExpiryDate.'.';

            return $json->Data;
        }

        //------------------
        //case 3: payment is Pending
        //payment is pending .. user has not paid yet and the invoice is not expired
        $json->Data->InvoiceStatus = 'Pending';
        $json->Data->InvoiceError = 'Pending Payment.';

        return $json->Data;
    }

    protected function getLastTransactionOfPaymentId(object $json, int|string $keyId)
    {
        foreach ($json->Data->InvoiceTransactions as $transaction) {
            if ($transaction->PaymentId == $keyId && $transaction->Error) {
                return $transaction;
            }
        }
    }

    /**
     * @param  object  $json
     */
    protected function getLastTransactionOfInvoiceId($json): object
    {
        usort($json->Data->InvoiceTransactions, function ($a, $b) {
            return strtotime($a->TransactionDate) - strtotime($b->TransactionDate);
        });

        return end($json->Data->InvoiceTransactions);
    }

    /**
     * (POST API)
     *
     * @param  int|string|null  $orderId  (default value: null) used in log file
     *
     * @throws Exception
     */
    protected function executePayment(array $curlData, int|string $gatewayId, int|string $orderId = null): array
    {

        $curlData['PaymentMethodId'] = $gatewayId;

        $json = $this->callAPI("$this->apiURL/v2/ExecutePayment", $curlData, $orderId, 'Execute Payment'); //__FUNCTION__

        return ['invoiceURL' => $json->Data->PaymentURL, 'invoiceId' => $json->Data->InvoiceId];
    }

    /**
     * (POST API)
     *
     * @param  int|string|null  $orderId  (default value: null) used in log file
     *
     * @throws Exception
     */
    protected function sendPayment(array $curlData, int|string $orderId = null): array
    {
        $curlData['NotificationOption'] = 'Lnk';

        $json = $this->callAPI("$this->apiURL/v2/SendPayment", $curlData, $orderId, 'Send Payment');

        return ['invoiceURL' => $json->Data->InvoiceURL, 'invoiceId' => $json->Data->InvoiceId];
    }
}
