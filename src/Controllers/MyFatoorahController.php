<?php

namespace LaravelPay\MyFatoorah\Controllers;

use Illuminate\Http\Response;
use LaravelPay\MyFatoorah\Facades\Payment;

class MyFatoorahController
{
    public function index()
    {
        try {
            $paymentMethodId = 0; // 0 for MyFatoorah invoice or 1 for Knet in test mode
            $data = Payment::getInvoiceURL($this->getPayLoadData(), $paymentMethodId);

            return response()->json(['IsSuccess' => 'true', 'Message' => 'Invoice created successfully.', 'Data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['IsSuccess' => 'false', 'Message' => $e->getMessage()]);
        }
    }

    private function getPayLoadData(int|string $orderId = null): array
    {
        $callbackURL = route('myfatoorah.callback');

        return [
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
        ];
    }

    /**
     * Get MyFatoorah payment information
     *
     * @return Response
     */
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
