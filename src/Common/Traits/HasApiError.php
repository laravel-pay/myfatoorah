<?php

namespace LaravelPay\MyFatoorah\Common\Traits;

trait HasApiError
{
    protected function getAPIError($json, $res)
    {
        if (isset($json->IsSuccess) && $json->IsSuccess) {
            return '';
        }

        //to avoid blocked IP <html><head><title>403 Forbidden</title></head><body><center><h1>403 Forbidden</h1></center><hr><center>Microsoft-Azure-Application-Gateway/v2</center></body></html>
        $stripHtmlStr = strip_tags($res);
        if ($res != $stripHtmlStr) {
            return trim(preg_replace('/\s+/', ' ', $stripHtmlStr));
        }

        //Check for the errors
        $err = $this->getJsonErrors($json);
        if ($err) {
            return $err;
        }

        if (! $json) {
            return ! empty($res) ? $res : 'Kindly review your MyFatoorah admin configuration due to a wrong entry.';
        }

        if (is_string($json)) {
            return $json;
        }

        return '';
    }

    /**
     * Check for the json (response model) errors
     */
    protected function getJsonErrors(object|string $json): string
    {

        if (isset($json->ValidationErrors) || isset($json->FieldsErrors)) {
            //$err = implode(', ', array_column($json->ValidationErrors, 'Error'));

            $errorsObj = $json->ValidationErrors ?? $json->FieldsErrors;
            $blogDatas = array_column($errorsObj, 'Error', 'Name');

            return implode(', ', array_map(function ($k, $v) {
                return "$k: $v";
            }, array_keys($blogDatas), array_values($blogDatas)));
        }

        if (isset($json->Data->ErrorMessage)) {
            return $json->Data->ErrorMessage;
        }

        //if not get the message. this is due that sometimes errors with ValidationErrors has Error value null so either get the "Name" key or get the "Message"
        //example {"IsSuccess":false,"Message":"Invalid data","ValidationErrors":[{"Name":"invoiceCreate.InvoiceItems","Error":""}],"Data":null}
        //example {"Message":"No HTTP resource was found that matches the request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'.","MessageDetail":"No route providing a controller name was found to match request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'"}
        if (isset($json->Message)) {
            return $json->Message;
        }

        return '';
    }
}
