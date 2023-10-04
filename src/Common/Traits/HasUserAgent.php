<?php

namespace LaravelPay\MyFatoorah\Common\Traits;

trait HasUserAgent
{
    /**
     * Check if the system supports ApplePay or not
     */
    protected function isAppleSystem(): bool
    {

        $userAgent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING);

        if ((stripos($userAgent, 'iPod') || stripos($userAgent, 'iPhone') || stripos($userAgent,
            'iPad') || stripos($userAgent, 'Mac')) && (self::getBrowserName($userAgent) == 'Safari')) {
            return true;
        }

        return false;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    public static function getBrowserName(string $userAgent): string
    {

        if (strpos($userAgent, 'Opera') || strpos($userAgent, 'OPR/')) {
            return 'Opera';
        } elseif (strpos($userAgent, 'Edge')) {
            return 'Edge';
        } elseif (strpos($userAgent, 'Chrome') || strpos($userAgent, 'CriOS')) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') || strpos($userAgent, 'FxiOS')) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (strpos($userAgent, 'MSIE') || strpos($userAgent, 'Trident/7')) {
            return 'Internet Explorer';
        }

        return 'Other';
    }
}
